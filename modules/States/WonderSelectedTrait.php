<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Wonder;

trait WonderSelectedTrait {

    public function wonderSelected($cardId){
        $this->checkAction(SevenWondersDuel::STATE_WONDER_SELECTED_NAME);

        $playerId = self::getCurrentPlayerId();

        $wonderSelection = $this->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION);
        $cards = SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$wonderSelection}");
        if (!array_key_exists($cardId, $cards)) {
            throw new \BgaUserException( self::_("The wonder you selected is not available.") );
        }
        SevenWondersDuel::get()->wonderDeck->moveCard($cardId, $playerId);

        $this->notifyAllPlayers(
            'wonderSelected',
            clienttranslate('${playerName} selected wonder ${wonderName}.'),
            [
//                'boards' => Boards::get()->boards,
                'wonderName' => Wonder::get($cards[$cardId]['type_arg'])->name,
                'playerName' => $this->getCurrentPlayerName(),
                'playerColor' => $this->getCurrentPlayerColor(),
                'playerId' => $playerId
            ]
        );

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState( SevenWondersDuel::STATE_WONDER_SELECTED_NAME );
    }

    public function stWonderSelected() {
        // Wonders are selected A-B-B-A, then B-A-A-B. Meaning we switch player after selections 1, 3 and 4, then repeat.
        $wonderSelection = $this->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION);
        $wonders = SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$wonderSelection}");
        if (count($wonders) == 3 || count($wonders) == 1) {
            $this->activeNextPlayer();
            $this->gamestate->nextState( SevenWondersDuel::STATE_SELECT_WONDER_NAME );
        }
        elseif(count($wonders) == 2) {
            // Same player chooses again
            $this->gamestate->nextState( SevenWondersDuel::STATE_SELECT_WONDER_NAME );
        }
        elseif(count($wonders) == 0) {
            if ($wonderSelection == 1) {
                $this->setGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION, 2);
                $this->activeNextPlayer();
                $this->gamestate->nextState( SevenWondersDuel::STATE_SELECT_WONDER_NAME );
            }
            else {
                $this->activeNextPlayer();
                $this->gamestate->nextState( SevenWondersDuel::STATE_NEXT_AGE_NAME );
            }
        }
    }
}