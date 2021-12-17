<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Player;
use SWD\ProgressToken;

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
        $data['_private'][Player::getActive()->id]['enkiProgressTokenIds'] = array_keys($this->progressTokenDeck->getCardsInLocation('enki'));
        $data['divinitiesSituation'] = Divinities::getSituation();
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateChooseEnkiProgressToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseEnkiProgressToken($progressTokenId) {
        $this->checkAction("actionChooseEnkiProgressToken");

        $progressToken = ProgressToken::get($progressTokenId);
        $payment = $progressToken->construct(Player::getActive());

        // Return any remaining progress tokens in the active selection back to the box.
        Divinities::resetEnkiProgressTokens();

        if ($payment->selectProgressToken) {
            $this->prependStateStackAndContinue([self::STATE_CHOOSE_PROGRESS_TOKEN_NAME]);
        }
        else {
            $this->stateStackNextState();
        }
    }

    public function shouldSkipChooseEnkiProgressToken() {
        return false;
    }

}