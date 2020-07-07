<?php


namespace SWD;


class MilitaryTrack
{

    public static function movePawn($player, $shields) {
        //TODO check if player has progress token military
        if(0) {
            $shields += 1;
        }

        if ($player->id <> \SevenWondersDuel::get()->getStartPlayerId()) {
            $shields *= -1;
        }

        $currentPosition = \SevenWondersDuel::get()->getConflictPawnPosition();
        $newPosition = max(-9, min(9, $currentPosition + $shields));
        \SevenWondersDuel::get()->setConflictPawnPosition($newPosition);
    }

    public static function getData() {
        return [
            'conflictPawn' => \SevenWondersDuel::get()->getConflictPawnPosition()
        ];
    }

}