<?php

namespace SWD\States;

trait MoveInfluenceTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argMoveInfluence() {
        return [];
    }

    public function enterStateMoveInfluence() {
//        $this->giveExtraTime($this->getActivePlayerId());
    }

    // See SenateActionsTrait.php
//    public function actionMoveInfluence
}