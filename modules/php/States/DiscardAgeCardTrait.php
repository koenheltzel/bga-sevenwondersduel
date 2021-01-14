<?php

namespace SWD\States;

use SWD\Building;
use SWD\Draftpool;
use SWD\MythologyToken;
use SWD\MythologyTokens;
use SWD\OfferingToken;
use SWD\OfferingTokens;
use SWD\Player;

trait DiscardAgeCardTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argDiscardAgeCard() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateDiscardAgeCard() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionDiscardAgeCard($location) {
        $this->checkAction("actionDiscardAgeCard");

        $player = Player::getActive();

        $currentAge = $this->getGameStateValue(self::VALUE_CURRENT_AGE);
        $buildingCards = $this->buildingDeck->getCardsInLocation("age{$currentAge}", $location);
        $buildingCard = array_shift($buildingCards);
        $building = Building::get($buildingCard['id']);
        $building->checkBuildingAvailable(false);
        $buildingRowCol = Draftpool::getBuildingRowCol($currentAge, $building->id);

        $draftpool = Draftpool::get();
        $tokens = null;
        if ($currentAge == 1) {
            $tokens = $draftpool['mythologyTokens'];
        }
        if ($currentAge == 2) {
            $tokens = $draftpool['offeringTokens'];
        }
        foreach($tokens as $token) {
            if ($token['rowCol'] == [$buildingRowCol[0], $buildingRowCol[1]]) {
                if ($currentAge == 1) {
                    $mythologyToken = MythologyToken::get($token['id']);
                    $mythologyToken->take($player, $building);
                }
                if ($currentAge == 2) {
                    $offeringToken = OfferingToken::get($token['id']);
                    $offeringToken->take($player, $building);
                }
                break;
            }
        }

        $building->discard($player, false);

        $this->notifyAllPlayers(
            'discardBuilding',
            clienttranslate('${player_name} chose to place ${buildingName} in the discard pile'),
            [
                'i18n' => ['buildingName'],
                'player_name' => $player->name,
                'buildingName' => $building->name,
                'gain' => 0,
                'playerId' => $player->id,
                'buildingId' => $building->id,
                'buildingDomId' => $buildingRowCol[0] . "_" . $buildingRowCol[1],
                'buildingRow' => $buildingRowCol[0],
                'buildingColumn' => $buildingRowCol[1],
                'draftpool' => Draftpool::get(),
            ]
        );

        $this->stateStackNextState();
    }

    public function shouldSkipDiscardAgeCard() {
        return $this->shouldSkipDiscardAvailableCard();
    }

}