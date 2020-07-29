<?php


namespace SWD;


use SevenWondersDuel;

class Base extends \APP_DbObject
{

    /**
     * Make _() available to our classes through self::() so the project checker doesn't complain.
     * @param $string
     * @return mixed
     */
    function _($string) {
        if (!strstr($_SERVER['HTTP_HOST'], 'boardgamearena.com')) {
            return $string;
        }
        else {
            return SevenWondersDuel::get()->_($string);
        }
    }
}