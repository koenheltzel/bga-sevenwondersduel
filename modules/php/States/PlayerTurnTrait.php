<?php

namespace SWD\States;

use SevenWondersDuelAgora;
use SWD\Building;
use SWD\Conspiracies;
use SWD\Conspiracy;
use SWD\Decree;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Senate;
use SWD\Wonder;
use SWD\Wonders;

trait PlayerTurnTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argPlayerTurn() {
        $data = [
            'ageRoman' => ageRoman($this->getGameStateValue(self::VALUE_CURRENT_AGE)),
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];

        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $this->addConspiraciesSituation($data);
            $data['senateSituation'] = Senate::getSituation();
        }
        return $data;
    }

    public function enterStatePlayerTurn() {
        $this->giveExtraTime($this->getActivePlayerId());

        $this->incStat(1, self::STAT_TURNS_NUMBER);
        $this->incStat(1, self::STAT_TURNS_NUMBER, $this->getActivePlayerId());
    }

    public function actionConstructBuilding($buildingId) {
        $this->checkAction("actionConstructBuilding");

        $player = Player::getActive();
        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();
        $payment = $building->construct($player);

        $this->incStat(1, self::STAT_BUILDINGS_CONSTRUCTED, $player->id);
        if (count($payment->steps) == 1 && $payment->steps[0]->resource == "linked") {
            $this->incStat(1, self::STAT_CHAINED_CONSTRUCTIONS, $player->id);
        }

        if (count($payment->militarySenateActions) > 0) {
            $this->setStateStack(array_merge($payment->militarySenateActions, [self::STATE_NEXT_PLAYER_TURN_NAME]));
            $this->stateStackNextState();
        }
        elseif ($payment->selectProgressToken) {
            $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_NAME);
        }
        elseif ($building->subType == Building::SUBTYPE_CONSPIRATOR) {
            $this->gamestate->nextState( self::STATE_CHOOSE_CONSPIRATOR_ACTION_NAME);
        }
        elseif ($building->subType == Building::SUBTYPE_POLITICIAN) {
            $this->setGameStateValue(self::VALUE_SENATE_ACTIONS_SECTION, $building->senateSection);
            $this->setGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT, $player->getSenateActionsCount());

            if ($player->hasDecree(12)) {
                $this->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} may perform ${actionsCount} Senate actions (+2 because he controls the Decree in Chamber ${chamber})'),
                    [
                        'player_name' => $player->name,
                        'actionsCount' => $this->getGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT),
                        'chamber' => Decree::get(12)->getChamber(),
                    ]
                );
            }
            else {
                $this->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} may perform ${actionsCount} Senate actions'),
                    [
                        'player_name' => $player->name,
                        'actionsCount' => $this->getGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT),
                    ]
                );
            }

            $this->setStateStack([self::STATE_SENATE_ACTIONS_NAME, self::STATE_NEXT_PLAYER_TURN_NAME]);
            $this->stateStackNextState();
        }
        else {
            $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
        }
    }

    public function actionDiscardBuilding($buildingId) {
        $this->checkAction("actionDiscardBuilding");

        $player = Player::getActive();
        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();
        $discardGain = $building->discard($player);

        $this->incStat(1, self::STAT_DISCARDED_CARDS, $player->id);

        if ($player->hasDecree(13)) {
            $this->notifyAllPlayers(
                'discardBuilding',
                clienttranslate('${player_name} discarded building “${buildingName}” for ${gain} coins (+2 because he controls the Decree in Chamber ${chamber})'),
                [
                    'i18n' => ['buildingName'],
                    'buildingName' => $building->name,
                    'gain' => $discardGain,
                    'player_name' => $player->name,
                    'playerId' => $player->id,
                    'buildingId' => $building->id,
                    'chamber' => Decree::get(13)->getChamber(),
                ]
            );
        }
        else {
            $this->notifyAllPlayers(
                'discardBuilding',
                clienttranslate('${player_name} discarded building “${buildingName}” for ${gain} coins'),
                [
                    'i18n' => ['buildingName'],
                    'buildingName' => $building->name,
                    'gain' => $discardGain,
                    'player_name' => $player->name,
                    'playerId' => $player->id,
                    'buildingId' => $building->id,
                ]
            );
        }


        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);

    }

    public function actionConstructWonder($buildingId, $wonderId) {
        $this->checkAction("actionConstructWonder");

        $player = Player::getActive();
        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();


        if (!in_array($wonderId, $player->getWonderIds())) {
            throw new \BgaUserException( clienttranslate("The wonder you selected is not available.") );
        }

        $wonder = Wonder::get($wonderId);
        if ($wonder->isConstructed()) {
            throw new \BgaUserException( clienttranslate("The wonder you selected has already been constructed.") );
        }

        $payment = $wonder->construct($player, $building);

        $this->incStat(1, self::STAT_WONDERS_CONSTRUCTED, $player->id);

        if ($this->checkImmediateVictory()) {
            // Specific for Wonders Circus Maximus & The Statue of Zeus we check if a immediate victory (military in this case) is the case.
            // In that case, we don't have to enter the CHOOSE_OPPONENT_BUILDING state.
            $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
        }
        elseif (count($payment->militarySenateActions) > 0) {
            $this->setStateStack(array_merge($payment->militarySenateActions, [self::STATE_NEXT_PLAYER_TURN_NAME]));
            $this->stateStackNextState();
        }
        elseif (count($wonder->actionStates) > 0) {
            $this->setStateStack(array_merge($wonder->actionStates, [self::STATE_NEXT_PLAYER_TURN_NAME]));
            $this->stateStackNextState();
        }
        else {
            // Handle some special rewards that possibly require going to a separate state. If not move on to the Next Player Turn.
            switch ($wonder->id) {
                case 5: // Wonder The Mausoleum - Choose a discarded building and construct it for free.
                    if (count(SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation('discard')) > 0) {
                        $this->gamestate->nextState( self::STATE_CHOOSE_DISCARDED_BUILDING_NAME);
                    }
                    else {
                        $this->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} can\'t choose a discarded card (Wonder “${wonderName}”)'),
                            [
                                'i18n' => ['wonderName'],
                                'player_name' => $player->name,
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
                            'i18n' => ['wonderName'],
                            'player_name' => $player->name,
                            'wonderName' => $wonder->name,
                        ]
                    );

                    // Would like to this in enterStateChooseProgressTokenFromBox, but aparently argChooseProgressTokenFromBox can be called before, so we need to do it now.
                    $this->progressTokenDeck->pickCardsForLocation(3, 'box', 'selection'); // Select 3 progress tokens for Wonder The Great Library.
                    $this->progressTokenDeck->shuffle('selection'); // Ensures we have defined card_location_arg
                    $this->gamestate->nextState( self::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME);
                    break;
                case 9: // Wonder The Statue of Zeus - Discard a brown building of your choice constructed by your opponent.
                case 12: // Wonder Circus Maximus - Discard a grey building of your choice constructed by your opponent.
                    if (count(Player::opponent()->getBuildings()->filterByTypes([$wonderId == 9 ? Building::TYPE_BROWN : Building::TYPE_GREY])->array) > 0) {
                        $this->setGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER, $wonderId);
                        $this->gamestate->nextState( self::STATE_CHOOSE_OPPONENT_BUILDING_NAME);
                    }
                    else {
                        $this->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} can\'t choose a ${buildingType} card from the opponent (Wonder “${wonderName}”)'),
                            [
                                'i18n' => ['wonderName', 'buildingType'],
                                'player_name' => $player->name,
                                'buildingType' => $wonderId == 9 ? clienttranslate('brown') : clienttranslate('grey'),
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

    public function actionPrepareConspiracy($buildingId, $conspiracyId) {
        $this->checkAction("actionPrepareConspiracy");

        $player = Player::getActive();
        $building = Building::get($buildingId);
        $building->checkBuildingAvailable();

        if (!in_array($conspiracyId, $player->getConspiracyIds())) {
            throw new \BgaUserException( clienttranslate("The Conspiracy you selected is not available.") );
        }

        $conspiracy = Conspiracy::get($conspiracyId);
        if ($conspiracy->isPrepared()) {
            throw new \BgaUserException( clienttranslate("The Conspiracy you selected has already been prepared.") );
        }
        if ($conspiracy->isTriggered()) {
            throw new \BgaUserException( clienttranslate("The Conspiracy you selected has already been triggered.") );
        }

        $conspiracy->prepare($player, $building);

        $this->incStat(1, self::STAT_CONSPIRACIES_PREPARED, $player->id);

        $this->gamestate->nextState( self::STATE_NEXT_PLAYER_TURN_NAME);
    }

    public function actionTriggerConspiracy($conspiracyId) {
        $this->checkAction("actionTriggerConspiracy");

        $player = Player::getActive();

        if (!in_array($conspiracyId, $player->getConspiracyIds())) {
            throw new \BgaUserException( clienttranslate("The Conspiracy you selected is not available.") );
        }

        $conspiracy = Conspiracy::get($conspiracyId);
        if (!$conspiracy->isPrepared() && $this->gamestate->state()['name'] != self::STATE_TRIGGER_UNPREPARED_CONSPIRACY_NAME) {
            throw new \BgaUserException( clienttranslate("The Conspiracy you selected has not been prepared yet.") );
        }
        if ($conspiracy->isTriggered()) {
            throw new \BgaUserException( clienttranslate("The Conspiracy you selected has already been triggered.") );
        }

        $payment = $conspiracy->trigger($player);

        $this->incStat(1, self::STAT_CONSPIRACIES_TRIGGERED, $player->id);

        switch($conspiracyId) {
            case 3:
            case 4:
                if (count(Player::opponent()->getBuildings()->filterByTypes([$conspiracyId == 3 ? Building::TYPE_BLUE : Building::TYPE_YELLOW])->array) > 0) {
                    $this->setGameStateValue(self::VALUE_DISCARD_OPPONENT_BUILDING_CONSPIRACY, $conspiracy->id);

                    $this->setStateStack(array_merge($conspiracy->actionStates, [self::STATE_PLAYER_TURN_NAME]));
                    $this->stateStackNextState();
                }
                else {
                    $this->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name} can\'t choose a ${buildingType} card from the opponent (Conspiracy “${conspiracyName}”)'),
                        [
                            'i18n' => ['conspiracyName', 'buildingType'],
                            'player_name' => $player->name,
                            'buildingType' => $conspiracyId == 3 ? clienttranslate('blue') : clienttranslate('yellow'),
                            'conspiracyName' => $conspiracy->name
                        ]
                    );
                    array_shift($conspiracy->actionStates); // Remove discard opponent state from stack.
                    $this->setStateStack(array_merge($conspiracy->actionStates, [self::STATE_PLAYER_TURN_NAME]));
                    $this->stateStackNextState();
                }
                break;
            default:
                $this->setStateStack(array_merge($payment->militarySenateActions, $conspiracy->actionStates, [self::STATE_PLAYER_TURN_NAME]));
                $this->stateStackNextState();
                break;
        }
    }
}