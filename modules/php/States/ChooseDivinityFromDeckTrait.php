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

    public function actionChooseDivinityFromDeck($divinityId, $divinityIdToTop = null) {
        $this->checkAction("actionChooseDivinityFromDeck");

        $player = Player::getActive();

        // Move all other divinities back to their decks.
        $selectionCards = $this->divinityDeck->getCardsInLocation('selection');
        $types = [];
        foreach($selectionCards as $card) {
            $divinity = Divinity::get($card['id']);
            if ($divinity->id != $divinityId) {
                $position = 0;
                if (count($selectionCards) > 2) {
                    $position = $divinity->id == $divinityIdToTop ? 1 : 0;
                }
                $this->divinityDeck->moveCard($divinity->id, "mythology{$divinity->type}", $position);
                $types[] = $divinity->type;
                if ($divinity->id == 1) {
                    // Enki: Return any remaining progress tokens in the active selection back to the box.
                    Divinities::resetEnkiProgressTokens();
                }
            }
        }

        $message = null;
        switch(count($selectionCards)) {
            case 2:
                $message = clienttranslate('${player_name} returns the other Divinity to the ${mythologyType} Mythology deck');
                break;
            case 3:
                $message = clienttranslate('${player_name} returns the other Divinities to the ${mythologyType} Mythology deck in the order of his choice');
                break;
        }

        if ($message) {
            // Text notification to all
            $this->notifyAllPlayers(
                'returnDivinities',
                $message,
                [
                    'i18n' => ['mythologyType'],
                    'mythologyType' => Divinity::getTypeName($divinity->type),
                    'player_name' => $player->name,
                    'types' => $types,
                    'selectedDivinityId' => $divinityId,
                ]
            );
        }

        // Activate selected Divinity
        $divinity = Divinity::get($divinityId);
        $payment = $divinity->activate($player, true); // Also handles transition to next state
    }

    public function shouldSkipChooseDivinityFromDeck() {
        return false;
    }

}