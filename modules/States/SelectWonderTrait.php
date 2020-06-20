<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Wonder;

trait SelectWonderTrait {

    public function stSelectWonder() {

    }

    public function wonderSelected($cardId){
        $this->checkAction(SevenWondersDuel::STATE_WONDER_SELECTED_NAME);

        $playerId = self::getCurrentPlayerId();

        $wonderSelection = $this->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION);
        $cards = SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$wonderSelection}");
        if (!array_key_exists($cardId, $cards)) {
            throw new \BgaUserException( self::_("The wonder you selected is not available.") );
        }
        $card = $cards[$cardId]; // Get before we re-set the $cards variable.
        SevenWondersDuel::get()->wonderDeck->moveCard($cardId, $playerId);

        // Renew the selection pool after the last wonder from the first pool was selected.
        if (count($cards) == 1 && $wonderSelection == 1) {
            // That was the last wonder.
            $this->setGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION, 2);
            $wonderSelection = 2;
        }
        $cards = SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$wonderSelection}");

        $this->notifyAllPlayers(
            'wonderSelected',
            clienttranslate('${playerName} selected wonder ${wonderName}.'),
            [
                'wonderSelection' => $cards,
                'wonderName' => Wonder::get($card['type_arg'])->name,
                'playerName' => $this->getCurrentPlayerName(),
                'playerColor' => $this->getCurrentPlayerColor(),
                'playerId' => $playerId
            ]
        );

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState( SevenWondersDuel::STATE_WONDER_SELECTED_NAME );
    }
}