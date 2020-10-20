<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Decree;
use SWD\Draftpool;
use SWD\MilitaryTrack;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Senate;

trait NextPlayerTurnTrait {

    public function enterStateNextPlayerTurn() {
        SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_MAY_TRIGGER_CONSPIRACY, 1);

        if ($this->checkImmediateVictory()) {
            $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
        }
        elseif (Draftpool::countCardsInCurrentAge() > 0) {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL)
                || SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY)
                || SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_DECREE)
            ) {
                $args = null;
                $message = null;
                if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL)) {
                    $message = clienttranslate('${player_name} gets an extra turn');
                    $args = [
                        'player_name' => Player::getActive()->name
                    ];
                }
                elseif (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY)) {
                    $message = clienttranslate('${player_name} gets an extra turn (Progress token “${progressTokenName}”)');
                    $args = [
                        'i18n' => ['progressTokenName'],
                        'player_name' => Player::getActive()->name,
                        'progressTokenName' => ProgressToken::get(9)->name, // Theology
                    ];
                }
                elseif (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_DECREE)) {
                    // This notification is done after constructing the Senator, because some actions remain after that. But we already want to get the information out there that an extra turn is happening.
                }

                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL, 0);
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY, 0);
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_DECREE, 0);

                if ($message) {
                    SevenWondersDuel::get()->notifyAllPlayers(
                        'message',
                        $message,
                        $args
                    );
                }

                $this->incStat(1, self::STAT_EXTRA_TURNS, Player::getActive()->id);
            }
            else {
                $this->activeNextPlayer();
            }

            $this->gamestate->nextState( self::STATE_PLAYER_TURN_NAME );
        }
        // End of the age
        else {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL)
                || SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY)
                || SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_DECREE)
            ) {
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_NORMAL, 0);
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_THEOLOGY, 0);
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_EXTRA_TURN_THROUGH_DECREE, 0);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} loses their extra turn because the age has ended'),
                    [
                        'player_name' => Player::getActive()->name,
                    ]
                );
            }

            if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) >= 3) {
                // Let's queue the nextPlayerTurnEndGameScoring notification because we want this to arrive first.
                // However, the playersSituation we pass by reference, empty for now. This will be filled after the foreach and determining the winner.
                $playerSituation = [];
                SevenWondersDuel::get()->notifyAllPlayers(
                    'nextPlayerTurnEndGameScoring',
                    "",
                    [
                        'playersSituation' => Players::getSituation(),
                        'endGamePlayersSituation' => &$playerSituation,
                    ]
                );

                // Wonders, Blue, green and yellow buildings' victory points have already been counted during the game.

                // Guilds points
                foreach(Players::get() as $player) {
                    /** @var Building $building */
                    foreach($player->getBuildings()->filterByTypes([Building::TYPE_PURPLE]) as $building) {
                        if ($building->guildRewardWonders) {
                            $constructedWonders = count($player->getWonders()->filterByConstructed()->array);
                            $constructedWondersOpponent = count($player->getOpponent()->getWonders()->filterByConstructed()->array);
                            $maxConstructedWonders = max($constructedWonders, $constructedWondersOpponent);
                            $mostPlayer = $constructedWonders >= $constructedWondersOpponent ? $player : $player->getOpponent();
                            $points = $maxConstructedWonders * 2;
                            $player->increaseScore($points, $building->getScoreCategory());
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'endGameCategoryUpdate',
                                clienttranslate('${player_name} scores ${points} victory points (Guild “${guildName}”), 2 for each constructed Wonder in the city which has the most of them (${mostPlayerName}\'s)'),
                                [
                                    'i18n' => ['guildName'],
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'guildName' => $building->name,
                                    'mostPlayerName' => $mostPlayer->name,
                                    'playerIds' => [$player->id],
                                    'category' => 'purple',
                                    'highlightId' => 'player_building_container_' . $building->id,
                                ]
                            );
                        }

                        if ($building->guildRewardCoinTriplets) {
                            $coinTriplets = floor($player->getCoins() / 3);
                            $coinTripletsOpponent = floor($player->getOpponent()->getCoins() / 3);
                            $maxCoinTriplets = max($coinTriplets, $coinTripletsOpponent);
                            $mostPlayer = $coinTriplets >= $coinTripletsOpponent ? $player : $player->getOpponent();
                            $points = $maxCoinTriplets;
                            $player->increaseScore($points, $building->getScoreCategory());
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'endGameCategoryUpdate',
                                clienttranslate('${player_name} scores ${points} victory points (Guild “${guildName}”), 1 for each set of 3 coins in the richest city (${mostPlayerName}\'s)'),
                                [
                                    'i18n' => ['guildName'],
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'guildName' => $building->name,
                                    'mostPlayerName' => $mostPlayer->name,
                                    'playerIds' => [$player->id],
                                    'category' => 'purple',
                                    'highlightId' => 'player_building_container_' . $building->id,
                                ]
                            );
                        }

                        if ($building->guildRewardBuildingTypes) {
                            $buildingsOfType = count($player->getBuildings()->filterByTypes($building->guildRewardBuildingTypes)->array);
                            $buildingsOfTypeOpponent = count($player->getOpponent()->getBuildings()->filterByTypes($building->guildRewardBuildingTypes)->array);
                            $maxBuildingsOfType = max($buildingsOfType, $buildingsOfTypeOpponent);
                            $mostPlayer = $buildingsOfType >= $buildingsOfTypeOpponent ? $player : $player->getOpponent();
                            $points = $maxBuildingsOfType;
                            $player->increaseScore($points, $building->getScoreCategory());
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'endGameCategoryUpdate',
                                clienttranslate('${player_name} scores ${points} victory points (Guild “${guildName}”), 1 for each ${buildingType} building in the city which has the most of them (${mostPlayerName}\'s)'),
                                [
                                    'i18n' => ['guildName', 'buildingType'],
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'buildingType' => count($building->guildRewardBuildingTypes) > 1 ? clienttranslate('Brown and Grey') : $building->guildRewardBuildingTypes[0],
                                    'guildName' => $building->name,
                                    'mostPlayerName' => $mostPlayer->name,
                                    'playerIds' => [$player->id],
                                    'category' => 'purple',
                                    'highlightId' => 'player_building_container_' . $building->id,
                                ]
                            );
                        }
                    }
                }

                foreach(Players::get() as $player) {
                    // Progress Token Mathematics (3 points for each Progress token)
                    if ($player->hasProgressToken(6)) {
                        $points = 3 * count($player->getProgressTokens()->array);
                        if ($points > 0) {
                            $player->increaseScore($points, self::SCORE_PROGRESSTOKENS);
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'endGameCategoryUpdate',
                                clienttranslate('${player_name} scores ${points} victory points (Progress token “${progressTokenName}”)'),
                                [
                                    'i18n' => ['progressTokenName'],
                                    'player_name' => $player->name,
                                    'points' => $points,
                                    'progressTokenName' => ProgressToken::get(6)->name,
                                    'playerIds' => [$player->id],
                                    'category' => 'progresstokens',
                                    'highlightId' => 'progress_token_6',
                                ]
                            );
                        }
                    }
                }

                foreach(Players::get() as $player) {
                    // Coins to points 3:1
                    $points = floor($player->getCoins() / 3);
                    if ($points > 0) {
                        $player->increaseScore($points, self::SCORE_COINS);
                        SevenWondersDuel::get()->notifyAllPlayers(
                            'endGameCategoryUpdate',
                            clienttranslate('${player_name} scores ${points} victory points (1 for each set of 3 coins)'),
                            [
                                'player_name' => $player->name,
                                'points' => $points,
                                'playerIds' => [$player->id],
                                'category' => 'coins',
                                'highlightId' => 'player_area_' . $player->id . '_coins_container',
                            ]
                        );
                    }
                }

                foreach(Players::get() as $player) {
                    // Military victory points
                    $points = MilitaryTrack::getVictoryPoints($player);
                    if ($points > 0) {
                        $player->increaseScore($points, self::SCORE_MILITARY);
                        SevenWondersDuel::get()->notifyAllPlayers(
                            'endGameCategoryUpdate',
                            clienttranslate('${player_name} scores ${points} victory points (Conflict pawn position)'),
                            [
                                'player_name' => $player->name,
                                'points' => $points,
                                'playerIds' => [$player->id],
                                'category' => 'military',
                                'highlightId' => 'conflict_pawn',
                            ]
                        );
                    }
                }

                if ($this->getGameStateValue(self::OPTION_AGORA)) {
                    $situation = Senate::getSituation();
                    foreach(Players::get() as $player) {
                        $playerChambers = [];
                        $playerSenatePoints = 0;
                        $playerChamberIds = [];
                        for($chamber = 1; $chamber <= 6; $chamber++) {
                            if ($situation['chambers'][$chamber]['controller'] == $player->id) {
                                // Senate victory points
                                $points = 1;
                                if (in_array($chamber, [2, 5])) $points = 2;
                                if (in_array($chamber, [3, 4])) $points = 3;

                                $playerChambers[] = $chamber;
                                $playerChamberIds[] = "chamber{$chamber}";
                                $playerSenatePoints += $points;
                            }
                        }

                        if ($playerSenatePoints > 0) {
                            $player->increaseScore($playerSenatePoints, self::SCORE_SENATE);
                            SevenWondersDuel::get()->notifyAllPlayers(
                                'endGameCategoryUpdate',
                                clienttranslate('${player_name} scores ${points} victory points (Senate Chamber(s) ${chambers})'),
                                [
                                    'player_name' => $player->name,
                                    'points' => $playerSenatePoints,
                                    'chambers' => implode(', ', $playerChambers),
                                    'playerIds' => [$player->id],
                                    'category' => 'senate',
                                    'highlightId' => $playerChamberIds,
                                ]
                            );
                        }

                    }
                }

                // Determine the winner of the game
                $winner = $this->determineWinner();

                // Pass $playerSituation by reference so the nextPlayerTurnEndGameScoring notification gets this data.
                Players::getSituation(true, $playerSituation);

                $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
            }
            else {
                $this->gamestate->nextState( self::STATE_NEXT_AGE_NAME );
            }
        }
    }

    public function checkImmediateVictory() {
        $player = Player::getActive();
        $opponent = $player->getOpponent();
        $scienceSymbolCount = $player->getScientificSymbolCount();
        $scienceSymbolCountOpponent = $opponent->getScientificSymbolCount();
        SevenWondersDuel::get()->setStat($scienceSymbolCount, SevenWondersDuel::STAT_SCIENCE_SYMBOLS, $player->id);
        SevenWondersDuel::get()->setStat($scienceSymbolCountOpponent, SevenWondersDuel::STAT_SCIENCE_SYMBOLS, $opponent->id);

        $politicalSupremacyPlayer = null;
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $situation = Senate::getSituation();
            $playerChambers = [Player::me()->id => 0, Player::opponent()->id => 0];
            foreach($situation['chambers'] as $chamber) {
                if (is_null($chamber['controller'])) {
                    break;
                }
                else {
                    $playerChambers[$chamber['controller']]++;
                    if ($playerChambers[$chamber['controller']] == 6) {
                        $politicalSupremacyPlayer = Player::get($chamber['controller']);
                    }
                }
            }
        }

        $conflictPawnPosition = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CONFLICT_PAWN_POSITION);
        if ($scienceSymbolCount >= 6 && $scienceSymbolCountOpponent >= 6) {
            $this->setGameStateValue(self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_SCIENTIFIC); // Bit of a hacky way to achieve both players' green cards getting highlighted part 1
            $this->setStat(1, self::STAT_DRAW, $player->id);
            $this->setStat(1, self::STAT_DRAW, $opponent->id);
            $this->setStat(1, self::STAT_DRAW);

            $this->notifyAllPlayers(
                'message',
                'Game ends in a draw (both players achieved Scientific Supremacy)',
                []
            );

            // Bit of a hacky way to achieve both players' green cards getting highlighted part 2
            $playersSituation = Players::getSituation(true);
            $playersSituation['winner'] = $player->id;
            SevenWondersDuel::get()->notifyAllPlayers(
                'nextPlayerTurnScientificSupremacy',
                '',
                [
                    'playersSituation' => $playersSituation,
                ]
            );
            $playersSituation['winner'] = $opponent->id;
            SevenWondersDuel::get()->notifyAllPlayers(
                'nextPlayerTurnScientificSupremacy',
                '',
                [
                    'playersSituation' => $playersSituation,
                ]
            );
            return true;
        }
        elseif ($scienceSymbolCount >= 6 || $scienceSymbolCountOpponent >= 6) {
            foreach(Players::get() as $tmpPlayer) {
                if ($tmpPlayer->getScientificSymbolCount() >= 6) {
                    $tmpPlayer->setWinner();
                    self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_SCIENTIFIC);
                    $this->setStat(1, self::STAT_SCIENTIFIC_SUPREMACY);
                    $this->setStat(1, self::STAT_SCIENTIFIC_SUPREMACY, $tmpPlayer->id);

                    SevenWondersDuel::get()->notifyAllPlayers(
                        'nextPlayerTurnScientificSupremacy',
                        clienttranslate('${player_name} wins the game through Scientific Supremacy (gathered 6 different scientific symbols)'),
                        [
                            'player_name' => $tmpPlayer->name,
                            'playerId' => $tmpPlayer->id,
                            'playersSituation' => Players::getSituation(true),
                        ]
                    );
                    return true;
                }
            }
        }
        elseif ($conflictPawnPosition <= -9 || $conflictPawnPosition >= 9) {
            $player->setWinner();
            self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_MILITARY);
            $this->setStat(1, self::STAT_MILITARY_SUPREMACY);
            $this->setStat(1, self::STAT_MILITARY_SUPREMACY, $player->id);

            SevenWondersDuel::get()->notifyAllPlayers(
                'nextPlayerTurnMilitarySupremacy',
                clienttranslate('${player_name} wins the game through Military Supremacy (Conflict pawn reached the opponent\'s capital)'),
                [
                    'player_name' => $player->name,
                    'playersSituation' => Players::getSituation(true),
                ]
            );
            return true;
        }
        elseif ($politicalSupremacyPlayer) {
            $politicalSupremacyPlayer->setWinner();
            self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_POLITICAL);
            $this->setStat(1, self::STAT_POLITICAL_SUPREMACY);
            $this->setStat(1, self::STAT_POLITICAL_SUPREMACY, $politicalSupremacyPlayer->id);

            SevenWondersDuel::get()->notifyAllPlayers(
                'nextPlayerTurnPoliticalSupremacy',
                clienttranslate('${player_name} wins the game through Political Supremacy (controls all 6 Chambers of the Senate)'),
                [
                    'player_name' => $politicalSupremacyPlayer->name,
                    'playersSituation' => Players::getSituation(true),
                ]
            );
            return true;
        }
        return false;
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
                $this->setStat(1, self::STAT_CIVILIAN_VICTORY);
                $this->setStat(1, self::STAT_CIVILIAN_VICTORY, $winner->id);

                $this->notifyAllPlayers(
                    'message',
                    '${player_name} wins the game with ${winnerPoints} victory points to ${loserPoints} (Civilian Victory)',
                    [
                        'player_name' => $winner->name,
                        'winnerPoints' => $winner->getScore(),
                        'loserPoints' => $winner->getOpponent()->getScore(),
                    ]
                );

                SevenWondersDuel::get()->notifyAllPlayers(
                    'endGameCategoryUpdate',
                    clienttranslate(''),
                    [
                        'points' => 0,
                        'playerIds' => [$winner->id],
                        'category' => 'total',
                        'highlightId' => null,
                        'stickyCategory' => true,
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
                    $this->setStat(1, self::STAT_CIVILIAN_VICTORY);
                    $this->setStat(1, self::STAT_CIVILIAN_VICTORY, $winner->id);

                    $this->notifyAllPlayers(
                        'message',
                        '${player_name} wins the game with a tied score but a majority of Civilian Building points (blue cards), ${winnerBuildings} to ${loserBuildings} (Civilian Victory)',
                        [
                            'player_name' => $winner->name,
                            'winnerBuildings' => $winner->getValue('player_score_blue'),
                            'loserBuildings' => $winner->getOpponent()->getValue('player_score_blue'),
                        ]
                    );

                    SevenWondersDuel::get()->notifyAllPlayers(
                        'endGameCategoryUpdate',
                        clienttranslate(''),
                        [
                            'points' => 0,
                            'playerIds' => [Player::me()->id, Player::opponent()->id],
                            'category' => 'total',
                            'highlightId' => null,
                            'stickyCategory' => true,
                        ]
                    );

                    SevenWondersDuel::get()->notifyAllPlayers(
                        'endGameCategoryUpdate',
                        clienttranslate(''),
                        [
                            'points' => 0,
                            'playerIds' => [$winner->id],
                            'category' => 'blue',
                            'highlightId' => null,
                            'stickyCategory' => true,
                        ]
                    );
                }
                return $winner;
            }
            else {
                $this->setGameStateValue(self::VALUE_END_GAME_CONDITION, self::END_GAME_CONDITION_DRAW);
                $this->setStat(1, self::STAT_DRAW, Player::me()->id);
                $this->setStat(1, self::STAT_DRAW, Player::opponent()->id);
                $this->setStat(1, self::STAT_DRAW);

                $this->notifyAllPlayers(
                    'message',
                    'Game ends in a draw (victory points and Civilian Building points (blue cards) count are both tied)',
                    []
                );

                SevenWondersDuel::get()->notifyAllPlayers(
                    'endGameCategoryUpdate',
                    clienttranslate(''),
                    [
                        'points' => 0,
                        'playerIds' => [Player::me()->id, Player::opponent()->id],
                        'category' => 'total',
                        'highlightId' => null,
                        'stickyCategory' => true,
                    ]
                );

                SevenWondersDuel::get()->notifyAllPlayers(
                    'endGameCategoryUpdate',
                    clienttranslate(''),
                    [
                        'points' => 0,
                        'playerIds' => [Player::me()->id, Player::opponent()->id],
                        'category' => 'blue',
                        'highlightId' => null,
                        'stickyCategory' => true,
                    ]
                );
                return null;
            }
        }
    }
}