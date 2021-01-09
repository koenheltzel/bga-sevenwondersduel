<?php

namespace SWD\States;

use SWD\MilitaryTrack;
use SWD\Player;

trait DiscardMilitaryTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argDiscardMilitaryToken() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateDiscardMilitaryToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionDiscardMilitaryToken($token) {
        $this->checkAction("actionDiscardMilitaryToken");

        if ($this->getMilitaryTokenValue($token) == 0) {
            throw new \BgaUserException( clienttranslate("This Military Token is not available, please refresh.") );
        }

        $this->takeMilitaryToken($token);

        $message = '';
        $startPlayer = Player::getStartPlayer();
        $sidePlayer = in_array($token, [1,2]) ? $startPlayer : $startPlayer->getOpponent();
        if (in_array($token, [1,4])) {
            $message = clienttranslate('${player_name} discarded the large Military token on ${sidePlayerName}\'s side without applying its effect');
        }
        else {
            $message = clienttranslate('${player_name} discarded the small Military token on ${sidePlayerName}\'s side without applying its effect');
        }
        $this->notifyAllPlayers(
            'discardMilitaryToken',
            $message,
            [
                'player_name' => Player::getActive()->name,
                'sidePlayerName' => $sidePlayer->name,
                'token' => $token,
            ]
        );

        $this->stateStackNextState();
    }

    public function shouldSkipDiscardMilitaryToken() {
        if ($this->getMilitaryTokenValue(1) == 0 &&
            $this->getMilitaryTokenValue(2) == 0 &&
            $this->getMilitaryTokenValue(3) == 0 &&
            $this->getMilitaryTokenValue(4) == 0) {
            return true;
        }
        return false;
    }

}