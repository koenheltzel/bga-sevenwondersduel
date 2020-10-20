<?php

namespace SWD\States;

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

        // Move the wonder to the box. The age card keeps the location wonderX, which is good because it doesn't interfere with Conspiracy 8 Treason.
        $this->wonderDeck->moveCard($wonderId, 'box');

        $this->notifyAllPlayers(
            'destroyConstructedWonder',
            clienttranslate('${player_name} returns ${opponent_name}\'s constructed Wonder “${wonderName}” to the box (Conspiracy “${conspiracyName}”)'),
            [
                'i18n' => ['wonderName', 'conspiracyName'],
                'wonderName' => Wonder::get($wonderId)->name,
                'conspiracyName' => Conspiracy::get(1)->name,
                'player_name' => $player->name,
                'opponent_name' => $opponent->name,
                'playerId' => $player->id,
                'wonderId' => $wonderId,
                'wondersSituation' => Wonders::getSituation(),
            ]
        );

        if ($wonder->victoryPoints > 0) {
            $opponent->increaseScore(-$wonder->victoryPoints, self::SCORE_WONDERS);

            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} loses ${points} victory point(s)'),
                [
                    'player_name' => $opponent->name,
                    'points' => $wonder->victoryPoints,
                ]
            );
        }

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