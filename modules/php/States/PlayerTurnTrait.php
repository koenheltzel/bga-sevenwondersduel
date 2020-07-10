<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Draftpool;
use SWD\MilitaryTrack;
use SWD\Player;
use SWD\Players;
use SWD\Wonder;
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

    public function actionConstructBuilding($buildingId) {
        $this->checkAction("actionConstructBuilding");

        $building = Building::get($buildingId);
        $payment = $building->construct(Player::me());

        $this->notifyAllPlayers(
            'constructBuilding',
            clienttranslate('${player_name} constructed building ${buildingName} for ${cost}.'),
            [
                'buildingName' => $building->name,
                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() . " " . COINS : 'free',
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $building->id,
                'draftpool' => Draftpool::get(),
                'wondersSituation' => Wonders::getSituation(),
                'playersSituation' => Players::getSituation(),
                'militaryTrack' => MilitaryTrack::getData(),
                'payment' => $payment,
            ]
        );

        if ($payment->newScientificSymbolPair) { // TODO check if there are progress tokens left to choose from
            $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_NAME);
        }
        else {
            $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
        }


    }

    public function actionDiscardBuilding($buildingId) {
        $this->checkAction("actionDiscardBuilding");

        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();
        $discardGain = $building->discard(Player::me());

        $this->notifyAllPlayers(
            'discardBuilding',
            clienttranslate('${player_name} discarded building ${buildingName} for ${gainDescription}.'),
            [
                'buildingName' => $building->name,
                'gain' => $discardGain,
                'gainDescription' => $discardGain . " " . COINS,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $building->id,
                'draftpool' => Draftpool::get(),
                'wondersSituation' => Wonders::getSituation(),
                'playersSituation' => Players::getSituation(),
            ]
        );

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);

    }

    public function actionConstructWonder($buildingId, $wonderId) {
        $this->checkAction("actionConstructWonder");

        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();

        $wonder = Wonder::get($wonderId);
        $wonder->checkWonderAvailable();
        $payment = $wonder->construct(Player::me(), $building);

        $this->notifyAllPlayers(
            'constructWonder',
            clienttranslate('${player_name} constructed wonder ${wonderName} for ${cost} using ${buildingName}.'),
            [
                'buildingName' => $building->name,
                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() . " " . COINS : 'free',
                'wonderName' => $wonder->name,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $building->id,
                'wonderId' => $wonder->id,
                'draftpool' => Draftpool::get(),
                'wondersSituation' => Wonders::getSituation(),
                'playersSituation' => Players::getSituation(),
                'militaryTrack' => MilitaryTrack::getData(),
                'payment' => $payment,
            ]
        );

        $this->gamestate->nextState( self::STATE_CONSTRUCT_WONDER_NAME);
    }
}