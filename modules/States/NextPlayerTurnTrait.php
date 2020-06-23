<?php

namespace SWD\States;

use SWD\Draftpool;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        $this->activeNextPlayer();

        if (Draftpool::countCardsInCurrentAge() > 0) {
            $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
        }
        else {
            $this->gamestate->nextState( self::STATE_NEXT_AGE_NAME );
        }
    }
}