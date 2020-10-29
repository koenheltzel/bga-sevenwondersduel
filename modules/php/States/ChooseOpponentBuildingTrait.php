<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Conspiracy;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonder;
use SWD\Wonders;

trait ChooseOpponentBuildingTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseOpponentBuilding() {
        $buildingType = 0;
        if ((int)$this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER) > 0) {
            $buildingType = $this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER) == 9 ? Building::TYPE_BROWN : Building::TYPE_GREY;
        }
        elseif ((int)$this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_CONSPIRACY) > 0) {
            $buildingType = $this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_CONSPIRACY) == 3 ? Building::TYPE_BLUE : Building::TYPE_YELLOW;
        }
        $data = [
            'buildingType' => $buildingType,
            'buildingTypeTranslatable' => Building::getBuildingTypeString($buildingType),
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        }
        return $data;
    }

    public function enterStateChooseOpponentBuilding() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseOpponentBuilding($buildingId) {
        $this->checkAction("actionChooseOpponentBuilding");

        $player = Player::getActive();
        $opponent = $player->getOpponent();
        if (!$opponent->hasBuilding($buildingId)) {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }
        // TODO check if selected building is actually of the type that is allowed to discard?

        SevenWondersDuel::get()->buildingDeck->insertCardOnExtremePosition($buildingId, 'discard', true);

        $building = Building::get($buildingId);
        if ($this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER) > 0) {
            $this->notifyAllPlayers(
                'opponentDiscardBuilding',
                clienttranslate('${player_name} discarded opponent\'s building “${buildingName}” (Wonder “${wonderName}”)'),
                [
                    'i18n' => ['buildingName', 'wonderName'],
                    'buildingName' => $building->name,
                    'wonderName' => Wonder::get($this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER))->name,
                    'player_name' => $player->name,
                    'playerId' => $player->id,
                    'buildingId' => $buildingId,
                ]
            );
            $this->setGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER, 0);
        }
        elseif ($this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_CONSPIRACY) > 0) {
            $this->notifyAllPlayers(
                'opponentDiscardBuilding',
                clienttranslate('${player_name} discarded opponent\'s building “${buildingName}” (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['buildingName', 'conspiracyName'],
                    'buildingName' => $building->name,
                    'conspiracyName' => Conspiracy::get($this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_CONSPIRACY))->name,
                    'player_name' => $player->name,
                    'playerId' => $player->id,
                    'buildingId' => $buildingId,
                ]
            );
            $this->setGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_CONSPIRACY, 0);
        }

        $building->deconstructEffects($opponent);

        $this->stateStackNextState(self::STATE_NEXT_PLAYER_TURN_NAME);

    }
}