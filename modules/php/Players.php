<?php


namespace SWD;


use SevenWondersDuel;

class Players extends Base
{

    /**
     * @return Player[] array
     */
    public static function get() {
        return [Player::me(), Player::opponent()];
    }

    public static function getSituation($endGameScoring=false) {
        $data = [];
        foreach(Players::get() as $player) {
            $data[$player->id] = [
                'score' => $player->getScore(),
                'coins' => $player->getCoins(),
                'scienceSymbolCount' => $player->getScientificSymbolCount(),
            ];
            if ($endGameScoring) {
                $scoringCategories = $player->getScoreCategories();
                $data[$player->id] = array_merge($data[$player->id], array_shift($scoringCategories)); // [0] doesn't work (the index can be a different number for some reason. So we use array_shift().
                $data[$player->id]['winner'] = $player->isWinner();
                $data['endGameCondition'] = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION);
            }
        }
        return $data;
    }

    public static function determineWinner() {
        return self::getWinner(true);
    }

    /**
     * @return Player|null
     */
    public static function getWinner($determine=false) {
        $meScore = Player::me()->getScore();
        $opponentScore = Player::opponent()->getScore();
        if ($meScore != $opponentScore) {
            $winner = $meScore > $opponentScore ? Player::me() : Player::opponent();
            if($determine) {
                $winner->setWinner();
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION, SevenWondersDuel::END_GAME_CONDITION_NORMAL);

                SevenWondersDuel::get()->notifyAllPlayers(
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
                    SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION, SevenWondersDuel::END_GAME_CONDITION_NORMAL_AUX);

                    SevenWondersDuel::get()->notifyAllPlayers(
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
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION, SevenWondersDuel::END_GAME_CONDITION_DRAW);

                SevenWondersDuel::get()->notifyAllPlayers(
                    'message',
                    'Game ends in a draw (victory points and blue buildings count are both tied)',
                    []
                );
                return null;
            }
        }
    }

}