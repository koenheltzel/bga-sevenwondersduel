<?php

namespace SWD\States;

use SWD\Draftpool;
use SWD\Player;
use SWD\Senate;

trait PlaceInfluenceTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argPlaceInfluence() {
        $data = [];
        $data['wonderSelectionRound'] = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $data['draftpool'] = Draftpool::get();
        $data['senateSituation'] = Senate::getSituation();
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) == 0) {
            $data['draftpool'] = Draftpool::revealCards(1); // Curia Julia during Wonder Selection
        }
        return $data;
    }

    public function enterStatePlaceInfluence() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function shouldSkipPlaceInfluence() {
        if (Player::getActive()->getCubes() == 0) {
            // Player has no more cubes to place, so skip this state
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no more Influence cubes left to place'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );
            return true;
        }
        return false;
    }

    // See SenateActionsTrait.php
//    public function actionPlaceInfluence
}