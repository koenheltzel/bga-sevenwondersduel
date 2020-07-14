<?php

namespace SWD\States;

use SWD\Draftpool;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        // TODO Scientific victory (6 different scientific symbols)
        // TODO Military victory (conflict pawn position 9 or -9)
        if (Draftpool::countCardsInCurrentAge() > 0) {
            $this->activeNextPlayer(); // TODO check if player has extra turn.
            $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
        }
        else {
            $this->gamestate->nextState( self::STATE_NEXT_AGE_NAME );
        }
    }
}