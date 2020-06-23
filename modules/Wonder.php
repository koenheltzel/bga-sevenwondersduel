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
        \SevenWondersDuel::get()->wonderDeck->getCardsInLocation('wonder' . $this->id);
    }

}