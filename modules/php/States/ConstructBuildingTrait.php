<?php

namespace SWD\States;

trait ConstructBuildingTrait {

    public function enterStateConstructBuilding() {
        $this->gamestate->nextState( self::STATE_BUILDING_CONSTRUCTED_NAME );
    }
}