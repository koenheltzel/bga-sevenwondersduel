<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
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
        return [
            'buildingType' => $this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER) == 9 ? Building::TYPE_BROWN : Building::TYPE_GREY,
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStateChooseOpponentBuilding() {

    }

    public function actionChooseOpponentBuilding($buildingId) {
        $this->checkAction("actionChooseOpponentBuilding");

        if (!Player::opponent()->hasBuilding($buildingId)) {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }

        SevenWondersDuel::get()->buildingDeck->insertCardOnExtremePosition($buildingId, 'discard', true);

        $this->notifyAllPlayers(
            'opponentDiscardBuilding',
            clienttranslate('${player_name} discarded opponent\'s building “${buildingName}” (Wonder “${wonderName}”)'),
            [
                'i18n' => ['buildingName', 'wonderName'],
                'buildingName' => Building::get($buildingId)->name,
                'wonderName' => Wonder::get($this->getGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER))->name,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $buildingId,
            ]
        );

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);

    }
}