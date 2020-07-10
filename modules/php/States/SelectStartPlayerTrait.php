<?php

namespace SWD\States;

trait SelectStartPlayerTrait {

    public function enterStateSelectStartPlayer() {

    }

    public function actionSelectStartPlayer($playerId) {
        $this->checkAction("actionSelectStartPlayer");

    }
}