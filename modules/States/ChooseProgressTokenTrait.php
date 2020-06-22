<?php

namespace SWD\States;

trait ChooseProgressTokenTrait {

    public function enterStateChooseProgressToken() {

    }

    public function actionChooseProgressToken($cardId) {
        $this->checkAction("actionChooseProgressToken");

    }
}