<?php

namespace SWD\States;

use SWD\Player;
use SWD\Senate;

trait RemoveInfluenceTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argRemoveInfluence() {
        $data = [];
        $data['senateSituation'] = Senate::getSituation();
        return $data;
    }

    public function enterStateRemoveInfluence() {
//        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function shouldSkipRemoveInfluence() {
        if (Player::opponent()->getCubes() == 12) {
            // Opponent still has all 12 cubes unused, so skip this state
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no Influence cubes to remove'),
                [
                    'player_name' => Player::opponent()->name,
                ]
            );
            return true;
        }
        return false;
    }

    // See SenateActionsTrait.php
//    public function actionRemoveInfluence
}