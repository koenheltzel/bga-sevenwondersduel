<?php

namespace SWD\States;

trait ChooseOpponentBuildingTrait {

    public function enterStateChooseOpponentBuilding() {

    }

    public function actionChooseOpponentBuilding($cardId) {
        $this->checkAction("actionChooseOpponentBuilding");

    }
}