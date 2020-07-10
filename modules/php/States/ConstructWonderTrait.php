<?php

namespace SWD\States;

trait ConstructWonderTrait {

    public function enterStateConstructWonder() {
        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME );
    }
}