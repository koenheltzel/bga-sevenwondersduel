<?php

namespace SWD;

use SevenWondersDuelAgora;

class Player extends Base{

    public $id = null;
    public $name = null;
    public $color = null;
    private $wonderIds = [];
    private $conspiracyIds = [];
    private $buildingIds = [];
    public $progressTokenIds = [];
    public $decreeIds = [];

    public static $instances = [];

    /**
     * @return Player
     */
    public static function get($playerId) {
        if (!isset(self::$instances[$playerId])) {
            self::$instances[$playerId] = new Player($playerId);
        }
        return self::$instances[$playerId];
    }

    /**
     * @return Player
     */
    public static function me() {
        if (isDevEnvironment()) {
            return self::get(1);
        } else {
            $playerIds = array_keys(SevenWondersDuelAgora::get()->loadPlayersBasicInfos());
            $playerId = SevenWondersDuelAgora::get()->getCurrentPlayerId(true); // pass true to prevent crash in zombie turn mode
            if (is_null($playerId) || !in_array($playerId, $playerIds)) {
                // We are either in zombieTurn (server initiated), or a spectator. Take the first player as me.
                $playerId = $playerIds[0];
            }
            return self::get($playerId);
        }
    }

    /**
     * @return Player
     */
    public static function opponent($meId = null) {
        if (isDevEnvironment()) {
            return self::get(2);
        } else {
            if (is_null($meId)) $meId = Player::me()->id;
            $players = SevenWondersDuelAgora::get()->loadPlayersBasicInfos();
            $playerIds = array_keys($players);
            foreach ($playerIds as $playerId) {
                // This is a 2-player game so we just look for a playerId not matching currentPlayerId.
                if ($playerId <> $meId) {
                    return self::get($playerId);
                }
            }
            throw new \BgaSystemException("Opponent object couldn't be constructed.");
        }
    }

    private function __construct($id) {
        $this->id = $id;
        if (strstr($_SERVER['HTTP_HOST'], 'boardgamearena.com')) {
            $basicInfo = SevenWondersDuelAgora::get()->getPlayerBasicInfo($this->id);
            $this->name = $basicInfo['player_name'];
            $this->color = $basicInfo['player_color'];
        }
        self::$instances[$id] = $this;
    }

    /**
     * @return Player
     */
    public function getOpponent() {
        return self::opponent($this->id);
    }

    /**
     * @return Player
     */
    public static function getActive() {
        return self::get(SevenWondersDuelAgora::get()->getActivePlayerId());
    }

    public function getAlias() {
        return $this->id == Player::me()->id ? 'me' : 'opponent';
    }

    public function getCoins() {
        return (int)$this->getValue("player_coins");
    }

    public function getCubes() {
        return SevenWondersDuelAgora::get()->influenceCubeDeck->countCardInLocation($this->id);
    }

    public function increaseCoins($increase) {
        if ($increase < 0) {
            $increase = max($increase, -$this->getCoins());
        }
        return $this->increaseValue("player_coins", $increase);
    }

    function isWinner() {
        return (int)$this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='{$this->id}'");
    }
    // set winner
    function setWinner() {
        $this->DbQuery("UPDATE player SET player_score='1' WHERE player_id='{$this->id}'");
    }

