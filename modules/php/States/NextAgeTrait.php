<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Player;

trait NextAgeTrait
{

    public function enterStateNextAge() {
        $this->incGameStateValue(self::VALUE_CURRENT_AGE, 1);

        if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) == 1) {
            $this->gamestate->nextState(self::STATE_PLAYER_TURN_NAME);
        } else {
            $conflictPawnPosition = $this->getGameStateValue(self::VALUE_CONFLICT_PAWN_POSITION);
            // In case the pawn is in the middle (position 0), the player that did the last action may decide the start player, so no need to change the active player.
            if ($conflictPawnPosition != 0) {
                // In case the pawn is NOT in the middle, the player that has the pawn on his side becomes the player to decide the start player.
                $gameStartPlayerId = SevenWondersDuel::get()->getGameStartPlayerId();
                $decisionPlayerId = $conflictPawnPosition < 0 ? $gameStartPlayerId : Player::opponent($gameStartPlayerId)->id;
                $this->gamestate->changeActivePlayer($decisionPlayerId);
            }
            $this->gamestate->nextState(self::STATE_SELECT_START_PLAYER_NAME);
        }
    }
}