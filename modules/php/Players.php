<?php


namespace SWD;


use SevenWondersDuelPantheon;

class Players extends Base
{

    /**
     * @return Player[] array
     */
    public static function get() {
        return [Player::me(), Player::opponent()];
    }

    public static function getSituation($endGameScoring=false, &$data = []) {
        $winner = null;
        foreach(Players::get() as $player) {
            $data[$player->id] = [
                'score' => $player->getScore(),
                'coins' => $player->getCoins(),
                'scienceSymbolCount' => $player->getScientificSymbolCount(),
            ];
            if (SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::OPTION_PANTHEON)) {
                if ($player->hasDivinity(4)) {
                    $data[$player->id]['astarteCoins'] = $player->getAstarteCoins();
                }
                if ($player->hasSnakeToken()) {
                    $data[$player->id]['snakeTokenBuildingId'] = $player::snakeTokenBuilding()->id;
                }

            }
            if (SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::OPTION_AGORA)) {
                $data[$player->id]['cubes'] = $player->getCubes();
            }

            $scoringCategories = $player->getScoreCategories();
            foreach (array_shift($scoringCategories) as $key => $value) {
                $data[$player->id][$key] = (int)$value;
            }
            if ($endGameScoring) {
                if ($player->isWinner()) {
                    $data[$player->id]['winner'] = 1;
                    $winner = $player->id;
                }
            }
        }
        if ($endGameScoring) {
            $data['endGameCondition'] = (int)SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_END_GAME_CONDITION);
            if ($winner) {
                $data['winner'] = $winner;
            }
        }
        return $data;
    }

}