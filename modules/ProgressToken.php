<?php

namespace SWD;

class ProgressToken extends Item
{

    /**
     * @param $id
     * @return ProgressToken
     */
    public static function get($id) {
        return Material::get()->progressTokens[$id];
    }

}