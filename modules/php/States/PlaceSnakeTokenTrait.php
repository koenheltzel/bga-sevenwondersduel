<?php

namespace SWD\States;

use SWD\Building;
use SWD\Divinity;
use SWD\Player;

trait PlaceSnakeTokenTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argPlaceSnakeToken() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStatePlaceSnakeToken() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionPlaceSnakeToken($buildingId) {
        $this->checkAction("actionPlaceSnakeToken");

        $building = Building::get($buildingId);

        $activePlayer = Player::getActive();
        $opponent = $activePlayer->getOpponent();

        if (!$opponent->hasBuilding($buildingId)) {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }
        if ($building->type != Building::TYPE_GREEN) {
            throw new \BgaUserException( clienttranslate("The building you selected is not a green card.") );
        }

        $this->setGameStateValue(\SevenWondersDuel::VALUE_SNAKE_TOKEN_BUILDING_ID, $buildingId);

        $this->notifyAllPlayers(
            'placeSnakeToken',
            clienttranslate('${player_name} places the Snake token on opponent\'s building “${buildingName}” (Divinity “${divinityName}”)'),
            [
                'i18n' => ['buildingName', 'divinityName'],
                'buildingName' => $building->name,
                'divinityName' => Divinity::get(3)->name,
                'player_name' => $activePlayer->name,
                'buildingId' => $buildingId,
            ]
        );

        // Active player can have completed a science symbol pair. Let active player go first, but both get to do the action before determining immediate win.
        if ($activePlayer->hasScientificSymbolPair($building->scientificSymbol) && Building::gatheredSciencePairNotification($activePlayer)) {
            $this->prependStateStack([self::STATE_CHOOSE_PROGRESS_TOKEN_NAME]);
        }

        $this->stateStackNextState();
    }

    public function shouldSkipPlaceSnakeToken() {
        $activePlayer = Player::getActive();
        $opponent = $activePlayer->getOpponent();
        if (count($opponent->getBuildings()->filterByTypes([Building::TYPE_GREEN])->array) == 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} can\'t choose a green card from the opponent to place the Snake token on (Divinity “${divinityName}”)'),
                [
                    'i18n' => ['divinityName'],
                    'player_name' => $activePlayer->name,
                    'divinityName' => Divinity::get(3)->name
                ]
            );
            return true;
        }
        return false;
    }

}