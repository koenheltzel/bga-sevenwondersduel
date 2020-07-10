<?php

namespace SWD;

use SevenWondersDuel;

class Wonder extends Item {

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
        $payment = parent::construct($player);

        SevenWondersDuel::get()->buildingDeck->moveCard($building->id, 'wonder' . $this->id);
        return $payment;
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
     * @param array $visualOpponentCoinLossPosition
     * @return static
     */
    public function setVisualOpponentCoinLossPosition(array $visualOpponentCoinLossPosition) {
        $this->visualOpponentCoinLossPosition = $visualOpponentCoinLossPosition;
        return $this;
    }

}