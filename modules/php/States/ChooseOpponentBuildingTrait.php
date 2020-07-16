<?php

namespace SWD\States;

use SWD\Building;
use SWD\Draftpool;
use SWD\Players;
use SWD\Wonders;

trait ChooseOpponentBuildingTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseOpponentBuilding() {
        return [
            'buildingType' => $this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER) == 9 ? Building::TYPE_BROWN : Building::TYPE_GREY,
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateChooseOpponentBuilding() {

    }

    public function actionChooseOpponentBuilding($buildingId) {
        $this->checkAction("actionChooseOpponentBuilding");

    }
}