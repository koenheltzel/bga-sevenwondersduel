<?php

namespace SWD\States;

trait RemoveInfluenceTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argRemoveInfluence() {
        return [];
    }

    public function enterStateRemoveInfluence() {
//        $this->giveExtraTime($this->getActivePlayerId());
    }

    // See SenateActionsTrait.php
//    public function actionRemoveInfluence
}