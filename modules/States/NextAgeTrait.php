<?php

namespace SWD\States;

use SevenWondersDuel;

trait NextAgeTrait {

    public function stNextAge() {
        $this->gamestate->nextState( SevenWondersDuel::STATE_PLAYER_TURN_NAME );
    }
}