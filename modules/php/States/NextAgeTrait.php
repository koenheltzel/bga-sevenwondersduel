<?php

namespace SWD\States;

use SevenWondersDuel;

trait NextAgeTrait {

    public function enterStateNextAge() {
        $this->incGameStateValue(self::VALUE_CURRENT_AGE, 1);

        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}