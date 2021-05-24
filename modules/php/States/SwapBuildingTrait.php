<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Building;
use SWD\Conspiracy;
use SWD\Player;

trait SwapBuildingTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argSwapBuilding() {
        $player = Player::getActive();
        $opponent = $player->getOpponent();

        $columns = [];
        $playerGreenCount = count($player->getBuildings()->filterByTypes([Building::TYPE_GREEN])->array);
        $playerBlueCount = count($player->getBuildings()->filterByTypes([Building::TYPE_BLUE])->array);
        $opponentGreenCount = count($opponent->getBuildings()->filterByTypes([Building::TYPE_GREEN])->array);
        $opponentBlueCount = count($opponent->getBuildings()->filterByTypes([Building::TYPE_BLUE])->array);
        if ($playerGreenCount > 0 && $opponentGreenCount > 0) $columns[] = Building::TYPE_GREEN;
        if ($playerBlueCount > 0 && $opponentBlueCount > 0) $columns[] = Building::TYPE_BLUE;

        $data = [
            'columns' => $columns
        ];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateSwapBuilding() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionSwapBuilding($opponentBuildingId, $meBuildingId) {
        $this->checkAction("actionSwapBuilding");

        $player = Player::getActive();
        $opponent = $player->getOpponent();

        $buildingOpponent = Building::get($opponentBuildingId);
        $buildingPlayer = Building::get($meBuildingId);

        if (!in_array($buildingOpponent->type, [Building::TYPE_GREEN, Building::TYPE_BLUE])
            || !in_array($buildingPlayer->type, [Building::TYPE_GREEN, Building::TYPE_BLUE])) {
            throw new \BgaUserException( clienttranslate("You are only allowed to choose a Green or Blue buildings.") );
        }
        if ($buildingOpponent->type <> $buildingPlayer->type) {
            // This is an error that we will actually throw during regular gameplay.
            throw new \BgaUserException( clienttranslate("Both buildings you select must be of the same color.") );
        }

        // Swap buildings in decks
        $cardOpponent = $this->buildingDeck->getCard($buildingOpponent->id);
        $cardPlayer = $this->buildingDeck->getCard($buildingPlayer->id);
        $this->buildingDeck->moveCard($buildingOpponent->id, $player->id, $cardPlayer['location_arg']);
        $this->buildingDeck->moveCard($buildingPlayer->id, $opponent->id, $cardOpponent['location_arg']);

        $this->notifyAllPlayers(
            'swapBuilding',
            clienttranslate('${player_name} takes Building “${buildingOpponentName}” and gives ${opponent_name} Building “${buildingPlayerName}” in exchange'),
            [
                'i18n' => ['buildingOpponentName', 'buildingPlayerName'],
                'player_name' => $player->name,
                'playerId' => $player->id,
                'opponent_name' => $player->getOpponent()->name,
                'buildingOpponentName' => $buildingOpponent->name,
                'buildingPlayerName' => $buildingPlayer->name,
                'buildingOpponentId' => $buildingOpponent->id,
                'buildingPlayerId' => $buildingPlayer->id,
            ]
        );

        $snakeTokenBuilding = Player::snakeTokenBuilding();
        if ($snakeTokenBuilding) {
            foreach([[$player, $buildingOpponent], [$opponent, $buildingPlayer]] as $pair) { // This may look wrong but the Snake token is on your opponent's building
                $tmpPlayer = $pair[0];
                $tmpBuilding = $pair[1];
                if ($tmpBuilding->id == $snakeTokenBuilding->id) {
                    $this->setGameStateValue(\SevenWondersDuel::VALUE_SNAKE_TOKEN_BUILDING_ID, 0);
                    $this->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name}\'s Snake token is discarded'),
                        [
                            'player_name' => $tmpPlayer->name
                        ]
                    );
                }
            }
        }

        $prependStates = [];

        $playerPoints = $buildingOpponent->victoryPoints - $buildingPlayer->victoryPoints;
        $opponentPoints = $buildingPlayer->victoryPoints - $buildingOpponent->victoryPoints;
        foreach([[$player, $buildingOpponent, $playerPoints], [$opponent, $buildingPlayer, $opponentPoints]] as $row) {
            /** @var Player $tmpPlayer */
            $tmpPlayer = $row[0];
            /** @var Building $tmpBuilding */
            $tmpBuilding = $row[1];
            $tmpPoints = $row[2];
            $tmpPlayer->increaseScore($tmpPoints, $tmpBuilding->getScoreCategory());
            if ($tmpPoints > 0) {
                $this->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} scores ${points} victory points'),
                    [
                        'player_name' => $tmpPlayer->name,
                        'points' => $tmpPoints,
                    ]
                );
            }
            if ($tmpPoints < 0) {
                // Because it's a swap we don't use the deconstruct function here to prevent a flood of notifications.
                $this->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} loses ${points} victory points'),
                    [
                        'player_name' => $tmpPlayer->name,
                        'points' => abs($tmpPoints),
                    ]
                );
            }

            // Active player and or opponent can have completed a science symbol pair. Let active player go first, but both get to do the action before determining immediate win.
            if ($tmpPlayer->hasScientificSymbolPair($tmpBuilding->scientificSymbol) && $tmpBuilding->gatheredSciencePairNotification($tmpPlayer)) {
                if ($tmpPlayer == Player::me()) {
                    $prependStates = array_merge([self::STATE_CHOOSE_PROGRESS_TOKEN_NAME], $prependStates);
                }
                else {
                    $prependStates = array_merge($prependStates, [self::STATE_PLAYER_SWITCH_NAME, self::STATE_CHOOSE_PROGRESS_TOKEN_NAME, self::STATE_PLAYER_SWITCH_NAME]);
                }
            }
        }

        $this->prependStateStack($prependStates);

        $this->stateStackNextState();
    }

    public function shouldSkipSwapBuilding() {
        $player = Player::me();
        $opponent = Player::opponent();
        $playerGreenCount = count($player->getBuildings()->filterByTypes([Building::TYPE_GREEN])->array);
        $playerBlueCount = count($player->getBuildings()->filterByTypes([Building::TYPE_BLUE])->array);
        $opponentGreenCount = count($opponent->getBuildings()->filterByTypes([Building::TYPE_GREEN])->array);
        $opponentBlueCount = count($opponent->getBuildings()->filterByTypes([Building::TYPE_BLUE])->array);
        if (($playerGreenCount == 0 || $opponentGreenCount == 0) && ($playerBlueCount == 0 || $opponentBlueCount == 0)) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} and ${opponent_name} can\'t swap a Green or Blue card of the same color (Conspiracy “${conspiracyName}”)'),
                [
                    'i18n' => ['conspiracyName'],
                    'player_name' => $player->name,
                    'opponent_name' => $opponent->name,
                    'conspiracyName' => Conspiracy::get(14)->name,
                ]
            );
            return true;
        }
        return false;
    }

}