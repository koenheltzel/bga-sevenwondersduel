<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Draftpool;
use SWD\Player;

trait PlayerTurnTrait {

    public function enterStatePlayerTurn() {
//        $this->notifyAllPlayers(
//            'updateDraftpool',
//            '',
//            [
//                'draftpool' => Draftpool::get(),
//                'progress_tokens' => $this->progressTokenDeck->getCardsInLocation("board"),
//            ]
//        );
    }

    public function actionConstructBuilding($cardId) {
        $this->checkAction("actionConstructBuilding");

        $playerId = self::getCurrentPlayerId();

        $age = SevenWondersDuel::get()->getCurrentAge();
        $cards = $this->buildingDeck->getCardsInLocation("age{$age}");
        if (!array_key_exists($cardId, $cards)) {
            throw new \BgaUserException( self::_("The building you selected is not available.") );
        }

        $card = $cards[$cardId];
        $building = Building::get($card['type_arg']);

        $payment = Player::me()->calculateCost($building);
        $totalCost = $payment->totalCost();
        if ($totalCost > Player::me()->getCoins()) {
            throw new \BgaUserException( self::_("You can't afford the building you selected.") );
        }

        if ($totalCost > 0) {
            Player::me()->increaseCoins(-$totalCost);
        }
        $this->buildingDeck->moveCard($cardId, $playerId);

        $this->notifyAllPlayers(
            'constructBuilding',
            clienttranslate('${player_name} constructed building ${buildingName} for ${cost}.'),
            [
                'buildingName' => $building->name,
                'cost' => $totalCost > 0 ? $totalCost . " " . COINS : 'free',
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => $playerId,
                'buildingId' => $building->id,
                'draftpool' => Draftpool::get(),
                'playerCoins' => Player::me()->getCoins(),
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