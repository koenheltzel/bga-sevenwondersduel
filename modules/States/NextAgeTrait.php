<?php

namespace SWD\States;

trait NextAgeTrait {

    public function stNextAge() {
        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}