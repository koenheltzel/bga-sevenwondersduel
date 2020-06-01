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

}