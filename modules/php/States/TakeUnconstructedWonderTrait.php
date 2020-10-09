<?php

namespace SWD\States;

use SWD\Conspiracy;
use SWD\Player;
use SWD\Wonder;
use SWD\Wonders;

trait TakeUnconstructedWonderTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argTakeUnconstructedWonder() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateTakeUnconstructedWonder() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionTakeUnconstructedWonder($wonderId) {
        $this->checkAction("actionTakeUnconstructedWonder");

        $player = Player::me();
        $opponent = Player::opponent();

        if (!in_array($wonderId, $opponent->getWonderIds())) {
            throw new \BgaUserException( clienttranslate("The wonder you selected is not available.") );
        }
        $wonder = Wonder::get($wonderId);
        if ($wonder->isConstructed()) {
            throw new \BgaUserException( clienttranslate("The wonder you selected has already been constructed.") );
        }

        $cards = $player->getWonderDeckCards();
        $position = 1;
        for ($i = 0; $i < count($cards); $i++) {
            if ($position != $cards[$i]['location_arg']) {
                break;
            }
            $position++;
        }
        // This will likely bring $position to 5, but if a wonder was destroyed using Conspiracy Sabotage it could a lower position which is then filled.
        $this->wonderDeck->moveCard($wonderId, $player->id, $position);

        $this->notifyAllPlayers(
            'takeUnconstructedWonder',
            clienttranslate('${player_name} takes unconstructed Wonder “${wonderName}” from his opponent (Conspiracy “${conspiracyName}”)'),
            [
                'i18n' => ['wonderName', 'conspiracyName'],
                'wonderName' => Wonder::get($wonderId)->name,
                'conspiracyName' => Conspiracy::get(1)->name,
                'player_name' => $player->name,
                'playerId' => $player->id,
                'wonderId' => $wonderId,
                'position' => $position,
                'wondersSituation' => Wonders::getSituation(),
            ]
        );

        $this->stateStackNextState();
    }

    public function shouldSkipTakeUnconstructedWonder() {
        $opponent = Player::opponent();
        if (count($opponent->getWonders()->filterByConstructed(false)->array) == 0) {
            // Player has no unconstructed wonders, so skip this state
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no unconstructed Wonder to take (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['conspiracyName'],
                    'player_name' => $opponent->name,
                    'conspiracyName' => Conspiracy::get(1)->name,
                ]
            );
            return true;
        }
        return false;
    }

}