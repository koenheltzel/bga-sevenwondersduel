<?php

namespace SWD\States;

trait ChooseDivinityFromDeckTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseDivinityFromDeck() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateChooseDivinityFromDeck() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseDivinityFromDeck($wonderId) {
        $this->checkAction("actionChooseDivinityFromDeck");
    }

    public function shouldSkipChooseDivinityFromDeck() {
        return false;
    }

}