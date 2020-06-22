<?php

namespace SWD\States;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        $this->activeNextPlayer();
        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}