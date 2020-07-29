<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Draftpool;
use SWD\MilitaryTrack;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        $conflictPawnPosition = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CONFLICT_PAWN_POSITION);
        if (Player::getActive()->getScientificSymbolCount() >= 6) {
            Player::me()->setWinner();
            self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_SCIENTIFIC);

            SevenWondersDuel::get()->notifyAllPlayers(
                'nextPlayerTurnScientificSupremacy',
                clienttranslate('${player_name} wins the game through Scientific Supremacy (gathered 6 different scientific symbols)'),
                [
                    'player_name' => Player::getActive()->name,
                    'playerId' => Player::getActive()->id,
                    'playersSituation' => Players::getSituation(true),
                ]
            );

            $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
        }
        elseif ($conflictPawnPosition <= -9 || $conflictPawnPosition >= 9) {
            Player::me()->setWinner();
            self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_MILITARY);

            SevenWondersDuel::get()->notifyAllPlayers(
                'nextPlayerTurnMilitarySupremacy',
                clienttranslate('${player_name} wins the game through Military Supremacy (Conflict pawn reached the opponent\'s capital)'),
                [
                    'player_name' => Player::getActive()->name,
                    'playerId' => Player::getActive()->id,
                    'playersSituation' => Players::getSituation(true),
                ]
            );

            $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
        }
        elseif (Draftpool::countCardsInCurrentAge() > 0) {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL) || SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY)) {
                if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL)) {
                    $message = clienttranslate('${player_name} gets an extra turn');
                }
                else {
                    $message = clienttranslate('${player_name} gets an extra turn (Progress token “${progressTokenName}”)');
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
                    clienttranslate('${player_name} loses his extra turn because the age has ended'),
                    [
                        'player_name' => Player::getActive()->name,
                    ]
                );
            }

            if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) >= 3) {
                foreach(Players::get() as $player) {
                    // Wonders, Blue, green and yellow buildings' victory points have already been counted during the game.
                    // Guilds points
                    /** @var Building $building */
                    foreach($player->getBuildings()->filterByTypes([Building::TYPE_PURPLE]) as $building) {
                        if ($building->guildRewardWonders) {
                            $constructedWonders = count($player->getWonders()->filterByConstructed()->array);
                            $constructedWondersOpponent = count($player->getOpponent()->getWonders()->filterByConstructed()->array);
                            $maxConstructedWonders = max($constructedWonders, $constructedWondersOpponent);
                            $mostPlayer = $constructedWonders >= $constructedWondersOpponent ? $player : $player->getOpponent();
                            $points = $maxConstructedWonders * 2;
                            $player->increaseScore($points, $building->type);
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} scores ${points} victory points (Guild “${guildName}”), 2 for each constructed Wonder in the city which has the most of them (${mostPlayerName}\'s)'),
                                [
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'guildName' => $building->name,
                                    'mostPlayerName' => $mostPlayer->name,
                                ]
                            );
                        }

                        if ($building->guildRewardCoinTriplets) {
                            $coinTriplets = floor($player->getCoins() / 3);
                            $coinTripletsOpponent = floor($player->getOpponent()->getCoins() / 3);
                            $maxCoinTriplets = max($coinTriplets, $coinTripletsOpponent);
                            $mostPlayer = $coinTriplets >= $coinTripletsOpponent ? $player : $player->getOpponent();
                            $points = $maxCoinTriplets;
                            $player->increaseScore($points, $building->type);
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} scores ${points} victory points (Guild “${guildName}”), 1 for each set of 3 coins in the richest city (${mostPlayerName}\'s)'),
                                [
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'guildName' => $building->name,
                                    'mostPlayerName' => $mostPlayer->name,
                                ]
                            );
                        }

                        if ($building->guildRewardBuildingTypes) {
                            $buildingsOfType = count($player->getBuildings()->filterByTypes($building->guildRewardBuildingTypes)->array);
                            $buildingsOfTypeOpponent = count($player->getOpponent()->getBuildings()->filterByTypes($building->guildRewardBuildingTypes)->array);
                            $maxBuildingsOfType = max($buildingsOfType, $buildingsOfTypeOpponent);
                            $mostPlayer = $buildingsOfType >= $buildingsOfTypeOpponent ? $player : $player->getOpponent();
                            $points = $maxBuildingsOfType;
                            $player->increaseScore($points, $building->type);
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} scores ${points} victory points (Guild “${guildName}”), 1 for each ${buildingType} building in the city which has the most of them (${mostPlayerName}\'s)'),
                                [
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'buildingType' => count($building->guildRewardBuildingTypes) > 1 ? clienttranslate('Brown and Grey') : $building->guildRewardBuildingTypes[0],
                                    'guildName' => $building->name,
                                    'mostPlayerName' => $mostPlayer->name,
                                ]
                            );
                        }
                    }

                    // Military victory points
                    $points = MilitaryTrack::getVictoryPoints($player);
                    if ($points > 0) {
                        $player->increaseScore($points, self::SCORE_MILITARY);
                        SevenWondersDuel::get()->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} scores ${points} victory points (Conflict pawn position)'),
                            [
                                'player_name' => $player->name,
                                'points' => $points,
                            ]
                        );
                    }

                    // Progress Token Mathematics (3 points for each Progress token)
                    if ($player->hasProgressToken(6)) {
                        $points = 3 * count($player->getProgressTokens()->array);
                        if ($points > 0) {
                            $player->increaseScore($points, self::SCORE_PROGRESSTOKENS);
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} scores ${points} victory points (Progress token “${progressTokenName}”)'),
                                [
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'progressTokenName' => ProgressToken::get(6)->name,
                                ]
                            );
                        }
                    }

                    // Coins to points 3:1
                    $points = floor($player->getCoins() / 3);
                    if ($points > 0) {
                        $player->increaseScore($points, self::SCORE_COINS);
                        SevenWondersDuel::get()->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} scores ${points} victory points (1 for each set of 3 coins)'),
                            [
                                'player_name' => $player->name,
                                'points' => $points,
                            ]
                        );
                    }
                }

                SevenWondersDuel::get()->notifyAllPlayers(
                    'nextPlayerTurnEndGameScoring',
                    "",
                    [
                        'playersSituation' => Players::getSituation(true),
                    ]
                );

                $winner = $this->determineWinner();

                $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
            }
            else {
                $this->gamestate->nextState( self::STATE_NEXT_AGE_NAME );
            }
        }
    }

    /**
     * @return Player|null
     */
    public function determineWinner() {
        return $this->getWinner(true);
    }

    /**
     * @return Player|null
     */
    public function getWinner($determine=false) {
        $meScore = Player::me()->getScore();
        $opponentScore = Player::opponent()->getScore();
        if ($meScore != $opponentScore) {
            $winner = $meScore > $opponentScore ? Player::me() : Player::opponent();
            if($determine) {
                $winner->setWinner();
                $this->setGameStateValue(self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_NORMAL);

                $this->notifyAllPlayers(
                    'message',
                    '${player_name} wins the game with ${winnerPoints} victory points to ${loserPoints} (Civilian Victory)',
                    [
                        'player_name' => $winner->name,
                        'winnerPoints' => $winner->getScore(),
                        'loserPoints' => $winner->getOpponent()->getScore(),
                    ]
                );
            }
            return $winner;
        }
        else {
            $meBluePoints = Player::me()->getValue('player_score_blue');
            $opponentBluePoints = Player::opponent()->getValue('player_score_blue');
            if ($meBluePoints != $opponentBluePoints) {
                $winner = $meBluePoints > $opponentBluePoints ? Player::me() : Player::opponent();
                if ($determine) {
                    $winner->setWinner();
                    $this->setGameStateValue(self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_NORMAL_AUX);

                    $this->notifyAllPlayers(
                        'message',
                        '${player_name} wins the game with a tied score but a majority of blue buildings, ${winnerBuildings} to ${loserBuildings} (Civilian Victory)',
                        [
                            'player_name' => $winner->name,
                            'winnerBuildings' => $winner->getValue('player_score_blue'),
                            'loserBuildings' => $winner->getOpponent()->getValue('player_score_blue'),
                        ]
                    );
                }
                return $winner;
            }
            else {
                $this->setGameStateValue(self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_DRAW);

                $this->notifyAllPlayers(
                    'message',
                    'Game ends in a draw (victory points and blue buildings count are both tied)',
                    []
                );
                return null;
            }
        }
    }
}