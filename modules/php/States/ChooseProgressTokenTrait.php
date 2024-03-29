<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Wonders;

trait ChooseProgressTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseProgressToken() {
        $data = [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
        if ($this->getGameStateValue(self::OPTION_PANTHEON)) {
            $data['divinitiesSituation'] = Divinities::getSituation();
        }
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        }
        return $data;
    }

    public function enterStateChooseProgressToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseProgressToken($progressTokenId) {
        $this->checkAction("actionChooseProgressToken");

        $progressToken = ProgressToken::get($progressTokenId);
        $payment = $progressToken->construct(Player::getActive());

        if ($payment->selectProgressToken) {
            $this->prependStateStackAndContinue([self::STATE_CHOOSE_PROGRESS_TOKEN_NAME]);
        }
        else {
            $this->stateStackNextState(self::STATE_NEXT_PLAYER_TURN_NAME);
        }
    }
}