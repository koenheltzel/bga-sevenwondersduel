<?php

namespace SWD;

use SevenWondersDuel;

class Player extends \APP_DbObject{

    public $id = null;
    public $name = null;
    public $color = null;
    private $wonderIds = [];
    private $buildingIds = [];
    public $progressTokenIds = [];

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
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return self::get(1);
        } else {
            $playerId = SevenWondersDuel::get()->getCurrentPlayerId();
            return self::get($playerId);
        }
    }

    /**
     * @return Player
     */
    public static function opponent($meId = null) {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return self::get(2);
        } else {
            if (is_null($meId)) $meId = SevenWondersDuel::get()->getCurrentPlayerId();
            $players = SevenWondersDuel::get()->loadPlayersBasicInfos();
            $playerIds = array_keys($players);
            foreach ($playerIds as $playerId) {
                // This is a 2-player game so we just look for a playerId not matching currentPlayerId.
                if ($playerId <> $meId) {
                    return self::get($playerId);
                }
            }
            throw new \BgaSystemException(clienttranslate("Opponent object couldn't be constructed."));
        }
    }

    private function __construct($id) {
        $this->id = $id;
        if ($_SERVER['HTTP_HOST'] != 'localhost') {
            $basicInfo = SevenWondersDuel::get()->getPlayerBasicInfo($this->id);
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
    public function getActive() {
        return self::get(SevenWondersDuel::get()->getActivePlayerId());
    }

    public function getAlias() {
        return $this->id == Player::me()->id ? 'me' : 'opponent';
    }

    public function getCoins() {
        return (int)$this->getValue("player_coins");
    }

    public function increaseCoins($increase) {
        if ($increase < 0) {
            $increase = max($increase, -$this->getCoins());
        }
        return $this->increaseValue("player_coins", $increase);
    }

    // get score
    function getScore() {
        return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='{$this->id}'");
    }
    // set score
    function setScore($score) {
        $this->DbQuery("UPDATE player SET player_score='$score' WHERE player_id='{$this->id}'");
    }
    // increment score (can be negative too)
    function increaseScore($increase) {
        $count = $this->getScore();
        if ($increase != 0) {
            $count += $increase;
            $this->setScore($count);
        }
        return $count;
    }

    public function getValue(string $column) {
        return self::getUniqueValueFromDB( "SELECT `$column` FROM player WHERE player_id='{$this->id}'" );
    }

    public function setValue(string $column, mixed $value) {
        self::DbQuery( "UPDATE player SET `$column` = {$this->escapeStringForDB($value)} WHERE player_id='{$this->id}'" );
    }

    public function increaseValue(string $column, int $increase) {
        self::DbQuery( "UPDATE player SET `$column` = `$column` + $increase WHERE player_id='{$this->id}'" );
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
        return 2 + count($this->getBuildings()->filterByTypes([Building::TYPE_YELLOW])->array);
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
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
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
            $payment = $this->getPaymentPlan($wonder);
            $row['cost'] = $row['constructed'] ? -1 : $payment->totalCost();
            $row['payment'] = $payment;
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @return array
     */
    public function getBuildingIds(): array {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
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
        return SevenWondersDuel::get()->buildingDeck->getCardsInLocation($this->id);
    }

    /**
     * @return array
     */
    public function getProgressTokenIds(): array {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
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

}