<?php

namespace SWD;

use SevenWondersDuelPantheon;

class Divinity extends Item {

    public const TYPE_GREEN = 1;
    public const TYPE_YELLOW = 2;
    public const TYPE_BLUE = 3;
    public const TYPE_GREY = 4;
    public const TYPE_RED = 5;
    public const TYPE_GATE = 6;

    public $type;
    public $typeName;

    /**
     * @param $id
     * @return Divinity
     */
    public static function get($id) {
        return Material::get()->divinities[$id];
    }

    public function __construct($id, $name, $type) {
        $this->type = $type;
        $this->typeName = self::getTypeName($type);
//        $this->addText(clienttranslate('After preparing a Divinity with an Age card, it can be triggered at the start of a following turn, before playing an Age card.'), false);
//        if ($id <= 16) {
//            $this->addText('&nbsp;', false);
//            $this->addText(clienttranslate('When you trigger this Divinity:'), false);
//        }
        parent::__construct($id, $name);
    }

    public static function getTypeName($type) {
        switch ($type) {
            case Divinity::TYPE_GREEN:
                return clienttranslate('Mesopotamian');
            case Divinity::TYPE_YELLOW:
                return clienttranslate('Phoenician');
            case Divinity::TYPE_BLUE:
                return clienttranslate('Greek');
            case Divinity::TYPE_GREY:
                return clienttranslate('Egyptian');
            case Divinity::TYPE_RED:
                return clienttranslate('Roman');
            case Divinity::TYPE_GATE:
                return clienttranslate('Gate');
        }
    }

    public function place($space) {
        SevenWondersDuelPantheon::get()->divinityDeck->moveCard($this->id, "space{$space}");

        $player = Player::getActive();

        // Send divinity id to active player
        SevenWondersDuelPantheon::get()->notifyPlayer($player->id,
            'placeDivinity',
            '',
            [
                'playerId' => $player->id,
                'divinityId' => $this->id,
                'divinityType' => $this->type,
                'space' => $space,
            ]
        );

        // Don't send divinity id to the other player / spectators, only the picked divinity's position in the deck's player location
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'placeDivinity',
            '',
            [
                'playerId' => $player->id,
                'divinityType' => $this->type,
                'space' => $space,
            ]
        );
    }

    /**
     * @param Divinity $building
     * @return PaymentPlan
     */
    public function activate(Player $player) {
        SevenWondersDuelPantheon::get()->divinityDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        // Text notification to all
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose a Divinity and placed it face down'),
            [
                'player_name' => $player->name,
                'playerId' => $player->id,
            ]
        );

        $position = SevenWondersDuelPantheon::get()->divinityDeck->getExtremePosition(true, $player->id);

        // Send divinity id to active player
        SevenWondersDuelPantheon::get()->notifyPlayer($player->id,
            'constructDivinity',
            '',
            [
                'playerId' => $player->id,
                'divinityId' => $this->id,
                'divinityPosition' => $position,
            ]
        );

        // Don't send divinity id to the other player / spectators, only the picked divinity's position in the deck's player location
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'constructDivinity',
            '',
            [
                'playerId' => $player->id,
                'divinityPosition' => $position,
            ]
        );
    }

    public function prepare(Player $player, $building) {
        SevenWondersDuelPantheon::get()->buildingDeck->moveCard($building->id, 'divinity' . $this->id);

        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'prepareDivinity',
            clienttranslate('${player_name} prepared a Divinity using building “${buildingName}”'),
            [
                'i18n' => ['buildingName'],
                'position' => $this->getPosition($player),
                'buildingId' => $building->id,
                'buildingName' => $building->name,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'divinitiesSituation' => Divinities::getSituation(),
            ]
        );
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     */
    public function trigger(Player $player) {
        // Set this divinity's "type_arg" to 1, which we use to indicate if the divinity is triggered.
        $sql = "UPDATE divinity SET card_type_arg = 1 WHERE card_id='{$this->id}'";
        self::DbQuery( $sql );

        $opponent = $player->getOpponent();

        $payment = new Payment(); // Triggering a divinity is free.

        // We want to send this notification first, before the detailed "effects" notifications.
        // However, the Payment object passed in this notification is by reference and this will contain
        // the effects' modifications when the notification is send at the end of the request.
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'triggerDivinity',
            clienttranslate('${player_name} triggered Divinity “${divinityName}”'),
            [
                'i18n' => ['divinityName'],
                'divinityPosition' => $this->getPosition($player),
                'divinityId' => $this->id,
                'divinityName' => $this->name,
                'payment' => $payment,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'divinitiesSituation' => Divinities::getSituation(),
            ]
        );

        parent::constructEffects($player, $payment);

        switch($this->id) {
            case 2:
                $payment->coinsFromOpponent = ceil($opponent->getCoins() / 2);
                if ($payment->coinsFromOpponent > 0) {
                    $opponent->increaseCoins(-$payment->coinsFromOpponent);
                    $player->increaseCoins($payment->coinsFromOpponent);

                    SevenWondersDuelPantheon::get()->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name} takes half of ${opponent_name}\'s coin(s) (${coins}, rounded up) (Divinity “${divinityName}”)'),
                        [
                            'i18n' => ['divinityName'],
                            'player_name' => $player->name,
                            'opponent_name' => $opponent->name,
                            'coins' => $payment->coinsFromOpponent,
                            'divinityName' => $this->name,
                        ]
                    );
                }
                break;
            case 5:
                SevenWondersDuelPantheon::get()->progressTokenDeck->moveAllCardsInLocation('box', 'selection');
                SevenWondersDuelPantheon::get()->progressTokenDeck->shuffle('selection'); // Ensures we have defined card_location_arg
                break;
            case 10:
