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
     * @return Payment
     */
    public function construct(Player $player, $building = null) {
        $payment = parent::construct($player, $building);

        SevenWondersDuel::get()->buildingDeck->moveCard($building->id, 'wonder' . $this->id);

        SevenWondersDuel::get()->notifyAllPlayers(
            'constructWonder',
            clienttranslate('${player_name} constructed wonder ${wonderName} for ${cost} using building ${buildingName}.'),
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

        // TODO add extra turn
        // TODO opponent loses coins

//        if ($this->scientificSymbol) {
//            $buildings = Player::me()->getBuildings()->filterByScientificSymbol($this->scientificSymbol);
//            if (count($buildings->array) == 2) {
//                $payment->newScientificSymbolPair = true;
//
//                SevenWondersDuel::get()->notifyAllPlayers(
//                    'simpleNotif',
//                    clienttranslate('${player_name} gathered a pair of identical scientific symbols, and may now choose a Progress token.'),
//                    [
//                        'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
//                    ]
//                );
//            }
//        }
    }

    /**
     * Returns 0 if not constructed, else returns the age number of the building card that was used to construct the wonder.
     * @return int
     */
    public function isConstructed() {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
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

    /**
     * @param bool $extraTurn
     * @return static
     */
    public function setExtraTurn() {
        $this->extraTurn = true;
        return $this;
    }

    /**
     * @param int $opponentCoinLoss
     * @return static
     */
    public function setOpponentCoinLoss(int $opponentCoinLoss) {
        $this->opponentCoinLoss = $opponentCoinLoss;
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
        return $this;
    }

    /**
     * @param bool $discardOpponentBuilding
     * @return static
     */
    public function setDiscardOpponentBuilding(string $buildingType) {
        $this->discardOpponentBuilding = $buildingType;
        return $this;
    }

    /**
     * @param bool $progressTokenFromBox
     * @return static
     */
    public function setProgressTokenFromBox() {
        $this->progressTokenFromBox = true;
        return $this;
    }

}