<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Wonder;

trait WonderSelectedTrait {

    public function stWonderSelected() {
        // Wonders are selected A-B-B-A, then B-A-A-B. Meaning we switch player when there are 3 or 1 cards left, and when there are 4 cards left (= second draft pool).
        $wonderSelection = $this->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION);
        $cards = SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$wonderSelection}");
        switch (count($cards)) {
            case 1:
            case 3:
            case 4:
                $this->activeNextPlayer();
                $this->gamestate->nextState( SevenWondersDuel::STATE_SELECT_WONDER_NAME );
                break;
            case 2:
                // Same player chooses again
                $this->gamestate->nextState( SevenWondersDuel::STATE_SELECT_WONDER_NAME );
                break;
            case 0:
                $this->gamestate->nextState( SevenWondersDuel::STATE_NEXT_AGE_NAME );
                break;

        }
    }
}