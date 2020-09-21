<?php

namespace SWD\States;

use SWD\Conspiracies;
use SWD\Draftpool;
use SWD\Material;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Wonders;

trait ChooseConspiratorActionTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseConspiratorAction() {
        return [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateChooseConspiratorAction() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseConspiratorActionPlaceInfluence($chamber) {
        $this->checkAction("actionChooseConspiratorPlaceInfluence");

        $this->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose to Place Influence'),
            [
                'player_name' => Player::getActive()->name
            ]
        );

//        $progressToken = ProgressToken::get($progressTokenId);
//        $payment = $progressToken->construct(Player::getActive());

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }

    public function actionConspire() {
        $this->checkAction("actionConspire");

        Material::get()->conspiracies->conspire();

        $this->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose to Conspire'),
            [
                'player_name' => Player::getActive()->name
            ]
        );

        $this->setGameStateInitialValue(self::VALUE_CONSPIRE_RETURN_STATE, self::STATE_NEXT_PLAYER_TURN_ID);
        $this->gamestate->nextState( self::STATE_CONSPIRE_NAME);
    }
}