//                SevenWondersDuelPantheon::get()->notifyAllPlayers(
//                    'message',
//                    clienttranslate('${player_name} must choose a Progress token from the box (Divinity “${divinityName}”)'),
//                    [
//                        'i18n' => ['divinityName'],
//                        'player_name' => $player->name,
//                        'divinityName' => $this->name,
//                    ]
//                );

                SevenWondersDuelPantheon::get()->progressTokenDeck->moveAllCardsInLocation('box', 'selection');
                SevenWondersDuelPantheon::get()->progressTokenDeck->shuffle('selection'); // Ensures we have defined card_location_arg
                break;
            case 12:
                $payment->coinReward = 12 - $player->getCubes();
                if ($payment->coinReward > 0) {
                    $player->increaseCoins($payment->coinReward);

                    SevenWondersDuelPantheon::get()->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name} gets ${coins} coin(s), as many as Influence cubes he has in the Senate (Divinity “${divinityName}”)'),
                        [
                            'i18n' => ['divinityName'],
                            'player_name' => $player->name,
                            'coins' => $payment->coinReward,
                            'divinityName' => $this->name,
                        ]
                    );
                }
                $payment->opponentCoinLoss = 12 - $opponent->getCubes();
                if ($payment->opponentCoinLoss > 0) {
                    $possibleOpponentCoinLoss = min($opponent->getCoins(), $payment->opponentCoinLoss);
                    if ($possibleOpponentCoinLoss > 0) {
                        $payment->opponentCoinLoss = $possibleOpponentCoinLoss;
                        $opponent->increaseCoins(-$possibleOpponentCoinLoss);

                        SevenWondersDuelPantheon::get()->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} loses ${coins} coin(s), as many as Influence cubes he has in the Senate (Divinity “${divinityName}”)'),
                            [
                                'i18n' => ['divinityName'],
                                'player_name' => $opponent->name,
                                'coins' => $possibleOpponentCoinLoss,
                                'divinityName' => $this->name,
                            ]
                        );
                    }
                }
                break;
        }
        return $payment;
    }

    /**
     * Returns 0 if not prepared, else returns the age number of the building card that was used to prepare the divinity.
     * @return int
     */
    public function isPrepared() {
        if (isDevEnvironment()) {
            // Asume we are testing cost calculation
            return true;
        }
        else {
            $cards = SevenWondersDuelPantheon::get()->buildingDeck->getCardsInLocation('divinity' . $this->id);
            if (count($cards) > 0) {
                $card = array_shift($cards);
                return Building::get($card['id'])->age;
            }
            return 0;
        }
    }

    public function isTriggered() {
        if (isDevEnvironment()) {
            // Asume we are testing cost calculation
            return true;
        }
        else {
            $card = SevenWondersDuelPantheon::get()->divinityDeck->getCard($this->id);
            return (int)$card['type_arg'];
        }
    }

    /*
     * Check if at least 1 of the Divinity's actions can have an effect
     */
    public function isUsefulToTrigger(Player $player) {
        $opponent = $player->getOpponent();
        switch ($this->id) {
            case 1:
                // Check if opponent has unconstructed Wonders
                $action1 = count($opponent->getWonders()->filterByConstructed(false)->array) > 0;
                $action2 = $player->getCubes() < 12;
                return $action1 || $action2;
                break;
            case 2:
                // Check if opponent has coins
                $action1 = $opponent->getCoins() > 0;
                $action2 = $player->getCubes() < 12;
                return $action1 || $action2;
                break;
            case 3:
            case 4:
                // Check if opponent has blue/yellow buildings Wonders
                $action1 = count($opponent->getBuildings()->filterByTypes([($this->id == 3) ? Building::TYPE_BLUE : Building::TYPE_YELLOW])->array) > 0;
                $action2 = $player->getCubes() < 12;
                return $action1 || $action2;
                break;
            case 5:
                // There's a always a Progress token available to lock away
                return true;
                break;
            case 6:
                // Military shields are always "useful"
                return true;
                break;
            case 7;
                $lastRowBuildings = Draftpool::getLastRowBuildings();
                $lastRowBuildingsCount = count(Draftpool::getLastRowBuildings()->array);
                $lastRowSenatorsCount = count($lastRowBuildings->filterByTypes([Building::TYPE_SENATOR])->array);
                return $lastRowBuildingsCount - $lastRowSenatorsCount > 0;
                break;
            case 8:
                // The cards in the box shouldn't be touched so this action is always useful.
                return true;
                break;
            case 9:
                $action1 = $player->getCubes() > 0;
                $action2 = $opponent->getCubes() < 12;
                $action3 = $player->getCubes() < 12;
                return $action1 || $action2 || $action3;
                break;
            case 10:
                // There's a always a Progress token available in the box.
                return true;
                break;
            case 11:
                // Are there cards left in the current age?
                $action1 = Draftpool::countCardsInCurrentAge() > 0;
                $action2 = $player->getCubes() < 12;
                return $action1 || $action2;
                break;
            case 12:
                // Does the player have cubes in the senate?
                $action1 = $player->getCubes() < 12;
                // Does the opponent have cubes in the senate and does he have coins to lose?
                $action2 = $opponent->getCubes() < 12 && $opponent->getCoins() > 0;
                return $action1 || $action2;
                break;
            case 13:
                // Check if opponent has brown/gray buildings Wonders
                return count($opponent->getBuildings()->filterByTypes([Building::TYPE_BROWN, Building::TYPE_GREY])->array) > 0;
                break;
            case 14:
                // Check if there are blue cards to swap or green cards to swap.
                $playerGreenCount = count($player->getBuildings()->filterByTypes([Building::TYPE_GREEN])->array);
                $playerBlueCount = count($player->getBuildings()->filterByTypes([Building::TYPE_BLUE])->array);
                $opponentGreenCount = count($opponent->getBuildings()->filterByTypes([Building::TYPE_GREEN])->array);
                $opponentBlueCount = count($opponent->getBuildings()->filterByTypes([Building::TYPE_BLUE])->array);
                $action1 = ($playerGreenCount > 0 && $opponentGreenCount > 0) || ($playerBlueCount > 0 && $opponentBlueCount > 0);
                $action2 = $player->getCubes() < 12;
                return $action1 || $action2;
                break;
            case 15:
                // There's always a Decree to move.
                return true;
                break;
            case 16:
                // Check if opponent has constructed Wonders
                return count($opponent->getWonders()->filterByConstructed(true)->array) > 0;
                break;
        }
    }

    public function getPosition(Player $player) {
        return (int)SevenWondersDuelPantheon::get()->divinityDeck->getCard($this->id)['location_arg'];
    }

    protected function getScoreCategory() {
        return SevenWondersDuelPantheon::SCORE_WONDERS;
    }

}