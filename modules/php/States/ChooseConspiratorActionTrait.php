<?php

namespace SWD\States;

use SevenWondersDuelAgora;
use SWD\Conspiracies;
use SWD\Draftpool;
use SWD\Material;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Wonders;

trait ChooseConspiratorActionTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseConspiratorAction() {
        $data = [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateChooseConspiratorAction() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseConspiratorActionPlaceInfluence() {
        $this->checkAction("actionChooseConspiratorActionPlaceInfluence");

        $this->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose to Place Influence'),
            [
                'player_name' => Player::getActive()->name
            ]
        );

        $this->prependStateStackAndContinue([self::STATE_PLACE_INFLUENCE_NAME]);
    }

    public function actionConspire() {
        $this->checkAction("actionConspire");

        // The maximum conspiracies used in 1 game is 13 (6 * 2 using progress token + 1x Curia Julia), so we can always draw 2.

        $player = Player::getActive();

        if ($player->hasProgressToken(12)) {
            $cards = Conspiracies::getDeckCardsSorted('deck');
            $cardIds = [];
            for ($i = 0; $i < 2; $i++) {
                $card = array_pop($cards);
                SevenWondersDuelAgora::get()->conspiracyDeck->insertCardOnExtremePosition($card['id'], Player::getActive()->id, true);
                $cardIds[] = $card['id'];
            }

            // Message to both players
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} chose to Conspire, keeps both Conspiracies (Progress token “${progressTokenName}”)'),
                [
                    'i18n' => ['progressTokenName'],
                    'player_name' => Player::getActive()->name,
                    'progressTokenName' => ProgressToken::get(12)->name,
                ]
            );

            // To active player (secret ids)
            $this->notifyPlayer(
                $player->id,
                'conspireKeepBoth',
                '',
                [
                    'conspiracyIds' => $cardIds,
                ]
            );

            // To other player & spectators
            $this->notifyAllPlayers(
                'conspireKeepBoth',
                '',
                [
                    'conspiracyIds' => [17, 17],
                ]
            );

            $this->stateStackNextState();
        }
        else {
            // Normal Conspire
            Material::get()->conspiracies->conspire();

            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} chose to Conspire'),
                [
                    'player_name' => Player::getActive()->name
                ]
            );

            $this->setGameStateValue(self::VALUE_CONSPIRE_RETURN_STATE, self::STATE_NEXT_PLAYER_TURN_ID);
            $this->gamestate->nextState( self::STATE_CONSPIRE_NAME);
        }
    }
}