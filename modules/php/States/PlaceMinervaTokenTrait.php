<?php

namespace SWD\States;

use SWD\Divinity;
use SWD\MilitaryTrack;
use SWD\Player;

trait PlaceMinervaTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argPlaceMinervaToken() {
        $data = [
            'militaryTrack' => MilitaryTrack::getData()
        ];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStatePlaceMinervaToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionPlaceMinervaToken($position) {
        $this->checkAction("actionPlaceMinervaToken");

        if ($position == $this->getGameStateValue(self::VALUE_CONFLICT_PAWN_POSITION)) {
            throw new \BgaUserException( clienttranslate("You can't place the Minerva pawn on the same space as the Conflict pawn.") );
        }

        $this->setGameStateValue(self::VALUE_MINERVA_PAWN_POSITION, $position);

        $this->notifyAllPlayers(
            'placeMinervaToken',
            clienttranslate('${player_name} places the Minerva pawn on the Military track (Divinity “${divinityName}”)'),
            [
                'i18n' => ['divinityName'],
                'divinityName' => Divinity::get(14)->name,
                'player_name' => Player::getActive()->name,
                'militaryTrack' => MilitaryTrack::getData(),
            ]
        );

        $this->stateStackNextState();
    }

    public function shouldSkipPlaceMinervaToken() {
        return false;
    }

}