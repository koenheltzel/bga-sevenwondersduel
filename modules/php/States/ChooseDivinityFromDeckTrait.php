<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Divinity;
use SWD\Player;

trait ChooseDivinityFromDeckTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseDivinityFromDeck() {
        $determineTypeCard = $this->divinityDeck->getCardOnTop('selection');
        $data = [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                Player::getActive()->id => [ // Using "active" keyword inside "_private", you select active player(s)
                    'divinities' => $this->divinityDeck->getCardsInLocation('selection') // will be send only to active player(s)
                ],
            ],
            'i18n' => ['mythologyType'], // Used in state description
            'mythologyType' =>  Divinity::getTypeName($determineTypeCard['type']), // Used in state description
            'divinitiesSituation' => Divinities::getSituation(), // Update the deck count
            'divinityTypes' => Divinities::typesInSelection(),
            'enkiInSelection' => (int)Divinities::enkiInSelection(),
        ];
        if (Divinities::enkiInSelection()) {
            $data['_private'][Player::getActive()->id]['enkiProgressTokenIds'] = array_keys($this->progressTokenDeck->getCardsInLocation('enki'));
        }
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateChooseDivinityFromDeck() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseDivinityFromDeck($divinityId, $divinityIdToTop) {
        $this->checkAction("actionChooseDivinityFromDeck");
    }

    public function shouldSkipChooseDivinityFromDeck() {
        return false;
    }

}