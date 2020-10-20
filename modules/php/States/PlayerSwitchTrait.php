<?php

namespace SWD\States;

trait PlayerSwitchTrait {

    public function enterStatePlayerSwitch() {

        $this->activeNextPlayer();

        $this->stateStackNextState();
    }
}