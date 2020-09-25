<?php

namespace SWD\States;

use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Senate;
use SWD\Wonders;

trait SenateActionsTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argSenateActions() {
        return [

            'senateActionsLeft' => $this->getGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT),
            'senateActionsSection' => $this->getGameStateValue(self::VALUE_SENATE_ACTIONS_SECTION),
            // TODO: not sure what is needed below, I can image the cost of age/wonder cards can change with certain Decrees
//            'draftpool' => Draftpool::get(),
//            'wondersSituation' => Wonders::getSituation(),
//            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateSenateActions() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionSenateActionsPlaceInfluence($chamber) {
        $this->checkAction("actionSenateActionsPlaceInfluence");

        Senate::placeInfluence($chamber);

        if ($this->incGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT, -1) > 0) {
            $this->gamestate->nextState( self::STATE_SENATE_ACTIONS_NAME);
        }
        else {
            $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
        }

//        $progressToken = ProgressToken::get($progressTokenId);
//        $payment = $progressToken->construct(Player::getActive());
//
//        // Return any remaining progress tokens in the active selection back to the box.
//        $this->progressTokenDeck->moveAllCardsInLocation('selection', 'box');
//
//        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }

    public function actionMoveInfluence($chamberFrom, $chamberTo) {
        $this->checkAction("actionMoveInfluence");

//        $progressToken = ProgressToken::get($progressTokenId);
//        $payment = $progressToken->construct(Player::getActive());
//
//        // Return any remaining progress tokens in the active selection back to the box.
//        $this->progressTokenDeck->moveAllCardsInLocation('selection', 'box');
//
//        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }

    public function actionSenateActionsSkip() {
        $this->checkAction("actionSenateActionsSkip");
    }
}