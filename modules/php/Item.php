<?php

namespace SWD;

use SevenWondersDuel;

class Item
{

    // Don't change these values without looking at their use in the Player class. Right now they need to stay 1 char.
    const TYPE_BUILDING = 'B';
    const TYPE_WONDER = 'W';
    const TYPE_PROGRESSTOKEN = 'P';

    public $id = 0;
    public $name = "";
    public $cost = []; // coins and or resources
    public $resources = [];
    public $resourceChoice = [];
    public $military = 0;
    public $victoryPoints = 0;
    public $coins = 0; // coins as a reward, not cost
    public $scientificSymbol = 0;

    /**
     * The visual position of the coin on the card. Percentages from the center of the card.
     * @var int
     */
    public $visualCoinPosition = [0, 0];
//    public $playEffects = [];
//    public $endEffects = [];

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Checks if player can afford the item, if so, remove the amount of coins from the player.
     * @param Player $player
     * @param $cardId
     * @return Payment
     */
    public function construct(Player $player, $building = null) {
        $payment = $player->calculateCost($this);
        $totalCost = $payment->totalCost();
        if ($totalCost > $player->getCoins()) {
            throw new \BgaUserException(clienttranslate("You can't afford the building/wonder you selected."));
        }

        if ($totalCost > 0) {
            $player->increaseCoins(-$totalCost);
        }

        return $payment;
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     * @param Payment $payment
     */
    protected function constructEffects(Player $player, Payment $payment) {
        if ($this->victoryPoints > 0) {
            $player->increaseScore($this->victoryPoints);

            SevenWondersDuel::get()->notifyAllPlayers(
                'simpleNotif',
                clienttranslate('${player_name} scores ${points} victory point(s).'),
                [
                    'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                    'points' => $this->victoryPoints,
                ]
            );
        }
        if ($this->coins > 0) {
            $player->increaseCoins($this->coins);

            SevenWondersDuel::get()->notifyAllPlayers(
                'simpleNotif',
                clienttranslate('${player_name} takes ${coins} coin(s) from the bank.'),
                [
                    'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                    'coins' => $this->coins,
                ]
            );
        }
        if ($this->military > 0) {
            MilitaryTrack::movePawn(Player::me(), $this->military, $payment);

            SevenWondersDuel::get()->notifyAllPlayers(
                'simpleNotif',
                clienttranslate('${player_name} moves the Conflict pawn ${steps} space(s).'),
                [
                    'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                    'steps' => $payment->militarySteps,
                ]
            );

            list($payment->militaryTokenNumber, $payment->militaryTokenValue) = MilitaryTrack::getMilitaryToken();
            if ($payment->militaryTokenValue > 0) {
                $opponent = Player::opponent($player->id);
                $payment->militaryOpponentPays = min($payment->militaryTokenValue, $opponent->getCoins());
                if($payment->militaryOpponentPays > 0) {
                    $opponent->increaseCoins(-$payment->militaryOpponentPays);

                    SevenWondersDuel::get()->notifyAllPlayers(
                        'simpleNotif',
                        clienttranslate('The military token is removed, ${player_name} discards ${coins} coin(s).'),
                        [
                            'player_name' => Player::opponent()->name,
                            'coins' => $payment->militaryOpponentPays,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @param array $cost
     * @return static
     */
    public function setCost($cost) {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @param array $resources
     * @return static
     */
    public function setResources($resources) {
        $this->resources = $resources;
        return $this;
    }

    /**
     * @param int $military
     * @return static
     */
    public function setMilitary($military) {
        $this->military = $military;
        return $this;
    }

    /**
     * @param int $victoryPoints
     * @return static
     */
    public function setVictoryPoints(int $victoryPoints) {
        $this->victoryPoints = $victoryPoints;
        return $this;
    }

    /**
     * @param int $coins
     * @return static
     */
    public function setCoins(int $coins) {
        $this->coins = $coins;
        return $this;
    }

    /**
     * @param int $scientificSymbol
     * @return static
     */
    public function setScientificSymbol(int $scientificSymbol) {
        $this->scientificSymbol = $scientificSymbol;
        return $this;
    }

    /**
     * @param array $resourceChoice
     * @return static
     */
    public function setResourceChoice(array $resourceChoice) {
        $this->resourceChoice = $resourceChoice;
        return $this;
    }

    /**
     * @param array $visualCoinPosition
     * @return static
     */
    public function setVisualCoinPosition(array $visualCoinPosition) {
        $this->visualCoinPosition = $visualCoinPosition;
        return $this;
    }

}