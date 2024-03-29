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
    public const TYPE_SENATOR = 'Senator';

    public const SUBTYPE_POLITICIAN = 'Politician';
    public const SUBTYPE_CONSPIRATOR = 'Conspirator';

    private const SPRITESHEET_COLUMNS = 12;

    public $spriteXY;
    public $age;
    public $type;
    public $subType;
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
    public $listPage = 2;
    public $listPosition = [0, 0];
    public $senateSection = 0;

    /**
     * @param $id
     * @return Building
     */
    public static function get($id) {
        return Material::get()->buildings[$id];
    }

    public function __construct($id, $age, $name, $type) {
        $this->age = $age;
        $this->type = $type;

        $spriteId = $id;
        // Senators
        if ($id >= 74 && $id <= 75){
            $spriteId = 74;
        }
        elseif ($id >= 76 && $id <= 78){
            $spriteId = 75;
        }
        elseif ($id >= 79 && $id <= 80){
            $spriteId = 76;
        }
        elseif ($id >= 81 && $id <= 86){
            $spriteId = 77;
        }
        // Grand Temples (Pantheon)
        elseif ($id >= 87 && $id <= 91){
            $spriteId = $id - 9;
        }
        $this->spriteXY = self::getSpriteXY($spriteId);

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
                $this->typeColor = (int)SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::OPTION_PANTHEON) ? '#41499a' : '#6f488b';
                $this->typeDescription = (int)SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::OPTION_PANTHEON) ? clienttranslate('Grand Temple') : clienttranslate('Guild');
                break;
            case self::TYPE_SENATOR:
                $this->typeColor = '#000000';
                $this->typeDescription = clienttranslate('Senator');
                break;
        }

        parent::__construct($id, $name);
    }

    public function checkBuildingAvailable($dontAllowCoveredBuildings=true) {
        $age = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        if (!array_key_exists($this->id, $cards)) {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }

        if ($dontAllowCoveredBuildings && !Draftpool::buildingAvailable($this->id)) {
            throw new \BgaUserException( clienttranslate("The building you selected is still covered by other buildings, so it can't be picked.") );
        }
    }

    public function checkBuildingLastRow() {
        $currentAge = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
        $rowColumn = Draftpool::getBuildingRowCol($currentAge, $this->id);
        if ($rowColumn[0] != 1) {
            throw new \BgaUserException( clienttranslate("The building you selected is not on the last row.") );
        }
    }

    /**
     * @param Player $player
     * @param $cardId
     * @return PaymentPlan
     */
    public function construct(Player $player, $building = null, $discardedCard = false, $conspiracyTreason = false) {
        $payment = parent::construct($player, $building, $discardedCard || $conspiracyTreason);

        SevenWondersDuel::get()->buildingDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        // We want to send this notification first, before the detailed "effects" notifications.
        // However, the Payment object passed in this notification is by reference and this will contain
        // the effects' modifications when the notification is send at the end of the request.
        $wonderName = null;
        $conspiracyName = null;
        $progressTokenName = null;
        if ($discardedCard) {
            $message = clienttranslate('${player_name} constructed discarded building “${buildingName}” for free (Wonder “${wonderName}”)');
            $wonderName = Wonder::get(5)->name;
        }
        elseif ($conspiracyTreason) {
            $message = clienttranslate('${player_name} constructed building “${buildingName}” for free (Conspiracy “${conspiracyName}”)');
            $conspiracyName = Conspiracy::get(8)->name;
        }
        elseif ($this->type == Building::TYPE_SENATOR) {
            if ($player->hasProgressToken(11)) {
                $progressTokenName = ProgressToken::get(11)->name;
                $message = clienttranslate('${player_name} recruited Senator “${buildingName}” for free (Progress Token “${progressTokenName}”)');
            }
            else {
                $message = clienttranslate('${player_name} recruited Senator “${buildingName}” for ${cost} ${costUnit}');
            }
        }
        else {
            $message = clienttranslate('${player_name} constructed building “${buildingName}” for ${cost} ${costUnit}');
        }
        SevenWondersDuel::get()->notifyAllPlayers(
            'constructBuilding',
            $message,
            [
                'i18n' => ['buildingName', 'wonderName', 'conspiracyName', 'costUnit', 'progressTokenName'],
                'buildingName' => $this->name,
                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() : "",
                'costUnit' => $payment->totalCost() > 0 ? RESOURCES[COINS] : clienttranslate('free'),
                'player_name' => $player->name,
                'playerId' => $player->id,
                'buildingId' => $this->id,
                'payment' => $payment,
                'wonderName' => $wonderName,
                'conspiracyName' => $conspiracyName,
                'progressTokenName' => $progressTokenName,
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
            case self::TYPE_SENATOR:
                if ($this->subType == self::SUBTYPE_POLITICIAN) {
                    SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_POLITICIAN_CARDS, $player->id);
                }
                else {
                    SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_CONSPIRATOR_CARDS, $player->id);
                }
                break;
        }

        return $payment;
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     * @param PaymentPlan $payment
     */
    protected function constructEffects(Player $player, Payment $payment) {
        parent::constructEffects($player, $payment);

        $opponent = $player->getOpponent();

        if ($this->scientificSymbol) {
            if ($player->hasScientificSymbolPair($this->scientificSymbol) && $this->gatheredSciencePairNotification($player)) {
                $payment->selectProgressToken = true;
            }
        }

        if ($payment->isFreeThroughLinking() && $player->hasProgressToken(10)) {
            $payment->urbanismAward = 4;
            $player->increaseCoins($payment->urbanismAward);

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} gets 4 coins (Progress token “${progressTokenName}”)'),
                [
                    'i18n' => ['progressTokenName'],
                    'player_name' => $player->name,
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
                        'player_name' => $player->name,
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

            $opponentBuildingsOfType = $opponent->getBuildings()->filterByTypes($this->guildRewardBuildingTypes);
            $opponentBuildingsCount = count($opponentBuildingsOfType->array);

            $maxBuildingsCount = max($buildingsCount, $opponentBuildingsCount);
            $mostPlayerName = $buildingsCount >= $opponentBuildingsCount ? $player->name : $opponent->name;
            if ($maxBuildingsCount > 0){
                $payment->coinReward = $maxBuildingsCount;
                $player->increaseCoins($payment->coinReward);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} gets ${coins} coin(s), 1 for each ${buildingType} building in the city which has the most of them (${mostPlayerName}\'s)'),
                    [
                        'i18n' => ['buildingType'],
                        'player_name' => $player->name,
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
                        'player_name' => $player->name,
                        'coins' => $payment->coinReward,
                        'coinsPerWonder' => $this->coinsPerWonder,
                    ]
                );
            }
        }

        $colorDecree = [Building::TYPE_BLUE => 1, Building::TYPE_GREEN => 2, Building::TYPE_YELLOW => 3, Building::TYPE_RED => 4];
        if (isset($colorDecree[$this->type]) && ($player->hasDecree($colorDecree[$this->type]) || $opponent->hasDecree($colorDecree[$this->type]))) {
            $payment->decreeCoinRewardDecreeId = $colorDecree[$this->type];
            $payment->decreeCoinReward = (int)SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
            $decreePlayer = $player->hasDecree($colorDecree[$this->type]) ? $player : $opponent;

            $payment->decreeCoinRewardPlayerId = $decreePlayer->id;
            $decreePlayer->increaseCoins($payment->decreeCoinReward);

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} gets ${coins} coin(s) (as many as the current Age) because a ${buildingType} Building was constructed and he controls the Decree in Chamber ${chamber}'),
                [
                    'i18n' => ['buildingType'],
                    'buildingType' => $this->getBuildingTypeString($this->type),
                    'player_name' => $decreePlayer->name,
                    'coins' => $payment->decreeCoinReward,
                    'chamber' => Decree::get($payment->decreeCoinRewardDecreeId)->getChamber(),
                ]
            );
        }

        if ($this->type == Building::TYPE_SENATOR && $this->subType == Building::SUBTYPE_CONSPIRATOR && $player->hasDecree(16)) {
            SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_DECREE, 1);

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} gets an extra turn after this one (Decree in Chamber ${chamber})'),
                [
                    'chamber' => Decree::get(16)->getChamber(),
                    'player_name' => Player::getActive()->name,
                ]
            );
        }
    }

    public static function getBuildingTypeString($type) {
        switch($type){
            case Building::TYPE_BROWN:
                return clienttranslate('Brown');
            case Building::TYPE_GREY:
                return clienttranslate('Grey');
            case Building::TYPE_YELLOW:
                return clienttranslate('Yellow');
            case Building::TYPE_RED:
                return clienttranslate('Red');
            case Building::TYPE_BLUE:
                return clienttranslate('Blue');
            case Building::TYPE_GREEN:
                return clienttranslate('Green');
            case Building::TYPE_PURPLE:
                return clienttranslate('Purple');
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
    public function discard(Player $player, $gain=true) {
        $discardGain = 0;
        if ($gain) {
            $discardGain = $player->calculateDiscardGain($this);
            $player->increaseCoins($discardGain);
        }

        SevenWondersDuel::get()->buildingDeck->insertCardOnExtremePosition($this->id, 'discard', true);
        SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_DISCARDED_CARDS, $player->id);

        return $discardGain;
    }

    private static function getSpriteXY($spriteId) {
        return [
            ($spriteId - 1) % self::SPRITESHEET_COLUMNS,
            floor(($spriteId - 1) / self::SPRITESHEET_COLUMNS)
        ];
    }

    public static function getBackSpriteXY($age){
        return self::getSpriteXY(82 + $age);
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
                $this->addText(clienttranslate("This building produces one unit of the raw goods represented."));
            }
            if ($amount == 2) {
                $this->addText(clienttranslate("This building produces two units of the raw goods represented."));
            }
        }
        else {
            $this->addText(clienttranslate("This building produces one unit of the manufactured goods represented."));
        }
        return $this;
    }

    /**
     * @param array $fixedPriceResources
     * @return static
     */
    public function setFixedPriceResources(array $fixedPriceResources) {
        $this->fixedPriceResources = $fixedPriceResources;
        $this->addText(clienttranslate("This card changes the trading rules for the indicated resource(s). Starting on the following turn, you will purchase the indicated resource(s) from the bank at the fixed cost of 1 coin per unit."));
        return $this;
    }

    /**
     * @param int $linkedBuilding
     * @return static
     */
    public function setLinkedBuilding(int $linkedBuilding) {
        $this->setListPage(1);
        if ($linkedBuilding < $this->id) {
            $this->linkedBuilding = $linkedBuilding;
            if ($linkedBuilding < 0) {
                $this->addText(clienttranslate("At the end of the game, you score 5, 12, or 21 victory points if you’ve built 1, 2, or 3 Grand Temples."));
            }
        }
        else {
            $building = Building::get($linkedBuilding);
            $spritesheetColumns = 12;
            $x = ($linkedBuilding - 1) % $spritesheetColumns;
            $y = floor(($linkedBuilding - 1) / $spritesheetColumns);
            $this->addText(
                clienttranslate('This card grants the linking symbol shown. During Age ${age} you will be able to construct building “${buildingName}” for free.'),
                true,
                ["age" => ageRoman($building->age), "buildingName" => clienttranslate($building->name)]
            );
            $this->addText(
                '<div class="building building_header_small" style="background-position: -' . $x . '00% calc((-10px + -' . $y . ' * var(--building-height)) * var(--building-small-scale)); margin-left: calc(var(--scale) * 50px)"></div>',
                false
            );
        }
        return $this;
    }

    /**
     * @param array $coinsPerBuildingOfType
     * @return static
     */
    public function setCoinsPerBuildingOfType(string $type, int $coins) {
        $this->coinsPerBuildingOfType = [$type, $coins];
        $this->addText(
            clienttranslate('This card is worth ${coins} coin(s) for each ${color} card in your city ${includingItself} at the time it is constructed.'),
            true,
            [
                'coins' => $coins,
                'color' => $this->getBuildingTypeString($type),
                'includingItself' => $type == Building::TYPE_YELLOW ? clienttranslate("(including itself)") : ''
            ]
        );
        return $this;
    }

    /**
     * @param int $coinsPerWonder
     * @return static
     */
    public function setCoinsPerWonder(int $coinsPerWonder) {
        $this->coinsPerWonder = $coinsPerWonder;
        $this->addText(clienttranslate("This card is worth 2 coins per Wonder constructed in your city at the time it is constructed."));
        return $this;
    }

    /**
     * @param bool $guildRewardWonders
     * @return static
     */
    public function setGuildRewardWonders(bool $guildRewardWonders) {
        $this->guildRewardWonders = $guildRewardWonders;
        $this->addText(clienttranslate("At the end of the game, this card is worth 2 victory points for each Wonder constructed in the city which has the most wonders."));
        return $this;
    }

    /**
     * @param bool $guildRewardCoinTriplets
     * @return static
     */
    public function setGuildRewardCoinTriplets(bool $guildRewardCoinTriplets) {
        $this->guildRewardCoinTriplets = $guildRewardCoinTriplets;
        $this->addText(clienttranslate("At the end of the game, this card is worth 1 victory point for each set of 3 coins in the richest city."));
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
            $this->addText(
                clienttranslate('At the time it is constructed, this card grants you 1 coin for each ${color} card in the city which has the most ${color} cards.'),
                true,
                ['color' => $buildingType]
            );
            $this->addText(
                clienttranslate('At the end of the game, this card is worth 1 victory point for each ${color} card constructed in the city which has the most ${color} cards.'),
                true,
                ['color' => $buildingType]
            );
        }
        else {
            $this->addText(clienttranslate("At the time it is constructed, this card grants you 1 coin for each brown and each grey card in the city which has the most brown and grey cards."));
            $this->addText(clienttranslate("At the end of the game, this card is worth 1 victory point for each brown and each grey card in the city which has the most brown and grey cards."));
        }
        return $this;
    }

    /**
     * @param int $listPage
     * @return static
     */
    public function setListPage(int $listPage) {
        $this->listPage = $listPage;
        return $this;
    }

    /**
     * @param array $listPosition
     * @return static
     */
    public function setListPosition(array $listPosition) {
        $this->listPosition = $listPosition;
        return $this;
    }

    /**
     * @param mixed $subType
     * @return static
     */
    public function setSubType($subType) {
        $this->subType = $subType;
        return $this;
    }

    /**
     * @param int $senateSection
     * @return static
     */
    public function setSenateSection(int $senateSection) {
        $this->senateSection = $senateSection;
        return $this;
    }

}