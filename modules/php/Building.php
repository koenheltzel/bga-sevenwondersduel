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
    public $chain = null; // coins and or resources
    public $scienceSymbol = null; // coins and or resources
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

    public function __construct($id, $age, $name, $type) {
        $this->age = $age;
        $this->type = $type;
        parent::__construct($id, $name);
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
            $message = clienttranslate('${player_name} constructed discarded building “${buildingName}” for free (Wonder “${wonderName}”).');
        }
        else {
            $message = clienttranslate('${player_name} constructed building “${buildingName}” for ${cost}.');
        }
        SevenWondersDuel::get()->notifyAllPlayers(
            'constructBuilding',
            $message,
            [
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
                        clienttranslate('${player_name} gathered a pair of identical scientific symbols, and may now choose a Progress token.'),
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
                clienttranslate('${player_name} gets 4 coins (Progress token “${progressTokenName}”).'),
                [
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
                    clienttranslate('${player_name} gets ${coins} coin(s), ${coinsPerBuilding} for each ${buildingType} building in his/her city.'),
                    [
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
                    clienttranslate('${player_name} gets ${coins} coin(s), 1 for each ${buildingType} building in the city which has the most of them (${mostPlayerName}\'s).'),
                    [
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
                    clienttranslate('${player_name} gets ${coins} coin(s), ${coinsPerWonder} for each constructed Wonder in his/her city.'),
                    [
                        'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                        'coins' => $payment->coinReward,
                        'coinsPerWonder' => $this->coinsPerWonder,
                    ]
                );
            }
        }
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
     * @param array $fixedPriceResources
     * @return static
     */
    public function setFixedPriceResources(array $fixedPriceResources) {
        $this->fixedPriceResources = $fixedPriceResources;
        return $this;
    }

    /**
     * @param int $linkedBuilding
     * @return static
     */
    public function setLinkedBuilding(int $linkedBuilding) {
        $this->linkedBuilding = $linkedBuilding;
        return $this;
    }

    /**
     * @param array $coinsPerBuildingOfType
     * @return static
     */
    public function setCoinsPerBuildingOfType(string $type, int $coins) {
        $this->coinsPerBuildingOfType = [$type, $coins];
        return $this;
    }

    /**
     * @param int $coinsPerWonder
     * @return static
     */
    public function setCoinsPerWonder(int $coinsPerWonder) {
        $this->coinsPerWonder = $coinsPerWonder;
        return $this;
    }

    /**
     * @param bool $guildRewardWonders
     * @return static
     */
    public function setGuildRewardWonders(bool $guildRewardWonders) {
        $this->guildRewardWonders = $guildRewardWonders;
        return $this;
    }

    /**
     * @param bool $guildRewardCoinTriplets
     * @return static
     */
    public function setGuildRewardCoinTriplets(bool $guildRewardCoinTriplets) {
        $this->guildRewardCoinTriplets = $guildRewardCoinTriplets;
        return $this;
    }

    /**
     * @param array $guildRewardBuildingTypes
     * @return static
     */
    public function setGuildRewardBuildingTypes(array $guildRewardBuildingTypes) {
        $this->guildRewardBuildingTypes = $guildRewardBuildingTypes;
        return $this;
    }

}