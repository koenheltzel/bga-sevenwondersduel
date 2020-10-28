<?php

namespace SWD\States;

use SWD\Building;
use SWD\Conspiracy;
use SWD\Player;
use SWD\Wonder;
use SWD\Wonders;

trait DestroyConstructedWonderTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argDestroyConstructedWonder() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateDestroyConstructedWonder() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionDestroyConstructedWonder($wonderId) {
        $this->checkAction("actionDestroyConstructedWonder");

        $player = Player::me();
        $opponent = Player::opponent();

        if (!in_array($wonderId, $opponent->getWonderIds())) {
            throw new \BgaUserException( clienttranslate("The wonder you selected is not available.") );
        }
        $wonder = Wonder::get($wonderId);
        if (!$wonder->isConstructed()) {
            throw new \BgaUserException( clienttranslate("The wonder you selected is not constructed.") );
        }

        // Move the wonder to the box.
        $this->wonderDeck->moveCard($wonderId, 'box');
        // Move the age card to the discard.
        $ageCards = $this->buildingDeck->getCardsInLocation('wonder' . $wonderId);
        $ageCard = array_shift($ageCards);
        $building = Building::get($ageCard['id']);
        $this->buildingDeck->insertCardOnExtremePosition($building->id, 'discard', true);

        $this->notifyAllPlayers(
            'destroyConstructedWonder',
            clienttranslate('${player_name} returns ${opponent_name}\'s constructed Wonder “${wonderName}” to the box and Age card “${buildingName}” to the discard pile (Conspiracy “${conspiracyName}”)'),
            [
                'i18n' => ['wonderName', 'buildingName', 'conspiracyName'],
                'wonderName' => Wonder::get($wonderId)->name,
                'conspiracyName' => Conspiracy::get(1)->name,
                'player_name' => $player->name,
                'opponent_name' => $opponent->name,
                'playerId' => $player->id,
                'wonderId' => $wonderId,
                'buildingId' => $building->id,
                'buildingName' => $building->name,
                'wondersSituation' => Wonders::getSituation(),
            ]
        );

        $wonder->deconstructEffects($opponent);

        $this->stateStackNextState();
    }

    public function shouldSkipDestroyConstructedWonder() {
        $opponent = Player::opponent();
        if (count($opponent->getWonders()->filterByConstructed()->array) == 0) {
            // Player has no unconstructed wonders, so skip this state
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no constructed Wonder to return to the box (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['conspiracyName'],
                    'player_name' => $opponent->name,
                    'conspiracyName' => Conspiracy::get(16)->name,
                ]
            );
            return true;
        }
        return false;
    }

}