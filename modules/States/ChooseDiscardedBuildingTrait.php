<?php

namespace SWD\States;

trait ChooseDiscardedBuildingTrait
{

    public function enterStateChooseDiscardedBuilding() {

    }

    public function actionChooseDiscardedBuilding($buildingId) {
        $this->checkAction("actionChooseDiscardedBuilding");

    }
}