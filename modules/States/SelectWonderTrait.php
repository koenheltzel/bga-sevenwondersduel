<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Player;
use SWD\Wonder;

trait SelectWonderTrait {

    public function stSelectWonder() {

    }

    public function wonderSelected($cardId){
        $this->checkAction(SevenWondersDuel::STATE_WONDER_SELECTED_NAME);

        $playerId = self::getCurrentPlayerId();

        $wonderSelectionRound = $this->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $cards = SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$wonderSelectionRound}");
        if (!array_key_exists($cardId, $cards)) {
            throw new \BgaUserException( self::_("The wonder you selected is not available.") );
        }
        $card = $cards[$cardId]; // Get before we re-set the $cards variable.
        unset($cards[$cardId]);
        SevenWondersDuel::get()->wonderDeck->moveCard($cardId, $playerId);

        // Renew the selection pool after the last wonder from the first pool was selected.
        if (count($cards) == 0 && $wonderSelectionRound == 1) {
            $this->setGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION_ROUND, 2);
            $wonderSelectionRound = 2;
        }

        $wonder = Wonder::get($card['type_arg']);
        $this->notifyAllPlayers(
            'wonderSelected',
            clienttranslate('${playerName} selected wonder ${wonderName}.'),
            [
                'wonderName' => $wonder->name,
                'playerName' => $this->getCurrentPlayerName(),
                'playerId' => $playerId,
                'playerWonderCount' => count(Player::me()->getWonders()->array), // Used to correctly position the wonder in the player area.
                'wonderId' => $wonder->id,
                'updateWonderSelection' => count($cards) == 0, // Update the wonder selection at the end of the first and second selection rounds (second to hide the block).
                'wonderSelection' => count($cards) == 0 ? SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$wonderSelectionRound}") : null,
            ]
        );

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState( SevenWondersDuel::STATE_WONDER_SELECTED_NAME );
    }
}