    // get score
    function getScore() {
        return $this->getUniqueValueFromDB("SELECT player_score_total FROM player WHERE player_id='{$this->id}'");
    }
    // set score
    function setScore($score) {
        $this->DbQuery("UPDATE player SET player_score_total='$score' WHERE player_id='{$this->id}'");
    }
    // increment score (can be negative too)
    function increaseScore($increase, $category) {
        $count = $this->getScore();
        if ($increase != 0) {
            $count += $increase;
            $this->setScore($count);

            $category_column = 'player_score_' . strtolower($category);
            $this->increaseValue($category_column, $increase);

            if ($category == strtolower(Building::TYPE_BLUE)) {
                // Blue is also the deciding factor in case of a tie, so fill the player_score_aux column with the same value.
                $this->setValue('player_score_aux', $this->getValue($category_column));
            }

            SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VICTORY_POINTS, $this->id);
            switch (strtolower($category)) { // strtolower to be sure but shouldn't be necessary anymore.
                case strtolower(Building::TYPE_BLUE):
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_BLUE, $this->id);
                    break;
                case strtolower(Building::TYPE_GREEN):
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_GREEN, $this->id);
                    break;
                case strtolower(Building::TYPE_YELLOW):
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_YELLOW, $this->id);
                    break;
                case strtolower(Building::TYPE_PURPLE):
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_PURPLE, $this->id);
                    break;
                case SevenWondersDuelAgora::SCORE_WONDERS:
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_WONDERS, $this->id);
                    break;
                case SevenWondersDuelAgora::SCORE_PROGRESSTOKENS:
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_PROGRESS_TOKENS, $this->id);
                    break;
                case SevenWondersDuelAgora::SCORE_COINS:
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_COINS, $this->id);
                    break;
                case SevenWondersDuelAgora::SCORE_MILITARY:
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_MILITARY, $this->id);
                    break;
                case SevenWondersDuelAgora::SCORE_SENATE:
                    SevenWondersDuelAgora::get()->incStat($increase, SevenWondersDuelAgora::STAT_VP_SENATE, $this->id);
                    break;
            }
        }
        return $count;
    }

    public function getValue(string $column) {
        return self::getUniqueValueFromDB( "SELECT `$column` FROM player WHERE player_id='{$this->id}'" );
    }

    public function setValue(string $column, int $value) {
        self::DbQuery( "UPDATE player SET `$column` = {$this->escapeStringForDB($value)} WHERE player_id='{$this->id}'" );
    }

    public function increaseValue(string $column, int $increase) {
        self::DbQuery( "UPDATE player SET `$column` = `$column` + $increase WHERE player_id='{$this->id}'" );
    }

    public function getScoreCategories() {
        $agora_column = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::OPTION_AGORA) ? ',`player_score_senate`' : '';

        return self::getCollectionFromDB("SELECT `player_score_blue`,`player_score_green`,`player_score_yellow`,`player_score_purple`,`player_score_wonders`,`player_score_progresstokens`,`player_score_coins`,`player_score_military` {$agora_column} FROM player WHERE player_id='{$this->id}'");
    }

    /**
     * Count resources of the specified type as provided by Brown and Grey cards only.
     * @param $searchResource
     * @return int
     */
    public function resourceCount($searchResource) {
        $count = 0;
        foreach ($this->getBuildingIds() as $id) {
            $building = Building::get($id);
            if (in_array($building->type, [Building::TYPE_BROWN, Building::TYPE_GREY])) {
                foreach($building->resources as $resource => $amount) {
                    if ($searchResource == $resource) {
                        $count += $amount;
                    }
                }
            }
        }
        return $count;
    }

    public function calculateDiscardGain() {
        $gain = 2 + count($this->getBuildings()->filterByTypes([Building::TYPE_YELLOW])->array);
        if ($this->hasDecree(13)) {
            $gain += 2;
        }
        return $gain;
    }

    /**
     * @param Item $buyingItem
     */
    public function getPaymentPlan($buyingItem, $print = false, $printChoices = false) {
        $payment = new PaymentPlan($buyingItem);
        $payment->calculate($this, $print, $printChoices);
        return $payment;
    }

    /**
     * @return array
     */
    public function getWonderIds(): array {
        if (isDevEnvironment()) {
            return $this->wonderIds;
        }
        else {
            return array_column($this->getWonderDeckCards(), 'id');
        }
    }

    /**
     * @param array $wonderIds
     */
    public function setWonderIds(array $wonderIds): void {
        $this->wonderIds = $wonderIds;
    }

    /**
     * @return Wonders
     */
    public function getWonders(): Wonders {
        return Wonders::createByWonderIds($this->getWonderIds());
    }

    public function hasWonder($id) : bool {
        return in_array($id, $this->getWonderIds());
    }

    public function getWonderDeckCards(): array {
        return Wonders::getDeckCardsSorted($this->id);
    }

    public function getWondersData(): array {
        $cards = $this->getWonderDeckCards();
        $rows = [];
        foreach($cards as $card) {
            $wonder = Wonder::get($card['id']);
            $row = [];
            $row['wonder'] = $wonder->id;
            $row['position'] = $card['location_arg'];
            $row['constructed'] = $wonder->isConstructed();
            $row['ageCardSpriteXY'] = $row['constructed'] ? Building::getBackSpriteXY($row['constructed']) : null;
            $payment = $this->getPaymentPlan($wonder);
            $row['cost'] = $row['constructed'] ? -1 : $payment->totalCost();
            $row['payment'] = $payment;
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @return Conspiracies
     */
    public function getConspiracies(): Conspiracies {
        return Conspiracies::createByConspiracyIds($this->getConspiracyIds());
    }

    /**
     * @return array
     */
    public function getConspiracyIds(): array {
        if (isDevEnvironment()) {
            return $this->conspiracyIds;
        }
        else {
            return array_column($this->getConspiracyDeckCards(), 'id');
        }
    }

    public function getConspiracyDeckCards(): array {
        return Conspiracies::getDeckCardsSorted($this->id);
    }

    public function getConspiraciesData(): array {
        $cards = $this->getConspiracyDeckCards();
        $rows = [];
        foreach($cards as $card) {
            $conspiracy = Conspiracy::get($card['id']);
            $progressToken = 0;
            if ($conspiracy->id == 5) {
                $progressToken = count(SevenWondersDuelAgora::get()->progressTokenDeck->getCardsInLocation('conspiracy5'));
            }
            $row = [];
            $row['conspiracy'] = $conspiracy->isTriggered() ? $conspiracy->id : 18;
            $row['position'] = (int)$card['location_arg'];
            $row['prepared'] = $conspiracy->isPrepared(); // Returns 0 or the age of the card used to prepare.
            $row['triggered'] = (int)$conspiracy->isTriggered();
            $row['progressToken'] = $progressToken;
            $row['ageCardSpriteXY'] = $row['prepared'] ? Building::getBackSpriteXY($row['prepared']) : null;
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @return array
     */
    public function getBuildingIds(): array {
        if (isDevEnvironment()) {
            return $this->buildingIds;
        }
        else {
            return array_column($this->getBuildingDeckCards(), 'id');
        }
    }

    /**
     * @param array $buildingIds
     */
    public function setBuildingIds(array $buildingIds): void {
        $this->buildingIds = $buildingIds;
    }

    /**
     * @return Buildings
     */
    public function getBuildings(): Buildings {
        return Buildings::createByBuildingIds($this->getBuildingIds());
    }

    public function hasBuilding($id) : bool {
        return in_array($id, $this->getBuildingIds());
    }

    public function getBuildingDeckCards(): array {
        return SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation($this->id);
    }

    public function getScientificSymbolCount(): int {
        $buildings = $this->getBuildings()->filterByTypes([Building::TYPE_GREEN]);
        $symbols = [];
        foreach ($buildings as $building) {
            if (!in_array($building->scientificSymbol, $symbols)) {
                $symbols[] = $building->scientificSymbol;
            }
        }
        if ($this->hasProgressToken(4)) {
            $symbols[] = ProgressToken::get(4)->scientificSymbol;
        }
        return count($symbols);
    }

    /**
     * @return array
     */
    public function getProgressTokenIds(): array {
        if (isDevEnvironment()) {
            return $this->progressTokenIds;
        }
        else {
            return array_column($this->getProgressTokenDeckCards(), 'id');
        }
    }

    /**
     * @param array $wonderIds
     */
    public function setProgressTokenIds(array $progressTokenIds): void {
        $this->progressTokenIds = $progressTokenIds;
    }

    /**
     * @return ProgressTokens
     */
    public function getProgressTokens(): ProgressTokens {
        return ProgressTokens::createByProgressTokenIds($this->getProgressTokenIds());
    }

    public function getProgressTokenDeckCards(): array {
        return ProgressTokens::getDeckCardsSorted($this->id);
    }

    public function hasProgressToken($id) : int {
        return in_array($id, $this->getProgressTokenIds());
    }

    public function hasDecree($id) : bool {
        if (isDevEnvironment()) {
            return in_array($id, $this->decreeIds);
        }
        else {
            $chamber = Decree::get($id)->getChamber();
            $player = Senate::getControllingPlayer($chamber);
            return $this == $player;
        }
    }

    public function countChambersInControl() {
        $situation = Senate::getSituation();
        $playerChambers = 0;
        foreach($situation['chambers'] as $chamber) {
            if ($chamber['controller'] == $this->id) {
                $playerChambers++;
            }
        }
        return $playerChambers;
    }

    public function getSenateActionsCount() {
        $blueBuildings = count($this->getBuildings()->filterByTypes([Building::TYPE_BLUE])->array);
        if ($this->hasDecree(12)) {
            return 2 + $blueBuildings;
        }
        else {
            switch($blueBuildings) {
                case 0:
                case 1:
                    return 1;
                    break;
                case 2:
                case 3:
                    return 2;
                    break;
                default:
                    return 3;
                    break;
            }
        }
    }

}