<?php

namespace SWD\States;

use SWD\Divinity;
use SWD\Player;

trait ConstructWonderWithDiscardedBuildingTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argConstructWonderWithDiscardedBuilding() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateConstructWonderWithDiscardedBuilding() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function shouldSkipConstructWonderWithDiscardedBuilding() {
        if (count($this->buildingDeck->getCardsInLocation('discard')) == 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} can\'t choose a discarded card to construct a Wonder with (Divinity “${divinityName}”)'),
                [
                    'player_name' => Player::getActive()->name,
                    'divinityName' => Divinity::get(11)->name,
                ]
            );
            return true;
        }
        $activePlayer = Player::opponent();
        if (count($activePlayer->getWonders()->filterByConstructed(false)->array) == 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no unconstructed Wonder to construct (Divinity “${divinityName}”)'),
                [
                    'i18n' => ['divinityName'],
                    'divinityName' => Divinity::get(11)->name,
                ]
            );
            return true;
        }
        return false;
    }

}