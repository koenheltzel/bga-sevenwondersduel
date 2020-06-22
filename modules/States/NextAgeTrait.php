<?php

namespace SWD\States;

trait NextAgeTrait {

    public function enterStateNextAge() {
        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}