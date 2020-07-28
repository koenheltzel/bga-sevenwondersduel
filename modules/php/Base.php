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
        return SevenWondersDuel::get()->_($string);
    }
}