<?php

namespace SWD;

use SevenWondersDuel;

class Building extends Item {

    public const TYPE_BROWN = 'Brown';
    public const TYPE_GREY = 'Grey';
    public const TYPE_BLUE = 'Blue';
    public const TYPE_GREEN = 'Green';
    public const TYPE_YELLOW = 'Yellow';
    public const TYPE_RED = 'Red';
    public const TYPE_PURPLE = 'Purple';

    public $age;
    public $type;
    public $typeColor;
    public $typeDescription;
    public $resources = [];
    public $chain = null; // coins and or resources
    public $fixedPriceResources = [];
    public $linkedBuilding = 0;
    public $coinsPerBuildingOfType = null;
    public $coinsPerWonder = 0;
    public $guildRewardWonders = false;
    public $guildRewardCoinTriplets = false;
    public $guildRewardBuildingTypes = null;

    /**
     * @param $id
     * @return Building
     */
    public static function get($id) {
        return Material::get()->buildings[$id];
    }

    public function __construct($id, $age, $name, $type, Array $text = []) {
        $this->age = $age;
        $this->type = $type;

        switch ($this->type) {
            case self::TYPE_BROWN:
                $this->typeColor = '#702c12';
                $this->typeDescription = clienttranslate('Raw materials');
                break;
            case self::TYPE_GREY:
                $this->typeColor = '#858680';
                $this->typeDescription = clienttranslate('Manufactured goods');
                break;
            case self::TYPE_BLUE:
                $this->typeColor = '#0275aa';
                $this->typeDescription = clienttranslate('Civilian Building');
                break;
            case self::TYPE_GREEN:
                $this->typeColor = '#027234';
                $this->typeDescription = clienttranslate('Scientific Building');
                break;
            case self::TYPE_YELLOW:
                $this->typeColor = '#f8b305';
                $this->typeDescription = clienttranslate('Commercial Building');
                break;
            case self::TYPE_RED:
                $this->typeColor = '#b7110e';
                $this->typeDescription = clienttranslate('Military Building');
                break;
            case self::TYPE_PURPLE:
                $this->typeColor = '#6f488b';
                $this->typeDescription = clienttranslate('Guild');
                break;
        }

        parent::__construct($id, $name, $text);
    }

    public function checkBuildingAvailable() {
        $age = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        if (!array_key_exists($this->id, $cards)) {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }

        if (!Draftpool::buildingAvailable($this->id)) {
            throw new \BgaUserException( clienttranslate("The building you selected is still covered by other buildings, so it can't be picked.") );
        }
    }

