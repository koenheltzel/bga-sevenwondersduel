<?php

namespace SWD\States;

use SWD\Player;
use SWD\Wonder;

trait TriggerUnpreparedConspiracyTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argTriggerUnpreparedConspiracy() {
        return [];
    }

    public function enterStateTriggerUnpreparedConspiracy() {
//        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function shouldSkipTriggerUnpreparedConspiracy() {
        if (count(Player::getActive()->getConspiracies()->filterByPrepared(false)->array) == 0) {
            // Player has no unprepared conspiracies, so skip this state
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no unprepared Conspiracies to trigger (Wonder ${wonderName}“”)'),
                [
                    'i18n' => ['wonderName'],
                    'player_name' => Player::getActive()->name,
                    'wonderName' => Wonder::get(13)->name,
                ]
            );
            return true;
        }
        return false;
    }

    public function actionSkipTriggerUnpreparedConspiracy() {
        $this->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chooses not to prepare an unprepared Conspiracy (Wonder ${wonderName}“”)'),
            [
                'i18n' => ['wonderName'],
                'player_name' => Player::getActive()->name,
                'wonderName' => Wonder::get(13)->name,
            ]
        );

        $this->stateStackNextState();
    }

    // See SenateActionsTrait.php
//    public function actionTriggerUnpreparedConspiracy
}