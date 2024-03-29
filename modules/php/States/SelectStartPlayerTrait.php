<?php

namespace SWD\States;

use SWD\Divinities;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonders;

trait SelectStartPlayerTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argSelectStartPlayer() {
        $draftpool = Draftpool::get();
        $data = [
            'ageRoman' => ageRoman($draftpool['age']),
            'draftpool' => $draftpool,
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

    public function enterStateSelectStartPlayer() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionSelectStartPlayer($playerId) {
        $this->checkAction("actionSelectStartPlayer");

        $this->performActionSelectStartPlayer(Player::get($playerId));
    }

    /**
     * Broken out of actionSelectStartPlayer so it's callable by zombieTurn as well.
     * @param Player $player Has to be passed because zombieTurn can't use current player
     */
    private function performActionSelectStartPlayer(Player $player) {
        $this->setGameStateValue(self::VALUE_AGE_START_PLAYER, $player->id);

        $this->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} begins Age ${ageRoman}'),
            [
                'player_name' => $player->name,
                'ageRoman' => ageRoman($this->getGameStateValue(self::VALUE_CURRENT_AGE)),
            ]
        );

        $this->gamestate->nextState( self::STATE_START_PLAYER_SELECTED_NAME );
    }
}