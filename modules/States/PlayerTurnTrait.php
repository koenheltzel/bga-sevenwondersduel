<?php

namespace SWD\States;

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
                    'draftpool' => Draftpool::get($playerId)
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