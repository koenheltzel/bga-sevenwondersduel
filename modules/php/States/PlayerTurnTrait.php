<?php

namespace SWD\States;

use SWD\Building;
use SWD\Draftpool;
use SWD\MilitaryTrack;
use SWD\Player;
use SWD\Players;
use SWD\Wonder;
use SWD\Wonders;

trait PlayerTurnTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argPlayerTurn() {
        return [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
            'militaryTrack' => MilitaryTrack::getData(),
        ];
    }

    public function enterStatePlayerTurn() {

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
                'payment' => $payment,
                'militaryTrack' => MilitaryTrack::getData(),
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
                'payment' => $payment,
                'wondersSituation' => Wonders::getSituation(),
                'militaryTrack' => MilitaryTrack::getData(),
            ]
        );

//        switch ($wonder->id) {
//            case 5:
//                $this->gamestate->nextState( self::STATE_CHOOSE_DISCARDED_BUILDING_NAME);
//                break;
//            case 6:
//                $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME);
//                break;
//            case 9:
//            case 12:
//                $this->gamestate->nextState( self::STATE_CHOOSE_OPPONENT_BUILDING_NAME);
//                break;
//            default:
                $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
//                break;
//        }
    }
}