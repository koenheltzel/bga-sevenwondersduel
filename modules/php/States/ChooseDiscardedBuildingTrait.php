<?php

namespace SWD\States;

use SWD\Building;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonder;
use SWD\Wonders;

trait ChooseDiscardedBuildingTrait
{

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseDiscardedBuilding() {
        return [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateChooseDiscardedBuilding() {

    }

    public function actionChooseDiscardedBuilding($buildingId) {
        $this->checkAction("actionChooseDiscardedBuilding");

//        if (!Player::opponent()->hasBuilding($buildingId)) {
//            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
//        }

//        SevenWondersDuel::get()->buildingDeck->insertCardOnExtremePosition($buildingId, 'discard', true);

        $this->notifyAllPlayers(
            'constructDiscardedBuilding',
            clienttranslate('${player_name} constructed discarded building “${buildingName}” for free (Wonder “${wonderName}”)'),
            [
                'buildingName' => Building::get($buildingId)->name,
                'wonderName' => Wonder::get(5)->name,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $buildingId,
            ]
        );

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);

    }
}