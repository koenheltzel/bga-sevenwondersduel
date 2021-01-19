<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Player;

trait ChooseEnkiProgressTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseEnkiProgressToken() {
        $data = [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                Player::getActive()->id => [ // Using "active" keyword inside "_private", you select active player(s)
                    'divinityIds' => array_keys($this->divinityDeck->getCardsInLocation('selection')) // will be send only to active player(s)
                ],
            ]
        ];
        if (Divinities::enkiInSelection()) {
            $data['_private'][Player::getActive()->id]['enkiProgressTokenIds'] = array_keys($this->progressTokenDeck->getCardsInLocation('enki'));
        }
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateChooseEnkiProgressToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseEnkiProgressToken($progressTokenId) {
        $this->checkAction("actionChooseEnkiProgressToken");
    }

    public function shouldSkipChooseEnkiProgressToken() {
        return false;
    }

}