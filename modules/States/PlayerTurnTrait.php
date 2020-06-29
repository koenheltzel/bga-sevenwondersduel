<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Draftpool;
use SWD\Player;
use SWD\Wonders;

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

        if (!Draftpool::buildingAvailable($building->id)) {
            throw new \BgaUserException( self::_("The building you selected is still covered by other buildings, so it can't be picked.") );
        }

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
                'wondersSituation' => Wonders::getSituation(),
                'playerCoins' => Player::me()->getCoins(),
            ]
        );

        $this->gamestate->nextState( self::STATE_CONSTRUCT_BUILDING_NAME);
    }

    public function actionDiscardBuilding($cardId) {
        $this->checkAction("actionDiscardBuilding");

        $playerId = self::getCurrentPlayerId();

        $age = SevenWondersDuel::get()->getCurrentAge();
        $cards = $this->buildingDeck->getCardsInLocation("age{$age}");
        if (!array_key_exists($cardId, $cards)) {
            throw new \BgaUserException( self::_("The building you selected is not available.") );
        }

        $card = $cards[$cardId];
        $building = Building::get($card['type_arg']);

        if (!Draftpool::buildingAvailable($building->id)) {
            throw new \BgaUserException( self::_("The building you selected is still covered by other buildings, so it can't be picked.") );
        }

        $discardGain = Player::me()->calculateDiscardGain($building);
        Player::me()->increaseCoins($discardGain);

        $this->buildingDeck->moveCard($cardId, 'discard');

        $this->notifyAllPlayers(
            'discardBuilding',
            clienttranslate('${player_name} discarded building ${buildingName} for ${gain}.'),
            [
                'buildingName' => $building->name,
                'gain' => $discardGain . " " . COINS,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => $playerId,
                'buildingId' => $building->id,
                'draftpool' => Draftpool::get(),
                'wondersSituation' => Wonders::getSituation(),
                'playerCoins' => Player::me()->getCoins(),
            ]
        );

        $this->gamestate->nextState( self::STATE_DISCARD_BUILDING_NAME);

    }

    public function actionConstructWonder($cardId, $wonderId) {
        $this->checkAction("actionConstructWonder");
    }
}