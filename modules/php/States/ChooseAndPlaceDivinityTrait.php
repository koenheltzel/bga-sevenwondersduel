<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Divinity;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonders;

trait ChooseAndPlaceDivinityTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseAndPlaceDivinity() {
        $cards = $this->divinityDeck->getCardsInLocation('selection');
        $firstCard = array_slice($cards, 0, 1)[0];
        $data = [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                Player::getActive()->id => [ // Using "active" keyword inside "_private", you select active player(s)
                    'divinities' => $cards // will be send only to active player(s)
                ]
            ],
            'divinitiesType' => (int)$firstCard['type'], // Normal & For F5 while selecting Curia Julia during Wonder Selection
            'draftpool' => Draftpool::get(), // Normal & For F5 while selecting Curia Julia during Wonder Selection
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
            'wonderSelectionRound' => $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND),
            'divinitiesSituation' => Divinities::getSituation(),
        ];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateChooseAndPlaceDivinity() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseAndPlaceDivinity($divinityId, $space) {
        $this->checkAction("actionChooseAndPlaceDivinity");

        $divinity = Divinity::get($divinityId);
        $divinity->place($space);

        $this->stateStackNextState();
    }
}