<?php


namespace SWD;


use SevenWondersDuel;

class Players
{

    public static function getSituation() {
        return [
            Player::me()->id => [
                'score' => Player::me()->getScore(),
                'coins' => Player::me()->getCoins(),
            ],
            Player::opponent()->id => [
                'score' => Player::opponent()->getScore(),
                'coins' => Player::opponent()->getCoins(),
            ],
        ];
    }

}