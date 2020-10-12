<?php

namespace SWD\States;

use SWD\Building;
use SWD\Conspiracy;
use SWD\Player;

trait TakeBuildingTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argTakeBuilding() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateTakeBuilding() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionTakeBuilding($buildingId) {
        $this->checkAction("actionTakeBuilding");

        $player = Player::getActive();

        $building = Building::get($buildingId);
        if (!in_array($building->type, [Building::TYPE_BROWN, Building::TYPE_GREY])) {
            throw new \BgaUserException( clienttranslate("You are only allowed to choose a Brown or Grey building.") );
        }
        if (!$player->getOpponent()->hasBuilding($buildingId)) {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }

        $this->buildingDeck->insertCardOnExtremePosition($building->id, $player->id, true);

        $this->notifyAllPlayers(
            'takeBuilding',
            clienttranslate('${player_name} takes Building “${buildingName}” from ${opponent_name} and places it in his city'),
            [
                'i18n' => ['buildingName'],
                'player_name' => $player->name,
                'playerId' => $player->id,
                'opponent_name' => $player->getOpponent()->name,
                'buildingName' => $building->name,
                'buildingId' => $building->id,
                'buildingColumn' => $building->type,
            ]
        );

        $this->stateStackNextState();
    }

    public function shouldSkipTakeBuilding() {
        $opponent = Player::opponent();
        if (count($opponent->getBuildings()->filterByTypes([Building::TYPE_BROWN, Building::TYPE_GREY])->array) == 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no Brown or Grey Building to take (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['conspiracyName'],
                    'player_name' => $opponent->name,
                    'conspiracyName' => Conspiracy::get(13)->name,
                ]
            );
            return true;
        }
        return false;
    }

}