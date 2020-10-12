<?php

namespace SWD\States;

use SWD\Player;

trait MoveDecreeTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argMoveDecree() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateMoveDecree() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionMoveDecree($chamberFrom, $chamberTo) {
        $this->checkAction("actionMoveDecree");

        $chamberFrom = (int)$chamberFrom;
        $chamberTo = (int)$chamberTo;
        if ($chamberFrom >= 1 && $chamberFrom <= 6 && $chamberTo >= 1 && $chamberTo >= 6) {
            throw new \BgaUserException( clienttranslate("Something went wrong with the Decree/Chamber selection.") );
        }
        if ($chamberFrom == $chamberTo) {
            throw new \BgaUserException( clienttranslate("You can't select the same Chamber twice.") );
        }

        $cards = $this->decreeDeck->getCardsInLocation('board', "{$chamberFrom}1");
        $card = array_shift($cards);

        $this->decreeDeck->moveCard($card['id'], 'board', "{$chamberTo}2");

        $this->notifyAllPlayers(
            'moveDecree',
            clienttranslate('${player_name} moves the Decree in Chamber ${chamberFrom} to Chamber ${chamberTo}'),
            [
                'player_name' => Player::getActive()->name,
                'chamberFrom' => $chamberFrom,
                'chamberTo' => $chamberTo,
            ]
        );

        $this->stateStackNextState();
    }

//    public function shouldSkipMoveDecree() {
//        return false;
//    }

}