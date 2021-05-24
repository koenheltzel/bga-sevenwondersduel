<?php

namespace SWD\States;

use SWD\Conspiracies;
use SWD\Draftpool;
use SWD\Material;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Wonders;

trait ChooseConspireRemnantPositionTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseConspireRemnantPosition() {
        $cards = $this->conspiracyDeck->getCardsInLocation('conspire');
        $data = [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                Player::getActive()->id => [ // Using "active" keyword inside "_private", you select active player(s)
                    'conspiracyId' => array_shift($cards)['id'] // will be send only to active player(s)
                ]
            ],
        ];
        $data['wonderSelectionRound'] = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) == 0) {
            $data['draftpool'] = Draftpool::get(); // For F5 while selecting Curia Julia during Wonder Selection
        }
        return $data;
    }

    public function enterStateChooseConspireRemnantPosition() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseConspireRemnantPosition($top) {
        $this->checkAction("actionChooseConspireRemnantPosition");

        Material::get()->conspiracies->conspireRemnantPosition($top);

        $this->notifyAllPlayers(
            'chooseConspireRemnantPosition',
            clienttranslate('${player_name} put the remaining Conspiracy card on ${topOrBottom} of the deck'),
            [
                'i18n' => ['topOrBottom'],
                'topOrBottom' => $top ? clienttranslate('top') : clienttranslate('bottom'),
                'onTop' => (int)$top, // Used by animation
                'player_name' => Player::getActive()->name,
            ]
        );

        $state = $this->getState($this->getGameStateValue(self::VALUE_CONSPIRE_RETURN_STATE));

        $this->prependStateStackAndContinue([$state['name']]);
    }
}