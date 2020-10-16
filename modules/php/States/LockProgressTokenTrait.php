<?php

namespace SWD\States;

use SWD\Conspiracy;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;

trait LockProgressTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argLockProgressToken() {
        $data = [
            '_private' => [ // Using "_private" keyword, all data inside this array will be made private
                Player::getActive()->id => [ // Using "active" keyword inside "_private", you select active player(s)
                    'progressTokensFromBox' => $this->progressTokenDeck->getCardsInLocation('selection') // will be send only to active player(s)
                ]
            ],
        ];
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        }
        return $data;
    }

    public function enterStateLockProgressToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionLockProgressToken($progressTokenId) {
        $this->checkAction("actionLockProgressToken");

        $player = Player::getActive();

        $progressToken = ProgressToken::get($progressTokenId);
        $card = $this->progressTokenDeck->getCard($progressTokenId);
        $this->progressTokenDeck->moveCard($progressTokenId, 'conspiracy5');

        if ($card['location'] == "selection") {
            // Message to all
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} locks away a Progress Token from the box, it can\'t be used during this game (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['conspiracyName'],
                    'conspiracyName' => Conspiracy::get(5)->name,
                    'player_name' => $player->name
                ]
            );

            // To active player (secret id)
            $this->notifyPlayer(
                $player->id,
                'lockProgressToken',
                '',
                [
                    'progressTokenId' => $progressToken->id,
                ]
            );

            // To other player & spectators
            $this->notifyAllPlayers(
                'lockProgressToken',
                '',
                [
                    'progressTokenId' => 16,
                ]
            );
        }
        elseif ($card['location'] == "board") {
            $this->notifyAllPlayers(
                'lockProgressToken',
                clienttranslate('${player_name} locks away Progress Token “${progressTokenName}” from the board, it can\'t be used during this game (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['conspiracyName', 'progressTokenName'],
                    'conspiracyName' => Conspiracy::get(5)->name,
                    'progressTokenName' => $progressToken->name,
                    'progressTokenId' => $progressToken->id,
                    'player_name' => $player->name
                ]
            );
        }
        else {
            $this->notifyAllPlayers(
                'lockProgressToken',
                clienttranslate('${player_name} locks away Progress Token “${progressTokenName}” from ${opponent_name}, it can\'t be used during this game (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['conspiracyName', 'progressTokenName'],
                    'conspiracyName' => Conspiracy::get(5)->name,
                    'progressTokenName' => $progressToken->name,
                    'progressTokenId' => $progressToken->id,
                    'player_name' => $player->name,
                    'opponent_name' => $player->getOpponent()->name,
                ]
            );
        }

        // Return any remaining progress tokens in the active selection back to the box.
        $this->progressTokenDeck->moveAllCardsInLocation('selection', 'box');

        $this->stateStackNextState();
    }

    public function shouldSkipLockProgressToken() {
        // Always possible
        return false;
    }

}