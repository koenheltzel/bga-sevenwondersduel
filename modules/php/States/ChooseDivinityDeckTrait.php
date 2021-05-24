<?php

namespace SWD\States;

use SWD\Divinities;

trait ChooseDivinityDeckTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseDivinityDeck() {
        $data = [
            'deckCounts' => Divinities::getDeckCounts()
        ];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateChooseDivinityDeck() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseDivinityDeck($type) {
        $this->checkAction("actionChooseDivinityDeck");

        // Preserve card order
        $deckCount = count(Divinities::getDeckCardsSorted("mythology{$type}"));
        for ($i = 0; $i < $deckCount; $i++) {
            $this->divinityDeck->pickCardsForLocation(1, "mythology{$type}", 'selection', $i);
        }
        if (Divinities::enkiInSelection()) {
            Divinities::setEnkiProgressTokens();
        }

        $this->prependStateStackAndContinue([self::STATE_CHOOSE_DIVINITY_FROM_DECK_NAME]);
    }

    public function shouldSkipChooseDivinityDeck() {
        return false;
    }

}