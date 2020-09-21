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
        return [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                'active' => [ // Using "active" keyword inside "_private", you select active player(s)
                    'conspiracyId' => array_shift($cards)['id'] // will be send only to active player(s)
                ]
            ],
        ];
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
                'topOrBottom' => $top ? clienttranslate('top') : clienttranslate('bottom'),
                'player_name' => Player::getActive()->name,
            ]
        );

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }
}