<?php

namespace SWD\States;

trait BuildingConstructedTrait
{

    public function enterStateBuildingConstructed() {
        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME );
    }
}