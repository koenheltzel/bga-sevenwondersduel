<?php

namespace SWD\States;

use SWD\Building;
use SWD\Draftpool;
use SWD\Player;

trait ConstructLastRowBuildingTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argConstructLastRowBuilding() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateConstructLastRowBuilding() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function shouldSkipConstructLastRowBuilding() {
        $lastRowBuildings = Draftpool::getLastRowBuildings();
        $lastRowBuildingsCount = count(Draftpool::getLastRowBuildings()->array);
        $lastRowSenatorsCount = count($lastRowBuildings->filterByTypes([Building::TYPE_SENATOR])->array);
        if ($lastRowBuildingsCount - $lastRowSenatorsCount <= 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('There are no more cards available for ${player_name} to construct for free'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );
            return true;
        }
        return false;
    }

}