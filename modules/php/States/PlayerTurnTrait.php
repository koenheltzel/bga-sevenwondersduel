<?php

namespace SWD\States;

use SWD\Building;
use SWD\Draftpool;
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
        ];
    }

    public function enterStatePlayerTurn() {

    }

    public function actionConstructBuilding($buildingId) {
        $this->checkAction("actionConstructBuilding");

        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();
        $payment = $building->construct(Player::me());

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
            clienttranslate('${player_name} discarded building “${buildingName}” for ${gainDescription}.'),
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

        switch ($wonder->id) {
            case 5:
                $this->gamestate->nextState( self::STATE_CHOOSE_DISCARDED_BUILDING_NAME);
                break;
//            case 6:
//                $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME);
//                break;
            case 9:
            case 12:
                $this->setGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER, $wonderId);
                $this->gamestate->nextState( self::STATE_CHOOSE_OPPONENT_BUILDING_NAME);
                break;
            default:
                $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
                break;
        }
    }
}