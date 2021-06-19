<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Wonders;

trait ChooseProgressTokenFromBoxTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseProgressTokenFromBox() {
        $data = [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                Player::getActive()->id => [ // Using "active" keyword inside "_private", you select active player(s)
                    'progressTokensFromBox' => $this->progressTokenDeck->getCardsInLocation('selection') // will be send only to active player(s)
                ]
            ],
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
        if ($this->getGameStateValue(self::OPTION_PANTHEON)) {
            $data['divinitiesSituation'] = Divinities::getSituation();
        }
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        }
        return $data;
    }

    public function enterStateChooseProgressTokenFromBox() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseProgressTokenFromBox($progressTokenId) {
        $this->checkAction("actionChooseProgressTokenFromBox");

        $progressToken = ProgressToken::get($progressTokenId);
        $payment = $progressToken->construct(Player::getActive());

        // Return any remaining progress tokens in the active selection back to the box.
        $this->progressTokenDeck->moveAllCardsInLocation('selection', 'box');

        if ($payment->selectProgressToken) {
            $this->prependStateStackAndContinue([self::STATE_CHOOSE_PROGRESS_TOKEN_NAME]);
        }
        else {
            // From Wonder 6 we go to next player turn, from Conspiracy 10 we go to player turn
            $this->stateStackNextState(self::STATE_NEXT_PLAYER_TURN_NAME);
        }
    }
}