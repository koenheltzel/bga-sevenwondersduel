<?php

namespace SWD;

use SevenWondersDuelAgora;

class Decree extends Item {

    /**
     * @param $id
     * @return Decree
     */
    public static function get($id) {
        return Material::get()->decrees[$id];
    }

    public function controlChanged($gained) {
        $payment = new Payment($this);
        $this->constructEffects($gained ? Player::getActive() : Player::getActive()->getOpponent(), $payment);
        return $payment;
    }

}