<?php

namespace SWD\States;

use SWD\Conspiracy;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Wonders;

trait ConspireTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argConspire() {
        return [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                'active' => [ // Using "active" keyword inside "_private", you select active player(s)
                    'conspiracies' => $this->conspiracyDeck->getCardsInLocation('conspire') // will be send only to active player(s)
                ]
            ],
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateConspire() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseConspiracy($conspiracyId) {
        $this->checkAction("actionChooseConspiracy");

        $conspiracy = Conspiracy::get($conspiracyId);
        $payment = $conspiracy->construct(Player::getActive());

        // Return any remaining progress tokens in the active selection back to the box.
        $this->progressTokenDeck->moveAllCardsInLocation('selection', 'box');

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);

    }
}