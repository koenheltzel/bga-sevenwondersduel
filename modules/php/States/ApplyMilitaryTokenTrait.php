<?php

namespace SWD\States;

use SWD\Divinity;
use SWD\MilitaryTrack;
use SWD\Player;

trait ApplyMilitaryTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argApplyMilitaryToken() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateApplyMilitaryToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionApplyMilitaryToken($token) {
        $this->checkAction("actionApplyMilitaryToken");

        if ($this->getMilitaryTokenValue($token) == 0) {
            throw new \BgaUserException( clienttranslate("This Military Token is not available, please refresh.") );
        }

        $neptune = Divinity::get(15);
        $payment = $neptune->neptuneApplyMilitaryToken(Player::getActive(), $token);

        $message = '';
        $startPlayer = Player::getStartPlayer();
        $sidePlayer = in_array($token, [1,2]) ? $startPlayer : $startPlayer->getOpponent();
        if (in_array($token, [1,4])) {
            $message = clienttranslate('${player_name} applied the effect of the large Military token on ${sidePlayerName}\'s side, then discarded it');
        }
        else {
            $message = clienttranslate('${player_name} applied the effect of the small Military token on ${sidePlayerName}\'s side, then discarded it');
        }

        $player = Player::getActive();
        $this->notifyAllPlayers(
            'applyMilitaryToken',
            $message,
            [
                'playerId' => $player->id,
                'player_name' => $player->name,
                'sidePlayerName' => $sidePlayer->name,
                'token' => $token,
                'payment' => $payment,
            ]
        );

        $this->prependStateStackAndContinue($payment->militarySenateActions);
    }

    public function shouldSkipApplyMilitaryToken() {
        if ($this->getMilitaryTokenValue(1) == 0 &&
            $this->getMilitaryTokenValue(2) == 0 &&
            $this->getMilitaryTokenValue(3) == 0 &&
            $this->getMilitaryTokenValue(4) == 0) {
            return true;
        }
        return false;
    }

}