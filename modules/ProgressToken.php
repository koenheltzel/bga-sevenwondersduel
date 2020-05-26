<?php

namespace SWD;

class ProgressToken extends Item
{

    public $id = 0;
    public $name = "";

    /**
     * @param $id
     * @return ProgressToken
     */
    public static function get($id) {
        global $progressTokens;
        return $progressTokens[$id];
    }

}