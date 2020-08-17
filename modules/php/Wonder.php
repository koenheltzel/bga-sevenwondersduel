<?php

namespace SWD;

use SevenWondersDuel;

class Wonder extends Item {

    public $extraTurn = false;
    public $opponentCoinLoss = 0;
    public $constructDiscardedBuilding = false;
    public $discardOpponentBuilding = false;
    public $progressTokenFromBox = false;

    /**
     * The visual position of the opponent coin loss on the card. Percentages from the center of the card.
     * @var int
     */
    public $visualOpponentCoinLossPosition = [0, 0];

    /**
     * @param $id
     * @return Wonder
     */
    public static function get($id) {
        return Material::get()->wonders[$id];
    }

    public function checkWonderAvailable() {
        if (!in_array($this->id, Player::me()->getWonderIds())) {
            throw new \BgaUserException( clienttranslate("The wonder you selected is not available.") );
        }

        if ($this->isConstructed()) {
            throw new \BgaUserException( clienttranslate("The wonder you selected has already been constructed.") );
        }
    }

    /**
     * @param Building $building
     * @return PaymentPlan
     */
    public function construct(Player $player, $building = null, $discardedBuilding = false) {
        $payment = parent::construct($player, $building, $discardedBuilding);

        SevenWondersDuel::get()->buildingDeck->moveCard($building->id, 'wonder' . $this->id);

        SevenWondersDuel::get()->notifyAllPlayers(
            'constructWonder',
            clienttranslate('${player_name} constructed wonder “${wonderName}” for ${cost} using building “${buildingName}”'),
            [
                'wonderId' => $this->id,
                'wonderName' => $this->name,
                'buildingId' => $building->id,
                'buildingName' => $building->name,
                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() . " " . COINS : 'free',
                'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'payment' => $payment,
                'wondersSituation' => Wonders::getSituation(),
            ]
        );

        $eightWonder = null;
        foreach(array_merge(Player::me()->getWonders()->array, Player::opponent()->getWonders()->array) as $wonder) {
            if (!$wonder->isConstructed()) {
                if ($eightWonder) {
                    // Found a second unconstructed wonder, means there aren't 7 wonders constructed yet.
                    $eightWonder = null;
                    break;
                }
                $eightWonder = $wonder;
            }
        }
        if ($eightWonder) {
            SevenWondersDuel::get()->wonderDeck->moveCard($eightWonder->id, 'box');
            $payment->eightWonderId = $eightWonder->id;
            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name}\'s Wonder “${wonderName}” is removed from the game because 7 Wonders have been constructed'),
                [
                    'player_name' => Player::me()->hasWonder($eightWonder->id) ? Player::me()->name : Player::opponent()->name,
                    'wonderName' => $eightWonder->name,
                ]
            );
        }

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

        // Set extra turn if the wonder provides it or if the player has progress token Theology.
        if ($this->extraTurn) {
            SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL, 1);
        }
        elseif($player->hasProgressToken(9)) {
            SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY, 1);
        }

        if ($this->opponentCoinLoss > 0) {
            $opponentCoinLoss = min($player->getOpponent()->getCoins(), $this->opponentCoinLoss);
            if ($opponentCoinLoss > 0) {
                $payment->opponentCoinLoss = $opponentCoinLoss;
                $player->getOpponent()->increaseCoins(-$opponentCoinLoss);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} loses ${coins} coin(s)'),
                    [
                        'player_name' => $player->getOpponent()->name,
                        'coins' => $opponentCoinLoss,
                    ]
                );
            }
        }

        // Note: the extra turn effect is handled in NextPlayerTurnTrait so we can also indicate if the extra turn is lost due to the age ending.
    }

    /**
     * Returns 0 if not constructed, else returns the age number of the building card that was used to construct the wonder.
     * @return int
     */
    public function isConstructed() {
        if (!strstr($_SERVER['HTTP_HOST'], 'boardgamearena.com')) {
            // Asume we are testing cost calculation
            return true;
        }
        else {
            $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation('wonder' . $this->id);
            if (count($cards) > 0) {
                $card = array_shift($cards);
                return Building::get($card['id'])->age;
            }
            return 0;
        }
    }

    protected function getScoreCategory() {
        return SevenWondersDuel::SCORE_WONDERS;
    }

    /**
     * @param bool $extraTurn
     * @return static
     */
    public function setExtraTurn() {
        $this->extraTurn = true;
        $this->text[] = clienttranslate("Immediately play a second turn.");
        return $this;
    }

    /**
     * @param int $opponentCoinLoss
     * @return static
     */
    public function setOpponentCoinLoss(int $opponentCoinLoss) {
        $this->opponentCoinLoss = $opponentCoinLoss;
        $this->text[] = sprintf(self::_("Your opponent loses %d coins, which are returned to the bank."), $opponentCoinLoss);
        return $this;
    }

    /**
     * @param array $visualOpponentCoinLossPosition
     * @return static
     */
    public function setVisualOpponentCoinLossPosition(array $visualOpponentCoinLossPosition) {
        $this->visualOpponentCoinLossPosition = $visualOpponentCoinLossPosition;
        return $this;
    }

    /**
     * @param bool $constructDiscardedBuilding
     * @return static
     */
    public function setConstructDiscardedBuilding() {
        $this->constructDiscardedBuilding = true;
        $this->text[] = clienttranslate("Take all of the cards which have been discarded since the beginning of the game and immediately construct one of your choice for free.");
        return $this;
    }

    /**
     * @param bool $discardOpponentBuilding
     * @return static
     */
    public function setDiscardOpponentBuilding(string $buildingType) {
        $this->discardOpponentBuilding = $buildingType;
        if ($buildingType == Building::TYPE_BROWN) {
            $this->text[] = clienttranslate("Put in the discard pile one brown card (Raw goods) of your choice constructed by their opponent.");
        }
        elseif ($buildingType == Building::TYPE_GREY) {
            $this->text[] = clienttranslate("Place in the discard pile a grey card (manufactured goods) of your choice constructed by your opponent.");
        }
        return $this;
    }

    /**
     * @param bool $progressTokenFromBox
     * @return static
     */
    public function setProgressTokenFromBox() {
        $this->progressTokenFromBox = true;
        $this->text[] = clienttranslate("Randomly draw 3 Progress tokens from among those discarded at the beginning of the game. Choose one, play it, and return the other 2 to the box.");
        return $this;
    }

}