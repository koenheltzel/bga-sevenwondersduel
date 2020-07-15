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
    public $guildRewardBuildingTypes = [];

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
     * @return Payment
     */
    public function construct(Player $player, $building = null) {
        $payment = parent::construct($player);

        SevenWondersDuel::get()->buildingDeck->moveCard($this->id, $player->id);

        // We want to send this notification first, before the detailed "effects" notifications.
        // However, the Payment object passed in this notification is by reference and this will contain
        // the effects' modifications when the notification is send at the end of the request.
        SevenWondersDuel::get()->notifyAllPlayers(
            'constructBuilding',
            clienttranslate('${player_name} constructed building “${buildingName}” for ${cost}.'),
            [
                'buildingName' => $this->name,
                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() . " " . COINS : 'free',
                'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $this->id,
                'payment' => $payment,
            ]
        );

        $this->constructEffects($player, $payment);

        return $payment;
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     * @param Payment $payment
     */
    protected function constructEffects(Player $player, Payment $payment) {
        parent::constructEffects($player, $payment);

        if ($this->scientificSymbol) {
            $buildings = Player::me()->getBuildings()->filterByScientificSymbol($this->scientificSymbol);
            if (count($buildings->array) == 2) {
                $payment->newScientificSymbolPair = true;

                SevenWondersDuel::get()->notifyAllPlayers(
                    'simpleNotif',
                    clienttranslate('${player_name} gathered a pair of identical scientific symbols, and may now choose a Progress token.'),
                    [
                        'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                    ]
                );
            }
        }

        if ($player->hasBuilding($this->linkedBuilding) && $player->hasProgressToken(10)) {
            $payment->urbanismAward = 4;
            $player->increaseCoins($payment->urbanismAward);

            SevenWondersDuel::get()->notifyAllPlayers(
                'simpleNotif',
                clienttranslate('${player_name} gets 4 coins from Progress token ${progressTokenName}.'), // TODO does the progressTokenName get translated this way?
                [
                    'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                    'progressTokenName' => ProgressToken::get(10)->name,
                ]
            );
        }

        if($this->coinsPerBuildingOfType) {
            $buildingsOfType = $player->getBuildings()->filterByTypes([$this->coinsPerBuildingOfType[0]]);
            $buildingsCount = count($buildingsOfType->array);
            if ($buildingsCount > 0){
                $payment->coinsPerBuildingOfType = $buildingsCount * $this->coinsPerBuildingOfType[1];
                $player->increaseCoins($payment->coinsPerBuildingOfType);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'simpleNotif',
                    clienttranslate('${player_name} gets ${coins} coin(s), ${coinsPerBuilding} for each ${buildingType} building in his/her city.'),
                    [
                        'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                        'coins' => $payment->coinsPerBuildingOfType,
                        'coinsPerBuilding' => $this->coinsPerBuildingOfType[1],
                        'buildingType' => $this->coinsPerBuildingOfType[0],
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

        SevenWondersDuel::get()->buildingDeck->moveCard($this->id, 'discard');
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