<?php

namespace SWD;

use SevenWondersDuelAgora;

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
        SevenWondersDuelAgora::get()->conspiracyDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        // Text notification to all
        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose a Conspiracy and placed it face down'),
            [
                'player_name' => $player->name,
                'playerId' => $player->id,
            ]
        );

        $position = SevenWondersDuelAgora::get()->conspiracyDeck->getExtremePosition(true, $player->id);

        // Send conspiracy id to active player
        SevenWondersDuelAgora::get()->notifyPlayer($player->id,
            'constructConspiracy',
            '',
            [
                'playerId' => $player->id,
                'conspiracyId' => $this->id,
                'conspiracyPosition' => $position,
            ]
        );

        // Don't send conspiracy id to the other player / spectators, only the picked conspiracy's position in the deck's player location
        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'constructConspiracy',
            '',
            [
                'playerId' => $player->id,
                'conspiracyPosition' => $position,
            ]
        );
    }

    public function prepare(Player $player, $building) {
        SevenWondersDuelAgora::get()->buildingDeck->moveCard($building->id, 'conspiracy' . $this->id);

        SevenWondersDuelAgora::get()->notifyAllPlayers(
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
        SevenWondersDuelAgora::get()->notifyAllPlayers(
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

                    SevenWondersDuelAgora::get()->notifyAllPlayers(
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
                SevenWondersDuelAgora::get()->progressTokenDeck->moveAllCardsInLocation('box', 'selection');
                SevenWondersDuelAgora::get()->progressTokenDeck->shuffle('selection'); // Ensures we have defined card_location_arg
                break;
            case 10:
//                SevenWondersDuelAgora::get()->notifyAllPlayers(
//                    'message',
//                    clienttranslate('${player_name} must choose a Progress token from the box (Conspiracy “${conspiracyName}”)'),
//                    [
//                        'i18n' => ['conspiracyName'],
//                        'player_name' => $player->name,
//                        'conspiracyName' => $this->name,
//                    ]
//                );

                SevenWondersDuelAgora::get()->progressTokenDeck->moveAllCardsInLocation('box', 'selection');
                SevenWondersDuelAgora::get()->progressTokenDeck->shuffle('selection'); // Ensures we have defined card_location_arg
                break;
            case 12:
                $payment->coinReward = 12 - $player->getCubes();
                if ($payment->coinReward > 0) {
                    $player->increaseCoins($payment->coinReward);

                    SevenWondersDuelAgora::get()->notifyAllPlayers(
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

                        SevenWondersDuelAgora::get()->notifyAllPlayers(
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
            $cards = SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation('conspiracy' . $this->id);
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
            $card = SevenWondersDuelAgora::get()->conspiracyDeck->getCard($this->id);
            return (int)$card['type_arg'];
        }
    }

    public function getPosition(Player $player) {
        return (int)SevenWondersDuelAgora::get()->conspiracyDeck->getCard($this->id)['location_arg'];
    }

    protected function getScoreCategory() {
        return SevenWondersDuelAgora::SCORE_WONDERS;
    }

}