<?php

namespace SWD\States;

use SWD\Building;
use SWD\Buildings;
use SWD\Player;

trait ConstructBuildingFromBoxTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argConstructBuildingFromBox() {
        $data = [];
        $data['_private'][Player::getActive()->id]['buildingsFromBox'] = Buildings::getAgeCardsFromBoxByAge((int)$this->getGameStateValue(self::VALUE_CURRENT_AGE));
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateConstructBuildingFromBox() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionConstructBuildingFromBox($buildingId) {
        $this->checkAction("actionConstructBuildingFromBox");

        $building = Building::get($buildingId);

        $cardInfo = $this->buildingDeck->getCard($buildingId);
        if ($cardInfo['location'] != 'box') {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }
        if ($building->age > 3) {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }

        $payment = $building->construct(Player::getActive(), null, false, true);


        $this->transitionAfterConstructBuilding($building, $payment);
    }

    public function shouldSkipConstructBuildingFromBox() {
        // This action is always possible.
        return false;
    }

}