<?php

namespace SWD\States;

use SWD\Player;
use SWD\Wonder;
use SWD\Wonders;

trait SelectWonderTrait {

    public function argSelectWonder() {
        $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $cards = Wonders::getDeckCardsSorted("selection{$wonderSelectionRound}");
        return [
            'round' => $wonderSelectionRound,
            'updateWonderSelection' => count($cards) == 4, // Update the wonder selection at the end of the first and second selection rounds (second to hide the block).
            'wonderSelection' => count($cards) == 4 ? Wonders::getDeckCardsSorted("selection{$wonderSelectionRound}") : null,
        ];
    }
    public function enterStateSelectWonder() {

    }

    public function actionSelectWonder($wonderId){
        $this->checkAction("actionSelectWonder");

        $playerId = self::getCurrentPlayerId();

        $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $cards = Wonders::getDeckCardsSorted("selection{$wonderSelectionRound}");
        $index = array_search($wonderId, array_column($cards, 'id'));
        if ($index === false) {
            throw new \BgaUserException( clienttranslate("The wonder you selected is not available.") );
        }
        unset($cards[$index]);

        $playerWonderCount = count(Player::me()->getWonders()->array) + 1;
        $this->wonderDeck->moveCard($wonderId, $playerId, $playerWonderCount);

        // Renew the selection pool after the last wonder from the first pool was selected.
        if (count($cards) == 0 && $wonderSelectionRound == 1) {
            $this->setGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND, 2);
        }

        $wonder = Wonder::get($wonderId);
        $this->notifyAllPlayers(
            'wonderSelected',
            clienttranslate('${player_name} selected wonder “${wonderName}”'),
            [
                'i18n' => ['wonderName'],
                'wonderName' => $wonder->name,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => $playerId,
                'playerWonderCount' => $playerWonderCount, // Used to correctly position the wonder in the player area.
                'wonderId' => $wonder->id,
            ]
        );

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState( self::STATE_WONDER_SELECTED_NAME );
    }
}