<?php

namespace SWD\States;

trait ChooseProgressTokenTrait {

    public function enterStateChooseProgressToken() {

    }

    public function actionChooseProgressToken($progressTokenId) {
        $this->checkAction("actionChooseProgressToken");

    }
}