<?php


namespace SWD;


use SevenWondersDuel;

class MilitaryTrack
{

    public static function movePawn($player, $shields, $payment) {
        //TODO check if player has progress token military
        if(0) {
            $shields += 1;
        }

        if ($player->id <> SevenWondersDuel::get()->getStartPlayerId()) {
            $shields *= -1;
        }

        $currentPosition = SevenWondersDuel::get()->getConflictPawnPosition();
        $newPosition = max(-9, min(9, $currentPosition + $shields));
        SevenWondersDuel::get()->setConflictPawnPosition($newPosition);
    }
    
    public static function getMilitaryToken() {
        $position = SevenWondersDuel::get()->getConflictPawnPosition();
        $number = 0;
        if ($position >= -8 && $position <= -6) {
            $number = 1;
        }
        else if ($position >= -5 && $position <= -3) {
            $number = 2;
        }
        else if ($position >= 3 && $position <= 5) {
            $number = 3;
        }
        else if ($position >= 6 && $position <= 8) {
            $number = 4;
        }
        $value = SevenWondersDuel::get()->takeMilitaryToken($number);
        return [$number, $value];
    }

    public static function getData() {
        return [
            'conflictPawn' => SevenWondersDuel::get()->getConflictPawnPosition()
        ];
    }

}