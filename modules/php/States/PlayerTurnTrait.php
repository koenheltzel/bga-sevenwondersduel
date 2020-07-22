<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonder;
use SWD\Wonders;

trait PlayerTurnTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argPlayerTurn() {
        return [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
    }

    public function enterStatePlayerTurn() {

    }

    public function actionConstructBuilding($buildingId) {
        $this->checkAction("actionConstructBuilding");

        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();
        $payment = $building->construct(Player::me());

        if ($payment->selectProgressToken) {
            $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_NAME);
        }
        else {
            $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
        }
    }

    public function actionDiscardBuilding($buildingId) {
        $this->checkAction("actionDiscardBuilding");

        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();
        $discardGain = $building->discard(Player::me());

        $this->notifyAllPlayers(
            'discardBuilding',
            clienttranslate('${player_name} discarded building “${buildingName}” for ${gainDescription}.'),
            [
                'buildingName' => $building->name,
                'gain' => $discardGain,
                'gainDescription' => $discardGain . " " . COINS,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'buildingId' => $building->id,
            ]
        );

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);

    }

    public function actionConstructWonder($buildingId, $wonderId) {
        $this->checkAction("actionConstructWonder");

        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();

        $wonder = Wonder::get($wonderId);
        $wonder->checkWonderAvailable();
        $payment = $wonder->construct(Player::me(), $building);

        // Handle some special rewards that possibly require going to a separate state. If not move on to the Next Player Turn.
        switch ($wonder->id) {
            case 5: // Wonder The Mausoleum - Choose a discarded building and construct it for free.
                if (count(SevenWondersDuel::get()->wonderDeck->getCardsInLocation('discard')) > 0) {
                    $this->gamestate->nextState( self::STATE_CHOOSE_DISCARDED_BUILDING_NAME);
                }
                else {
                    $this->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name} can\'t choose a discarded card (Wonder “${wonderName}”)'),
                        [
                            'player_name' => $this->getCurrentPlayerName(),
                            'wonderName' => $wonder->name
                        ]
                    );
                    $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
                }
                break;
            case 6: // Wonder The Great Library - Randomly draw 3 Progress tokens from among those discarded at the beginning of the game. Choose one, play it, and return the other 2 to the box.
                $this->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} must choose a Progress token from the box (Wonder “${wonderName}”)'),
                    [
                        'player_name' => $this->getCurrentPlayerName(),
                        'wonderName' => $wonder->name,
                    ]
                );
                $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME);
                break;
            case 9: // Wonder The Statue of Zeus - Discard a brown building of your choice constructed by your opponent.
            case 12: // Wonder Circus Maximus - Discard a grey building of your choice constructed by your opponent.
                $this->setGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER, $wonderId);
                if (count(Player::opponent()->getBuildings()->filterByTypes([$wonderId == 9 ? Building::TYPE_BROWN : Building::TYPE_GREY])->array) > 0) {
                    $this->gamestate->nextState( self::STATE_CHOOSE_OPPONENT_BUILDING_NAME);
                }
                else {
                    $this->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name} can\'t choose a ${buildingType} card from the opponent (Wonder “${wonderName}”)'),
                        [
                            'player_name' => $this->getCurrentPlayerName(),
                            'buildingType' => $wonderId == 9 ? Building::TYPE_BROWN : Building::TYPE_GREY,
                            'wonderName' => $wonder->name
                        ]
                    );
                    $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
                }
                break;
            default:
                $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
                break;
        }
    }
}