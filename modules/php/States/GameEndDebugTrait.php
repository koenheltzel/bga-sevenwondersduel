<?php

namespace SWD\States;

use SWD\Players;

trait GameEndDebugTrait
{

    function argGameEndDebug() {
        // This will update the player situation automatically through onEnteringState
        return [
            'playersSituation' => Players::getSituation(true)
        ];
    }

    function enterStateGameEndDebug() {
        $this->gamestate->nextState( self::STATE_GAME_END_NAME );
    }

}