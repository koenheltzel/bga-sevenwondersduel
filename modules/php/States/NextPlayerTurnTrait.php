<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Draftpool;
use SWD\Player;
use SWD\ProgressToken;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        $conflictPawnPosition = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CONFLICT_PAWN_POSITION);
        if (Player::getActive()->getScientificSymbolCount() >= 6) {
            if (Player::me()->getScore() < Player::opponent()->getScore()) {
                Player::opponent()->setScore(-Player::opponent()->getScore()); // Make opponent's score negative to make sure the current player wins.
            }
            self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_SCIENTIFIC);

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} wins the game through Scientific Surpremacy (gathered 6 different scientific symbols)'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );

            $this->gamestate->nextState( self::STATE_GAME_END_NAME );
        }
        elseif ($conflictPawnPosition <= -9 || $conflictPawnPosition >= 9) {
            if (Player::me()->getScore() < Player::opponent()->getScore()) {
                Player::opponent()->setScore(-Player::opponent()->getScore()); // Make opponent's score negative to make sure the current player wins.
            }
            self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_MILITARY);

            SevenWondersDuel::get()->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} wins the game through Military Surpremacy (Conflict pawn reached the opponent\'s capital)'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );

            $this->gamestate->nextState( self::STATE_GAME_END_NAME );
        }
        elseif (Draftpool::countCardsInCurrentAge() > 0) {
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
        // End of the age
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

            if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) >= 3) {
                $this->gamestate->nextState( self::STATE_GAME_END_NAME );
            }
            else {
                $this->gamestate->nextState( self::STATE_NEXT_AGE_NAME );
            }
        }
    }
}