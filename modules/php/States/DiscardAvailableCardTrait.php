<?php

namespace SWD\States;

use SWD\Building;
use SWD\Draftpool;
use SWD\MythologyTokens;
use SWD\OfferingTokens;
use SWD\Player;

trait DiscardAvailableCardTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argDiscardAvailableCard() {
        $data = [];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        $data['round'] = (int)$this->getGameStateValue(self::VALUE_DISCARD_AVAILABLE_CARD_ROUND);
        $data['draftpool'] = Draftpool::get();

        if ($this->getGameStateValue(self::OPTION_PANTHEON)) {
            $data['mythologyTokensSituation'] = MythologyTokens::getSituation();
            $data['offeringTokensSituation'] = OfferingTokens::getSituation();
        }
        return $data;
    }

    public function enterStateDiscardAvailableCard() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionSkipDiscardAvailableCard() {
        $this->checkAction("actionSkipDiscardAvailableCard");

        if ($this->getGameStateValue(self::VALUE_DISCARD_AVAILABLE_CARD_ROUND) == 1) {
            throw new \BgaUserException( clienttranslate("You have to discard at least 1 available card.") );
        }

        $this->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} skipped the second possibility to place an available card in the discard pile'),
            [
                'player_name' => Player::getActive()->name,
            ]
        );

        $this->stateStackNextState();
    }

    public function actionDiscardAvailableCard($buildingId) {
        $this->checkAction("actionDiscardAvailableCard");

        $player = Player::getActive();
        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();
        $building->discard($player, false);

        $this->notifyAllPlayers(
            'discardBuilding',
            clienttranslate('${player_name} chose to place ${buildingName} in the discard pile'),
            [
                'i18n' => ['buildingName'],
                'player_name' => $player->name,
                'buildingName' => Building::get($buildingId)->name,
                'gain' => 0,
                'playerId' => $player->id,
                'buildingId' => $buildingId,
                'draftpool' => Draftpool::get(),
            ]
        );

        $this->incGameStateValue(self::VALUE_DISCARD_AVAILABLE_CARD_ROUND, 1);

        // Always reveal cards that are available thanks to this Conspiracy.
        Draftpool::revealCards(); // Pierre Berthelot: The Conspiracy card “Turn of events” is the exception that confirms the rule. Cards "released" by his action are returned immediately.

        $this->stateStackNextState();
    }

    public function shouldSkipDiscardAvailableCard() {
        if (Draftpool::countCardsInCurrentAge() == 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('There are no more cards available for ${player_name} to place in the discard pile'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );
            return true;
        }
        return false;
    }

    public function preEnterStateDiscardAvailableCard() {
        // Always reveal cards that are available thanks to this Conspiracy.
        Draftpool::revealCards(); // Pierre Berthelot: The Conspiracy card “Turn of events” is the exception that confirms the rule. Cards "released" by his action are returned immediately.
    }

}