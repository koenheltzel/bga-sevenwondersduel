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

}