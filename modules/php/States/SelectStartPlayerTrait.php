<?php

namespace SWD\States;

use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonders;

trait SelectStartPlayerTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argSelectStartPlayer() {
        $draftpool = Draftpool::get();
        return [
            'ageRoman' => ageRoman($draftpool['age']),
            'draftpool' => $draftpool,
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateSelectStartPlayer() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionSelectStartPlayer($playerId) {
        $this->checkAction("actionSelectStartPlayer");

        $this->setGameStateValue(self::VALUE_AGE_START_PLAYER, $playerId);

        $this->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} begins Age ${ageRoman}'),
            [
                'player_name' => Player::get($playerId)->name,
                'ageRoman' => ageRoman($this->getGameStateValue(self::VALUE_CURRENT_AGE)),
            ]
        );

        $this->gamestate->nextState( self::STATE_START_PLAYER_SELECTED_NAME );
    }
}