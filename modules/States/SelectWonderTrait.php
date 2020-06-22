<?php

namespace SWD\States;

use SWD\Player;
use SWD\Wonder;

trait SelectWonderTrait {

    public function enterStateSelectWonder() {

    }

    public function actionSelectWonder($cardId){
        $this->checkAction("actionSelectWonder");

        $playerId = self::getCurrentPlayerId();

        $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $cards = $this->wonderDeck->getCardsInLocation("selection{$wonderSelectionRound}");
        if (!array_key_exists($cardId, $cards)) {
            throw new \BgaUserException( self::_("The wonder you selected is not available.") );
        }
        $card = $cards[$cardId]; // Get before we re-set the $cards variable.
        unset($cards[$cardId]);
        $this->wonderDeck->moveCard($cardId, $playerId);

        // Renew the selection pool after the last wonder from the first pool was selected.
        if (count($cards) == 0 && $wonderSelectionRound == 1) {
            $this->setGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND, 2);
            $wonderSelectionRound = 2;
        }

        $wonder = Wonder::get($card['type_arg']);
        $this->notifyAllPlayers(
            'wonderSelected',
            clienttranslate('${player_name} selected wonder ${wonderName}.'),
            [
                'wonderName' => $wonder->name,
                'player_name' => $this->getCurrentPlayerName(),
                'playerId' => $playerId,
                'playerWonderCount' => count(Player::me()->getWonders()->array), // Used to correctly position the wonder in the player area.
                'wonderId' => $wonder->id,
                'updateWonderSelection' => count($cards) == 0, // Update the wonder selection at the end of the first and second selection rounds (second to hide the block).
                'wonderSelection' => count($cards) == 0 ? $this->wonderDeck->getCardsInLocation("selection{$wonderSelectionRound}") : null,
            ]
        );

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState( self::STATE_WONDER_SELECTED_NAME );
    }
}