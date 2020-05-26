<?php

namespace SWD;

class Wonder extends Item {

    /**
     * @param $id
     * @return Wonder
     */
    public static function get($id) {
        global $wonders;
        return $wonders[$id];
    }

}