    /**
     * @param Player $player
     * @param $cardId
     * @return PaymentPlan
     */
    public function construct(Player $player, $building = null, $discardedCard = false) {
        $payment = parent::construct($player, $building, $discardedCard);

        SevenWondersDuel::get()->buildingDeck->moveCard($this->id, $player->id);

        // We want to send this notification first, before the detailed "effects" notifications.
        // However, the Payment object passed in this notification is by reference and this will contain
        // the effects' modifications when the notification is send at the end of the request.
        if ($discardedCard) {
            $message = clienttranslate('${player_name} constructed discarded building “${buildingName}” for free (Wonder “${wonderName}”)');
        }
        else {
            $message = clienttranslate('${player_name} constructed building “${buildingName}” for ${cost}');
        }
        SevenWondersDuel::get()->notifyAllPlayers(
            'constructBuilding',
            $message,
            [
                'i18n' => ['buildingName', 'wonderName', 'cost'],
                'buildingName' => $this->name,
                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() . " " . COINS : 'free',
                'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $this->id,
                'payment' => $payment,
                'wonderName' => $discardedCard ? Wonder::get(5)->name : ''
            ]
        );

        $this->constructEffects($player, $payment);

        switch ($this->type) {
            case self::TYPE_BROWN:
                SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_BROWN_CARDS, $player->id);
                break;
            case self::TYPE_GREY:
                SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_GREY_CARDS, $player->id);
                break;
            case self::TYPE_YELLOW:
                SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_YELLOW_CARDS, $player->id);
                break;
            case self::TYPE_RED:
                SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_RED_CARDS, $player->id);
                break;
            case self::TYPE_BLUE:
                SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_BLUE_CARDS, $player->id);
                break;
            case self::TYPE_GREEN:
                SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_GREEN_CARDS, $player->id);
                break;
            case self::TYPE_PURPLE:
                SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_PURPLE_CARDS, $player->id);
                break;
        }

        return $payment;
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     * @param PaymentPlan $payment
     */
    protected function constructEffects(Player $player, PaymentPlan $payment) {
        parent::constructEffects($player, $payment);

        if ($this->scientificSymbol) {
            $buildings = Player::me()->getBuildings()->filterByScientificSymbol($this->scientificSymbol);
            if (count($buildings->array) == 2) {
                if (count(SevenWondersDuel::get()->progressTokenDeck->getCardsInLocation('board')) > 0) {
                    $payment->selectProgressToken = true;
                    SevenWondersDuel::get()->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name} gathered a pair of identical scientific symbols, and may now choose a Progress token'),
                        [
                            'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                        ]
                    );
                }
                else {
                    SevenWondersDuel::get()->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name} gathered a pair of identical scientific symbols, but there are no Progress tokens left'),
                        [
                            'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                        ]
                    );
                }
            }
        }

        if ($player->hasBuilding($this->linkedBuilding) && $player->hasProgressToken(10)) {
            $payment->urbanismAward = 4;
            $player->increaseCoins($payment->urbanismAward);

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} gets 4 coins (Progress token “${progressTokenName}”)'),
                [
                    'i18n' => ['progressTokenName'],
                    'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                    'progressTokenName' => ProgressToken::get(10)->name, // Urbanism
                ]
            );
        }

        if($this->coinsPerBuildingOfType) {
            $buildingsOfType = $player->getBuildings()->filterByTypes([$this->coinsPerBuildingOfType[0]]);
            $buildingsCount = count($buildingsOfType->array);
            if ($buildingsCount > 0){
                $payment->coinReward = $buildingsCount * $this->coinsPerBuildingOfType[1];
                $player->increaseCoins($payment->coinReward);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} gets ${coins} coin(s), ${coinsPerBuilding} for each ${buildingType} building in their city'),
                    [
                        'i18n' => ['buildingType'],
                        'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                        'coins' => $payment->coinReward,
                        'coinsPerBuilding' => $this->coinsPerBuildingOfType[1],
                        'buildingType' => $this->coinsPerBuildingOfType[0],
                    ]
                );
            }
        }

        if($this->guildRewardBuildingTypes) {
            $buildingsOfType = $player->getBuildings()->filterByTypes($this->guildRewardBuildingTypes);
            $buildingsCount = count($buildingsOfType->array);

            $opponentBuildingsOfType = $player->getOpponent()->getBuildings()->filterByTypes($this->guildRewardBuildingTypes);
            $opponentBuildingsCount = count($opponentBuildingsOfType->array);

            $maxBuildingsCount = max($buildingsCount, $opponentBuildingsCount);
            $mostPlayerName = $buildingsCount >= $opponentBuildingsCount ? $player->name : $player->getOpponent()->name;
            if ($maxBuildingsCount > 0){
                $payment->coinReward = $maxBuildingsCount;
                $player->increaseCoins($payment->coinReward);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} gets ${coins} coin(s), 1 for each ${buildingType} building in the city which has the most of them (${mostPlayerName}\'s)'),
                    [
                        'i18n' => ['buildingType'],
                        'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                        'coins' => $payment->coinReward,
                        'buildingType' => count($this->guildRewardBuildingTypes) > 1 ? clienttranslate('Brown and Grey') : $this->guildRewardBuildingTypes[0],
                        'mostPlayerName' => $mostPlayerName,
                    ]
                );
            }
        }

        if($this->coinsPerWonder) {
            $constructedWonders = $player->getWonders()->filterByConstructed();
            $constructedWondersCount = count($constructedWonders->array);
            if ($constructedWondersCount > 0){
                $payment->coinReward = $constructedWondersCount * $this->coinsPerWonder;
                $player->increaseCoins($payment->coinReward);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} gets ${coins} coin(s), ${coinsPerWonder} for each constructed Wonder in their city'),
                    [
                        'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                        'coins' => $payment->coinReward,
                        'coinsPerWonder' => $this->coinsPerWonder,
                    ]
                );
            }
        }
    }

    protected function getBuildingTypeString($type) {
        switch($type){
            case Building::TYPE_BROWN:
                return self::_('Brown');
            case Building::TYPE_GREY:
                return self::_('Grey');
            case Building::TYPE_YELLOW:
                return self::_('Yellow');
            case Building::TYPE_RED:
                return self::_('Red');
            case Building::TYPE_BLUE:
                return self::_('Blue');
            case Building::TYPE_GREEN:
                return self::_('Green');
            case Building::TYPE_PURPLE:
                return self::_('Purple');
        }
    }
    public function getScoreCategory() {
        return strtolower($this->type);
    }

    /**
     * @param Player $player
     * @param $cardId
     * @return int
     */
    public function discard(Player $player) {
        $discardGain = $player->calculateDiscardGain($this);
        $player->increaseCoins($discardGain);

        SevenWondersDuel::get()->buildingDeck->insertCardOnExtremePosition($this->id, 'discard', true);
        return $discardGain;
    }

    /**
     * @param array $resources
     * @return static
     */
    public function setResources($resources) {
        $this->resources = $resources;
        $resource = array_keys($resources)[0];
        $amount = array_shift($resources);
        if (in_array($resource, [CLAY, WOOD, STONE])) {
            if ($amount == 1) {
                $this->text[] = clienttranslate("This building produces one unit of the raw goods represented.");
            }
            if ($amount == 2) {
                $this->text[] = clienttranslate("This building produces two units of the raw goods represented.");
            }
        }
        else {
            $this->text[] = clienttranslate("This building produces one unit of the manufactured goods represented.");
        }
        return $this;
    }

    /**
     * @param array $fixedPriceResources
     * @return static
     */
    public function setFixedPriceResources(array $fixedPriceResources) {
        $this->fixedPriceResources = $fixedPriceResources;
        $this->text[] = clienttranslate("This card changes the trading rules for the indicated resource(s). Starting on the following turn, you will purchase the indicated resource(s) from the bank at the fixed cost of 1 coin per unit.");
        return $this;
    }

    /**
     * @param int $linkedBuilding
     * @return static
     */
    public function setLinkedBuilding(int $linkedBuilding) {
        if ($linkedBuilding < $this->id) {
            $this->linkedBuilding = $linkedBuilding;
        }
        else {
            $building = Building::get($linkedBuilding);
            $this->text[] = sprintf(self::_("This card grants the linking symbol shown. During Age %s you will be able to construct building “%s” for free."), ageRoman($building->age), $building->name);
        }
        return $this;
    }

    /**
     * @param array $coinsPerBuildingOfType
     * @return static
     */
    public function setCoinsPerBuildingOfType(string $type, int $coins) {
        $this->coinsPerBuildingOfType = [$type, $coins];
        $this->text[] = sprintf(
            self::_("This card is worth %d coin(s) for each %s card in your city %s at the time it is constructed."),
            $coins,
            $this->getBuildingTypeString($type),
            $type == Building::TYPE_YELLOW ? self::_("(including itself)") : ''
        );
        return $this;
    }

    /**
     * @param int $coinsPerWonder
     * @return static
     */
    public function setCoinsPerWonder(int $coinsPerWonder) {
        $this->coinsPerWonder = $coinsPerWonder;
        $this->text[] = clienttranslate("This card is worth 2 coins per Wonder constructed in your city at the time it is constructed.");
        return $this;
    }

    /**
     * @param bool $guildRewardWonders
     * @return static
     */
    public function setGuildRewardWonders(bool $guildRewardWonders) {
        $this->guildRewardWonders = $guildRewardWonders;
        $this->text[] = clienttranslate("At the end of the game, this card is worth 2 victory points for each Wonder constructed in the city which has the most wonders.");
        return $this;
    }

    /**
     * @param bool $guildRewardCoinTriplets
     * @return static
     */
    public function setGuildRewardCoinTriplets(bool $guildRewardCoinTriplets) {
        $this->guildRewardCoinTriplets = $guildRewardCoinTriplets;
        $this->text[] = clienttranslate("At the end of the game, this card is worth 1 victory point for each set of 3 coins in the richest city.");
        return $this;
    }

    /**
     * @param array $guildRewardBuildingTypes
     * @return static
     */
    public function setGuildRewardBuildingTypes(array $guildRewardBuildingTypes) {
        $this->guildRewardBuildingTypes = $guildRewardBuildingTypes;
        if (count($guildRewardBuildingTypes) == 1) {
            $buildingType = $this->getBuildingTypeString($this->guildRewardBuildingTypes[0]);
            $this->text[] = sprintf(self::_("At the time it is constructed, this card grants you 1 coin for each %s card in the city which has the most %s cards."), $buildingType, $buildingType);
            $this->text[] = sprintf(self::_("At the end of the game, this card is worth 1 victory point for each %s card in the city which has the most %s cards."), $buildingType, $buildingType);
        }
        else {
            $this->text[] = clienttranslate("At the time it is constructed, this card grants you 1 coin for each brown and each grey card in the city which has the most brown and grey cards.");
            $this->text[] = clienttranslate("At the end of the game, this card is worth 1 victory point for each brown and each grey card in the city which has the most brown and grey cards.");
        }
        return $this;
    }

}