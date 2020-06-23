<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Draftpool;

trait NextAgeTrait {

    public function enterStateNextAge() {
        $this->setGameStateValue(self::VALUE_CURRENT_AGE, SevenWondersDuel::get()->getCurrentAge() + 1);

        $this->notifyAllPlayers(
            'nextAge',
            '',
            [
                'draftpool' => Draftpool::get(),
            ]
        );

        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}