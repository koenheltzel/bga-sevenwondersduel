<?php

namespace SWD\States;

trait ChooseDiscardedBuildingTrait
{

    public function enterStateChooseDiscardedBuilding() {

    }

    public function actionChooseDiscardedBuilding($cardId) {
        $this->checkAction("actionChooseDiscardedBuilding");

    }
}