<?php

namespace SWD;

use SevenWondersDuelPantheon;

class Conspiracy extends Item {

    /**
     * @param $id
     * @return Conspiracy
     */
    public static function get($id) {
        return Material::get()->conspiracies[$id];
    }

    public function __construct($id, $name) {
        $this->addText(clienttranslate('After preparing a Conspiracy with an Age card, it can be triggered at the start of a following turn, before playing an Age card.'), false);
        if ($id <= 16) {
            $this->addText('&nbsp;', false);
            $this->addText(clienttranslate('When you trigger this Conspiracy:'), false);
        }
        parent::__construct($id, $name);
    }
    /**
     * @param Conspiracy $building
     * @return PaymentPlan
     */
    public function choose(Player $player) {
        SevenWondersDuelPantheon::get()->conspiracyDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        // Text notification to all
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose a Conspiracy and placed it face down'),
            [
                'player_name' => $player->name,
                'playerId' => $player->id,
            ]
        );

        $position = SevenWondersDuelPantheon::get()->conspiracyDeck->getExtremePosition(true, $player->id);

        // Send conspiracy id to active player
        SevenWondersDuelPantheon::get()->notifyPlayer($player->id,
            'constructConspiracy',
            '',
            [
                'playerId' => $player->id,
                'conspiracyId' => $this->id,
                'conspiracyPosition' => $position,
            ]
        );

        // Don't send conspiracy id to the other player / spectators, only the picked conspiracy's position in the deck's player location
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'constructConspiracy',
            '',
            [
                'playerId' => $player->id,
                'conspiracyPosition' => $position,
            ]
        );
    }

    public function prepare(Player $player, $building) {
        SevenWondersDuelPantheon::get()->buildingDeck->moveCard($building->id, 'conspiracy' . $this->id);

        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'prepareConspiracy',
            clienttranslate('${player_name} prepared a Conspiracy using building “${buildingName}”'),
            [
                'i18n' => ['buildingName'],
                'position' => $this->getPosition($player),
                'buildingId' => $building->id,
                'buildingName' => $building->name,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'conspiraciesSituation' => Conspiracies::getSituation(),
            ]
        );
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     */
    public function trigger(Player $player) {
        // Set this conspiracy's "type_arg" to 1, which we use to indicate if the conspiracy is triggered.
        $sql = "UPDATE conspiracy SET card_type_arg = 1 WHERE card_id='{$this->id}'";
        self::DbQuery( $sql );

        $opponent = $player->getOpponent();

        $payment = new Payment(); // Triggering a conspiracy is free.

        // We want to send this notification first, before the detailed "effects" notifications.
        // However, the Payment object passed in this notification is by reference and this will contain
        // the effects' modifications when the notification is send at the end of the request.
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'triggerConspiracy',
            clienttranslate('${player_name} triggered Conspiracy “${conspiracyName}”'),
            [
                'i18n' => ['conspiracyName'],
                'conspiracyPosition' => $this->getPosition($player),
                'conspiracyId' => $this->id,
                'conspiracyName' => $this->name,
                'payment' => $payment,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'conspiraciesSituation' => Conspiracies::getSituation(),
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
                        clienttranslate('${player_name} takes half of ${opponent_name}\'s coin(s) (${coins}, rounded up) (Conspiracy “${conspiracyName}”)'),
                        [
                            'i18n' => ['conspiracyName'],
                            'player_name' => $player->name,
                            'opponent_name' => $opponent->name,
                            'coins' => $payment->coinsFromOpponent,
                            'conspiracyName' => $this->name,
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
//                    clienttranslate('${player_name} must choose a Progress token from the box (Conspiracy “${conspiracyName}”)'),
//                    [
//                        'i18n' => ['conspiracyName'],
//                        'player_name' => $player->name,
//                        'conspiracyName' => $this->name,
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
                        clienttranslate('${player_name} gets ${coins} coin(s), as many as Influence cubes he has in the Senate (Conspiracy “${conspiracyName}”)'),
                        [
                            'i18n' => ['conspiracyName'],
                            'player_name' => $player->name,
                            'coins' => $payment->coinReward,
                            'conspiracyName' => $this->name,
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
                            clienttranslate('${player_name} loses ${coins} coin(s), as many as Influence cubes he has in the Senate (Conspiracy “${conspiracyName}”)'),
                            [
                                'i18n' => ['conspiracyName'],
                                'player_name' => $opponent->name,
                                'coins' => $possibleOpponentCoinLoss,
                                'conspiracyName' => $this->name,
                            ]
                        );
                    }
                }
                break;
        }
        return $payment;
    }

    /**
     * Returns 0 if not prepared, else returns the age number of the building card that was used to prepare the conspiracy.
     * @return int
     */
    public function isPrepared() {
        if (isDevEnvironment()) {
            // Asume we are testing cost calculation
            return true;
        }
        else {
            $cards = SevenWondersDuelPantheon::get()->buildingDeck->getCardsInLocation('conspiracy' . $this->id);
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
            $card = SevenWondersDuelPantheon::get()->conspiracyDeck->getCard($this->id);
            return (int)$card['type_arg'];
        }
    }

    /*
     * Check if at least 1 of the Conspiracy's actions can have an effect
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
        return (int)SevenWondersDuelPantheon::get()->conspiracyDeck->getCard($this->id)['location_arg'];
    }

    protected function getScoreCategory() {
        return SevenWondersDuelPantheon::SCORE_WONDERS;
    }

}