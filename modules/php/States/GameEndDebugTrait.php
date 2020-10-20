<?php

namespace SWD\States;

use SWD\Player;
use SWD\Players;

trait GameEndDebugTrait
{

    function argGameEndDebug() {
        // This will update the player situation automatically through onEnteringState
        return [
            'playersSituation' => Players::getSituation(true)
        ];
    }

    function enterStateGameEndDebug() {
        // Set some statistics
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            foreach(Players::get() as $player) {
                $this->setStat(12 - $player->getCubes(), self::STAT_INFLUENCE_CUBES_USED, $player->id);
                $this->setStat($player->countChambersInControl(), self::STAT_CHAMBERS_IN_CONTROL, $player->id);
            }
        }

        $this->gamestate->nextState( self::STATE_GAME_END_NAME );
    }

}