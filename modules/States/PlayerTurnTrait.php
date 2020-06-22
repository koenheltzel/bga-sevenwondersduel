<?php

namespace SWD\States;

use SWD\Building;
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

        $playerId = self::getCurrentPlayerId();

        $cards = $this->buildingDeck->getCardsInLocation("age1");
        if (!array_key_exists($cardId, $cards)) {
            throw new \BgaUserException( self::_("The building you selected is not available.") );
        }

        $this->buildingDeck->moveCard($cardId, $playerId);
        $card = $cards[$cardId];

        $building = Building::get($card['type_arg']);
        $this->notifyAllPlayers(
            'constructBuilding',
            clienttranslate('${player_name} constructed building ${buildingName}.'),
            [
                'buildingName' => $building->name,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => $playerId,
                'buildingId' => $building->id,
                'draftpool' => Draftpool::get($playerId),
            ]
        );

        $this->gamestate->nextState( self::STATE_CONSTRUCT_BUILDING_NAME);
    }

    public function actionDiscardBuilding($cardId) {
        $this->checkAction("actionDiscardBuilding");

    }

    public function actionConstructWonder($cardId, $wonderId) {
        $this->checkAction("actionConstructWonder");
    }
}