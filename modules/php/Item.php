<?php

namespace SWD;

use SevenWondersDuel;

class Item extends Base
{

    // Don't change these values without looking at their use in the Player class. Right now they need to stay 1 char.
    const TYPE_BUILDING = 'B';
    const TYPE_WONDER = 'W';
    const TYPE_PROGRESSTOKEN = 'P';

    public $id = 0;
    public $name = "";
    public $text = [];
    public $cost = []; // coins and or resources
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

    public function __construct($id, $name, Array $text = []) {
        $this->id = $id;
        $this->name = $name;
        $this->text = $text;
    }

    /**
     * Checks if player can afford the item, if so, remove the amount of coins from the player.
     * @param Player $player
     * @param $cardId
     * @return PaymentPlan
     */
    public function construct(Player $player, $building = null, $discardedCard = false) {
        if ($discardedCard) {
            $payment = new Payment($this);
            $payment->discardedCard = true;
        }
        else {
            $payment = new Payment($this);
            $payment->calculate($player);
        }

        $totalCost = $payment->totalCost();
        if ($totalCost > $player->getCoins()) {
            $itemType = $this instanceof Building ? clienttranslate('Building') : clienttranslate('Wonder');
            throw new \BgaUserException(sprintf(clienttranslate("You can't afford the %s you selected."), $itemType));
        }

        if ($totalCost > 0) {
            $player->increaseCoins(-$totalCost);
        }

        return $payment;
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     * @param PaymentPlan $payment
     */
    protected function constructEffects(Player $player, PaymentPlan $payment) {
        // Economy Progress Token
        if ($player->getOpponent()->hasProgressToken(3) && $payment->totalCost() > 0) {
            foreach($payment->steps as $paymentStep) {
                if ($paymentStep->resource != COINS) { // LINKED_BUILDING is already filtered out by the totalCost > 0 if statement.
                    $payment->economyProgressTokenCoins += $paymentStep->cost;
                }
            }

            if ($payment->economyProgressTokenCoins > 0) {
                $player->getOpponent()->increaseCoins($payment->economyProgressTokenCoins);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${coins} coin(s) of the cost for ${item_name} go to ${player_name} (“${progressTokenName}” Progress token)'),
                    [
                        'i18n' => ['item_name', 'progressTokenName'],
                        'player_name' => $player->getOpponent()->name,
                        'item_name' => $payment->getItem()->name,
                        'coins' => $payment->economyProgressTokenCoins,
                        'progressTokenName' => ProgressToken::get(3)->name,
                    ]
                );
            }
        }

        if ($this->victoryPoints > 0) {
            $player->increaseScore($this->victoryPoints, $this->getScoreCategory());

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} scores ${points} victory point(s)'),
                [
                    'player_name' => $player->name,
                    'points' => $this->victoryPoints,
                ]
            );
        }
        if ($this->coins > 0) {
            $payment->coinReward = $this->coins;
            $player->increaseCoins($payment->coinReward);

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} takes ${coins} coin(s) from the bank'),
                [
                    'player_name' => $player->name,
                    'coins' => $payment->coinReward,
                ]
            );
        }
        if ($this->military > 0) {
            MilitaryTrack::movePawn($player, $this->military, $payment);

            if($player->hasProgressToken(8) && $payment->getItem() instanceof Building) {
                $message = clienttranslate('${player_name} moves the Conflict pawn ${steps} space(s) (+1 from Progress token “${progressTokenName}”)');
            }
            else {
                $message = clienttranslate('${player_name} moves the Conflict pawn ${steps} space(s)');
            }

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                $message,
                [
                    'i18n' => ['progressTokenName'],
                    'player_name' => $player->name,
                    'steps' => $payment->militarySteps,
                    'progressTokenName' => ProgressToken::get(8)->name, //Strategy
                ]
            );

            list($payment->militaryTokenNumber, $payment->militaryTokenValue) = MilitaryTrack::getMilitaryToken();
            if ($payment->militaryTokenValue > 0) {
                $opponent = $player->getOpponent();
                $payment->militaryOpponentPays = min($payment->militaryTokenValue, $opponent->getCoins());
                if($payment->militaryOpponentPays > 0) {
                    $opponent->increaseCoins(-$payment->militaryOpponentPays);

                    SevenWondersDuel::get()->notifyAllPlayers(
                        'message',
                        clienttranslate('The military token is removed, ${player_name} discards ${coins} coin(s)'),
                        [
                            'player_name' => $opponent->name,
                            'coins' => $payment->militaryOpponentPays,
                        ]
                    );
                }
            }
        }
    }

    protected function getItemType() {
        if ($this instanceof Building) {
            return self::_('building');
        }
        if ($this instanceof Wonder) {
            return self::_('wonder');
        }
        if ($this instanceof ProgressToken) {
            return self::_('progress token');
        }
    }

    protected function getScoreCategory() {
        return '';
    }

    /**
     * @param string $text
     * @return static
     */
    public function addText(string $text, $bulletPoint=true) {
        $this->text[] = [$text, $bulletPoint];
        return $this;
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
     * @param int $military
     * @return static
     */
    public function setMilitary($military) {
        $this->military = $military;
        $this->addText(sprintf(self::_('This %s is worth %d Shield(s).'), $this->getItemType(), $military),false);
        return $this;
    }

    /**
     * @param int $victoryPoints
     * @return static
     */
    public function setVictoryPoints(int $victoryPoints) {
        $this->victoryPoints = $victoryPoints;
        $this->addText(sprintf(self::_('This %s is worth %d victory point(s).'), $this->getItemType(), $victoryPoints));
        return $this;
    }

    /**
     * @param int $coins
     * @return static
     */
    public function setCoins(int $coins) {
        $this->coins = $coins;
        $this->addText(sprintf(self::_('You take %d coins from the bank.'), $coins));
        return $this;
    }

    /**
     * @param int $scientificSymbol
     * @return static
     */
    public function setScientificSymbol(int $scientificSymbol) {
        $this->scientificSymbol = $scientificSymbol;
        $this->addText(sprintf(self::_('This %s is worth a scientific symbol.'), $this->getItemType()));
        return $this;
    }

    /**
     * @param array $resourceChoice
     * @return static
     */
    public function setResourceChoice(array $resourceChoice) {
        $this->resourceChoice = $resourceChoice;
        $this->addText(sprintf(self::_("This %s produces one unit of one of the resources shown for you each turn."), $this->getItemType()));
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