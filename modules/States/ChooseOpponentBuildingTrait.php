<?php

namespace SWD\States;

trait ChooseOpponentBuildingTrait {

    public function enterStateChooseOpponentBuilding() {

    }

    public function actionChooseOpponentBuilding($buildingId) {
        $this->checkAction("actionChooseOpponentBuilding");

    }
}