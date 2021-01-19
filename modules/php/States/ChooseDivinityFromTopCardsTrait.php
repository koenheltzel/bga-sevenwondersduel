<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Divinity;
use SWD\Player;

trait ChooseDivinityFromTopCardsTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseDivinityFromTopCards() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function preEnterStateChooseDivinityFromTopCards() {
        for ($type = 1; $type <=5; $type++) {
            $this->divinityDeck->pickCardForLocation("mythology{$type}", "selection", 0);
        }
        if (Divinities::enkiInSelection()) {
            Divinities::setEnkiProgressTokens();
        }
        return true;
    }

    public function enterStateChooseDivinityFromTopCards() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseDivinityFromTopCards($divinityId) {
        $this->checkAction("actionChooseDivinityFromTopCards");

        $player = Player::getActive();

        // Move all other divinities back to their decks.
        $selectionCards = $this->divinityDeck->getCardsInLocation('selection');
        $types = [];
        foreach($selectionCards as $card) {
            $divinity = Divinity::get($card['id']);
            if ($divinity->id != $divinityId) {
                $this->divinityDeck->insertCardOnExtremePosition($divinity->id, "mythology{$divinity->type}", true);
                $types[] = $divinity->type;
            }
        }

        // Text notification to all
        $this->notifyAllPlayers(
            'returnDivinities',
            clienttranslate('${player_name} returns the other Divinities on top of their respective decks'),
            [
                'player_name' => $player->name,
                'types' => $types,
            ]
        );

        // Activate selected Divinity
        $divinity = Divinity::get($divinityId);
        $payment = $divinity->activate($player); // Also handles transition to next state
    }

    public function shouldSkipChooseDivinityFromTopCards() {
        return false;
    }

}