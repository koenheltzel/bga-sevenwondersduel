<?php

namespace SWD\States;

use SWD\Conspiracies;
use SWD\Material;
use SWD\Player;
use SWD\Wonder;
use SWD\Wonders;

trait SelectWonderTrait {

    public function argSelectWonder() {
        $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $cards = Wonders::getDeckCardsSorted("selection{$wonderSelectionRound}");
        $data = [
            'round' => $wonderSelectionRound,
            'updateWonderSelection' => count($cards) == 4, // Update the wonder selection at the end of the first and second selection rounds (second to hide the block).
            'wonderSelection' => count($cards) == 4 ? Wonders::getDeckCardsSorted("selection{$wonderSelectionRound}") : null,
        ];

        // Because of Curia Julia, update the conspiracies situation (for the deck count)
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $data['conspiraciesSituation'] = Conspiracies::getSituation();
        }
        return $data;
    }
    public function enterStateSelectWonder() {
        $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $location = "selection{$wonderSelectionRound}";
        $cards = Wonders::getDeckCardsSorted($location);
        // Automatically select last wonder of the selection round.
        if (count($cards) == 1) {
            $card = $this->wonderDeck->getCardOnTop($location);
            $this->performActionSelectWonder(Player::getActive(), $card['id'], true);
        }
        else {
            $this->giveExtraTime($this->getActivePlayerId());
        }
    }

    public function actionSelectWonder($wonderId){
        $this->checkAction("actionSelectWonder");

        $this->performActionSelectWonder(Player::getActive(), $wonderId);
    }

    /**
     * Broken out of actionSelectWonder so it's callable by zombieTurn as well.
     * @param Player $player Has to be passed because zombieTurn can't use current player
     * @param $wonderId
     */
    private function performActionSelectWonder(Player $player, $wonderId, $automatic = false) {
        $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $cards = Wonders::getDeckCardsSorted("selection{$wonderSelectionRound}");
        $index = array_search($wonderId, array_column($cards, 'id'));
        if ($index === false) {
            throw new \BgaUserException( clienttranslate("The wonder you selected is not available.") );
        }
        unset($cards[$index]);

        $playerWonderCount = count($player->getWonders()->array) + 1;
        $this->wonderDeck->moveCard($wonderId, $player->id, $playerWonderCount);

        // Renew the selection pool after the last wonder from the first pool was selected.
        if (count($cards) == 0 && $wonderSelectionRound == 1) {
            $this->setGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND, 2);
        }

        $wonder = Wonder::get($wonderId);
        $this->notifyAllPlayers(
            'wonderSelected',
            $automatic ? clienttranslate('${player_name} gets the last wonder of the round “${wonderName}”') : clienttranslate('${player_name} selected wonder “${wonderName}”'),
            [
                'i18n' => ['wonderName'],
                'wonderName' => $wonder->name,
                'player_name' => $player->name,
                'playerId' => $player->id,
                'playerWonderCount' => $playerWonderCount, // Used to correctly position the wonder in the player area.
                'wonderId' => $wonder->id,
            ]
        );

        // Curia Julia: Conspire
        if ($wonderId == 13) {
            Material::get()->conspiracies->conspire();

            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} must Conspire (“${wonderName}”)'),
                [
                    'i18n' => ['wonderName'],
                    'wonderName' => $wonder->name,
                    'player_name' => $player->name,
                ]
            );

            $this->setGameStateValue(self::VALUE_CONSPIRE_RETURN_STATE, self::STATE_WONDER_SELECTED_ID);
            $this->gamestate->nextState( self::STATE_CONSPIRE_NAME );
        }
        // Knossos: Place Influence, then Move Influence
        elseif ($wonderId == 14) {
            $this->setStateStack([self::STATE_PLACE_INFLUENCE_NAME, self::STATE_WONDER_SELECTED_NAME]);
            $this->stateStackNextState();
        }
        else {
            $this->gamestate->nextState( self::STATE_WONDER_SELECTED_NAME );
        }
    }
}