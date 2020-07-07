<?php

namespace SWD;

use SevenWondersDuel;

class Wonder extends Item {

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
    public function construct(Player $player, $building) {
        $payment = parent::__construct($player);


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
            $cards = \SevenWondersDuel::get()->buildingDeck->getCardsInLocation('wonder' . $this->id);
            if (count($cards) > 0) {
                $card = array_shift($cards);
                return Building::get($card['id'])->age;
            }
            return 0;
        }
    }

}