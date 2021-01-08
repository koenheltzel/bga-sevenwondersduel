<?php

namespace SWD\States;

trait PlaceMinervaTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argPlaceMinervaToken() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStatePlaceMinervaToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionPlaceMinervaToken($position) {
        $this->checkAction("actionPlaceMinervaToken");
    }

    public function shouldSkipPlaceMinervaToken() {
        return false;
    }

}