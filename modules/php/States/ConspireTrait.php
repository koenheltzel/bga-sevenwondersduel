<?php

namespace SWD\States;

use SWD\Conspiracies;
use SWD\Conspiracy;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonders;

trait ConspireTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argConspire() {
        $data = [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                Player::getActive()->id => [ // Using "active" keyword inside "_private", you select active player(s)
                    'conspiracies' => $this->conspiracyDeck->getCardsInLocation('conspire') // will be send only to active player(s)
                ]
            ],
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
            'wonderSelectionRound' => $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND),
        ];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) == 0) {
            $data['draftpool'] = Draftpool::revealCards(1); // Curia Julia during Wonder Selection
        }
        return $data;
    }

    public function enterStateConspire() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseConspiracy($conspiracyId) {
        $this->checkAction("actionChooseConspiracy");

        $conspiracy = Conspiracy::get($conspiracyId);
        $conspiracy->choose(Player::getActive());

        $this->gamestate->nextState( self::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_NAME);

    }
}