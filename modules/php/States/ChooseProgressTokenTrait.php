<?php

namespace SWD\States;

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
        return [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateChooseProgressToken() {

    }

    public function actionChooseProgressToken($progressTokenId) {
        $this->checkAction("actionChooseProgressToken");

        $progressToken = ProgressToken::get($progressTokenId);
        $payment = $progressToken->construct(Player::me());

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }
}