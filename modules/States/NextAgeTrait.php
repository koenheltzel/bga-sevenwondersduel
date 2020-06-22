<?php

namespace SWD\States;

use SWD\Draftpool;

trait NextAgeTrait {

    public function enterStateNextAge() {
        $playerId = self::getCurrentPlayerId();

        $this->notifyAllPlayers(
            'nextAge',
            '',
            [
                'draftpool' => Draftpool::get($playerId),
            ]
        );

        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}