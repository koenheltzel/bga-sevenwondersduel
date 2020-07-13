<?php

namespace SWD\States;

use SWD\Draftpool;
use SWD\MilitaryTrack;
use SWD\Players;
use SWD\Wonders;

trait ChooseDiscardedBuildingTrait
{

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseDiscardedBuilding() {
        return [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
            'militaryTrack' => MilitaryTrack::getData(),
        ];
    }

    public function enterStateChooseDiscardedBuilding() {

    }

    public function actionChooseDiscardedBuilding($buildingId) {
        $this->checkAction("actionChooseDiscardedBuilding");

    }
}