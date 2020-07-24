<?php


namespace SWD;


use SevenWondersDuel;

class Players
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
            ];
            if ($endGameScoring) {
                $scoringCategories = $player->getScoreCategories();
                $data[$player->id] = array_merge($data[$player->id], array_shift($scoringCategories)); // [0] doesn't work (the index can be a different number for some reason. So we use array_shift().
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
            $player = $meScore > $opponentScore ? Player::me() : Player::opponent();
            if($determine) {
                $player->setWinner();
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION, SevenWondersDuel::END_GAME_CONDITION_NORMAL);
            }
            return $player;
        }
        else {
            $meBluePoints = Player::me()->getValue('player_score_blue');
            $opponentBluePoints = Player::opponent()->getValue('player_score_blue');
            if ($meBluePoints != $opponentBluePoints) {
                $player = $meBluePoints > $opponentBluePoints ? Player::me() : Player::opponent();
                if ($determine) {
                    $player->setWinner();
                    SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION, SevenWondersDuel::END_GAME_CONDITION_NORMAL_AUX);
                }
                return $player;
            }
            else {
                SevenWondersDuel::get()->setGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION, SevenWondersDuel::END_GAME_CONDITION_DRAW);
                return null;
            }
        }
    }

}