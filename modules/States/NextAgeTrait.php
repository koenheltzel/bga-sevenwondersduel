<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Draftpool;
use SWD\Wonders;

trait NextAgeTrait {

    public function enterStateNextAge() {
        $this->setGameStateValue(self::VALUE_CURRENT_AGE, SevenWondersDuel::get()->getCurrentAge() + 1);

        $this->notifyAllPlayers(
            'nextAge',
            '',
            [
                'draftpool' => Draftpool::get(),
                'wondersSituation' => Wonders::getSituation(),
            ]
        );

        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}