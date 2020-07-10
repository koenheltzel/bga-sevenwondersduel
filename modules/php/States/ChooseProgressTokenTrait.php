<?php

namespace SWD\States;

use SWD\Draftpool;
use SWD\MilitaryTrack;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\ProgressTokens;
use SWD\Wonders;

trait ChooseProgressTokenTrait {

    public function enterStateChooseProgressToken() {

    }

    public function actionChooseProgressToken($progressTokenId) {
        $this->checkAction("actionChooseProgressToken");

        $progressToken = ProgressToken::get($progressTokenId);
        $payment = $progressToken->construct(Player::me());

        $this->notifyAllPlayers(
            'progressTokenChosen',
            clienttranslate('${player_name} chose progress token ${progressTokenName}.'),
            [
                'progressTokenName' => $progressToken->name,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'progressTokenId' => $progressToken->id,
                'progressTokensSituation' => ProgressTokens::getSituation(),
                'draftpool' => Draftpool::get(),
                'wondersSituation' => Wonders::getSituation(),
                'playersSituation' => Players::getSituation(),
                'militaryTrack' => MilitaryTrack::getData(),
                'payment' => $payment,
            ]
        );

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }
}