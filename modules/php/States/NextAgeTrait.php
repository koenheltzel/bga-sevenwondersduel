<?php

namespace SWD\States;

use SevenWondersDuel;

trait NextAgeTrait {

    public function enterStateNextAge() {
        $this->setGameStateValue(self::VALUE_CURRENT_AGE, SevenWondersDuel::get()->getCurrentAge() + 1);

        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}