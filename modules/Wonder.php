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

    public function isConstructed() {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            // Asume we are testing cost calculation
            return true;
        }
        else {
            return count(\SevenWondersDuel::get()->wonderDeck->getCardsInLocation('wonder' . $this->id));
        }
    }

}