<?php

namespace SWD;

use SevenWondersDuelAgora;

class Decree extends Item {

    /**
     * @param $id
     * @return Wonder
     */
    public static function get($id) {
        return Material::get()->decrees[$id];
    }

}