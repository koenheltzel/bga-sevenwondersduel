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

        $cardInfo = $this->buildingDeck->getCard($buildingId);
        if ($cardInfo['location'] != 'discard') {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }

        $building = Building::get($buildingId);
        $payment = $building->construct(Player::me(), null, true);

        if ($payment->selectProgressToken) {
            $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_NAME);
        }
        else {
            $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
        }
    }
}