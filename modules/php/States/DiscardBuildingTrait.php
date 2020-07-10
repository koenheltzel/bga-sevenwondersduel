<?php

namespace SWD\States;

trait DiscardBuildingTrait {

    public function enterStateDiscardBuilding() {
        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME );
    }
}