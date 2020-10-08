<?php


namespace SWD;


use SevenWondersDuelAgora;

class Base extends \APP_DbObject
{

    /**
     * Make _() available to our classes through self::() so the project checker doesn't complain.
     * @param $string
     * @return mixed
     */
    function _($string) {
        if (isDevEnvironment()) {
            return $string;
        }
        else {
            return SevenWondersDuelAgora::get()->_($string);
        }
    }
}