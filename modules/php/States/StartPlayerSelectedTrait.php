<?php

namespace SWD\States;

trait StartPlayerSelectedTrait {

    public function enterStateStartPlayerSelected() {

        $this->gamestate->changeActivePlayer($this->getGameStateValue(self::VALUE_AGE_START_PLAYER));

        $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
    }
}