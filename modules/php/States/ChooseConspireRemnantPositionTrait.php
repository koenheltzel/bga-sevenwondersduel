<?php

namespace SWD\States;

use SWD\Conspiracies;
use SWD\Draftpool;
use SWD\Material;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Wonders;

trait ChooseConspireRemnantPositionTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseConspireRemnantPosition() {
        return [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateChooseConspireRemnantPosition() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseConspireRemnantPosition($top) {
        $this->checkAction("actionChooseConspiratorPlaceInfluence");

        Material::get()->conspiracies->conspireRemnantPosition($top);

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }
}