<?php

namespace SWD\States;

use SWD\Player;

trait LockProgressTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argLockProgressToken() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateLockProgressToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

//    public function actionLockProgressToken($progressTokenId) {
//        $this->checkAction("actionLockProgressToken");
//
//        $this->notifyAllPlayers(
//            'message',
//            clienttranslate('${player_name} chose to Place Influence'),
//            [
//                'player_name' => Player::getActive()->name
//            ]
//        );
//
//        $this->setStateStack([self::STATE_PLACE_INFLUENCE_NAME, self::STATE_NEXT_PLAYER_TURN_NAME]);
//        $this->stateStackNextState();
//    }

//    public function shouldSkipLockProgressToken() {
//        return false;
//    }

}