<?php

namespace SWD\States;

use SWD\Player;
use SWD\Senate;

trait MoveInfluenceTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argMoveInfluence() {
        $data = [];
        $data['senateSituation'] = Senate::getSituation();
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateMoveInfluence() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function shouldSkipMoveInfluence() {
        if (Player::getActive()->getCubes() == 12) {
            // Player still has all 12 cubes unused, so skip this state
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no Influence cubes to move'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );
            return true;
        }
        return false;
    }

    // See SenateActionsTrait.php
//    public function actionMoveInfluence
}