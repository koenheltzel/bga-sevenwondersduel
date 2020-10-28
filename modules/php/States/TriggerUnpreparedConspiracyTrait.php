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
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateTriggerUnpreparedConspiracy() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function shouldSkipTriggerUnpreparedConspiracy() {
        $player = Player::getActive();
        $unpreparedConspiracies = $player->getConspiracies()->filterByPrepared(false);
        if (count($unpreparedConspiracies->array) == 0) {
            // Player has no unprepared conspiracies, so skip this state
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has no unprepared Conspiracies to trigger (Wonder “${wonderName}”)'),
                [
                    'i18n' => ['wonderName'],
                    'player_name' => Player::getActive()->name,
                    'wonderName' => Wonder::get(13)->name,
                ]
            );
            return true;
        }
        else {
            $usefulConspiracies = 0;
            foreach($unpreparedConspiracies->array as $conspiracy) {
                $usefulConspiracies += (int)$conspiracy->isUsefulToTrigger($player);
            }
            if ($usefulConspiracies == 0) {
                // Player has no useful unprepared conspiracies, so skip this state
                $this->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has no unprepared Conspiracies to trigger that have relevant actions (Wonder “${wonderName}”)'),
                    [
                        'i18n' => ['wonderName'],
                        'player_name' => Player::getActive()->name,
                        'wonderName' => Wonder::get(13)->name,
                    ]
                );
            }
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