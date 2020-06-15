<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Draftpool;

trait PlayerTurnTrait {

    public function getPlayerTurnData($playerId) {
        return [
            'draftpool' => Draftpool::get($playerId)
        ];
    }

    public function notifyPlayersOfDraftpool() {
        $players = $this->loadPlayersBasicInfos();
        foreach ($players AS $playerId => $player) {
            $this->notifyPlayer(
                $playerId,
                'updateDraftpool',
                '',
                [
                    'draftpool' => Draftpool::get($playerId),
                    'progress_tokens' => SevenWondersDuel::get()->progressTokenDeck->getCardsInLocation("board"),
                ]
            );
        }
    }

    public function stPlayerTurn() {
//        $this->notifyAllPlayers(
//            'playerTurn',
//            "playerTurn notification log",
//            []
//        );

        $this->notifyPlayersOfDraftpool();
    }
}