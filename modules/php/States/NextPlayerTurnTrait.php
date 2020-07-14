<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Draftpool;
use SWD\Player;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        // TODO Scientific victory (6 different scientific symbols)
        // TODO Military victory (conflict pawn position 9 or -9)
        if (Draftpool::countCardsInCurrentAge() > 0) {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN)) {
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN, 0);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'simpleNotif',
                    clienttranslate('${player_name} gets an extra turn.'),
                    [
                        'player_name' => Player::getActive()->name,
                    ]
                );
            }
            else {
                $this->activeNextPlayer();
            }

            $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
        }
        else {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN)) {
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN, 0);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'simpleNotif',
                    clienttranslate('${player_name} loses his extra turn because the age has ended.'),
                    [
                        'player_name' => Player::getActive()->name,
                    ]
                );
            }
            $this->gamestate->nextState( self::STATE_NEXT_AGE_NAME );
        }
    }
}