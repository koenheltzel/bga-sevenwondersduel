<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Draftpool;
use SWD\Player;
use SWD\ProgressToken;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        // TODO Scientific victory (6 different scientific symbols)
        // TODO Military victory (conflict pawn position 9 or -9)
        if (Draftpool::countCardsInCurrentAge() > 0) {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL) || SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY)) {
                if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL)) {
                    $message = clienttranslate('${player_name} gets an extra turn.');
                }
                else {
                    $message = clienttranslate('${player_name} gets an extra turn (Progress token “${progressTokenName}”).');
                }
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL, 0);
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY, 0);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    $message,
                    [
                        'player_name' => Player::getActive()->name,
                        'progressTokenName' => ProgressToken::get(9)->name, // Theology
                    ]
                );
            }
            else {
                $this->activeNextPlayer();
            }

            $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
        }
        else {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL) || SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY)) {
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL, 0);
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY, 0);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
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