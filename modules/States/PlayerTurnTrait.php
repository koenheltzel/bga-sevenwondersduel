<?php

namespace SWD\States;use SWD\Draftpool;

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
                    'progress_tokens' => $this->progressTokenDeck->getCardsInLocation("board"),
                ]
            );
        }
    }

    public function enterStatePlayerTurn() {
//        $this->notifyAllPlayers(
//            'playerTurn',
//            "playerTurn notification log",
//            []
//        );

        $this->notifyPlayersOfDraftpool();
    }

    public function actionConstructBuilding($cardId) {
        $this->checkAction("actionConstructBuilding");

    }

    public function actionDiscardBuilding($cardId) {
        $this->checkAction("actionDiscardBuilding");

    }

    public function actionConstructWonder($cardId, $wonderId) {
        $this->checkAction("actionConstructWonder");

    }
}