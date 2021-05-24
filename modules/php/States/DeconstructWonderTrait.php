<?php

namespace SWD\States;

use SWD\Building;
use SWD\Divinity;
use SWD\Player;
use SWD\Wonder;
use SWD\Wonders;

trait DeconstructWonderTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argDeconstructWonder() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateDeconstructWonder() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionDeconstructWonder($wonderId) {
        $this->checkAction("actionDeconstructWonder");

        $activePlayer = Player::getActive();

        $wonder = Wonder::get($wonderId);
        $owner = $activePlayer->hasWonder($wonderId) ? $activePlayer : $activePlayer->getOpponent();
        // Move the age card to the discard (which is equal to "deconstructing" the wonder in the database).
        $ageCards = $this->buildingDeck->getCardsInLocation('wonder' . $wonderId);
        $ageCard = array_shift($ageCards);
        $building = Building::get($ageCard['id']);
        $this->buildingDeck->insertCardOnExtremePosition($building->id, 'discard', true);

        $this->notifyAllPlayers(
            'deconstructWonder',
            clienttranslate('${player_name} discards Age card “${buildingName}” previously used to construct ${ownerPlayerName} Wonder “${wonderName}” (Divinity “${divinityName}”)'),
            [
                'i18n' => ['wonderName', 'buildingName', 'divinityName'],
                'wonderName' => Wonder::get($wonderId)->name,
                'divinityName' => Divinity::get(10)->name,
                'player_name' => $activePlayer->name,
                'ownerPlayerName' => $owner->name,
                'playerId' => $activePlayer->id,
                'wonderId' => $wonderId,
                'buildingId' => $building->id,
                'buildingName' => $building->name,
                'wondersSituation' => Wonders::getSituation(),
            ]
        );

        $wonder->deconstructEffects($owner);

        $this->stateStackNextState();
    }

    public function shouldSkipDeconstructWonder() {
        $activePlayer = Player::getActive();
        if (count($activePlayer->getWonders()->filterByConstructed()->array) == 0 && count($activePlayer->getOpponent()->getWonders()->filterByConstructed()->array) == 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('There is no constructed Wonder to discard the Age card from (Divinity “${divinityName}”)'),
                [
                    'i18n' => ['divinityName'],
                    'divinityName' => Divinity::get(10)->name,
                ]
            );
            return true;
        }
        return false;
    }

}