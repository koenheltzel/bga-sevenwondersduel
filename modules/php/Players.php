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
        $winner = null;
        foreach(Players::get() as $player) {
            $data[$player->id] = [
                'score' => $player->getScore(),
                'coins' => $player->getCoins(),
                'scienceSymbolCount' => $player->getScientificSymbolCount(),
            ];
            $scoringCategories = $player->getScoreCategories();
            $data[$player->id] = array_merge($data[$player->id], array_shift($scoringCategories)); // [0] doesn't work (the index can be a different number for some reason. So we use array_shift().
            if ($endGameScoring) {
                if ($player->isWinner()) {
                    $data[$player->id]['winner'] = 1;
                    $winner = $player->id;
                }
            }
        }
        if ($endGameScoring) {
            $data['endGameCondition'] = (int)SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_END_GAME_CONDITION);
            if ($winner) {
                $data['winner'] = $winner;
            }
        }
        return $data;
    }

}