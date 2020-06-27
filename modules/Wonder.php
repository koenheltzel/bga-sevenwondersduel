<?php

namespace SWD;

class Wonder extends Item {

    /**
     * @param $id
     * @return Wonder
     */
    public static function get($id) {
        return Material::get()->wonders[$id];
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
                return Building::get($card['type_arg'])->age;
            }
            return 0;
        }
    }

}