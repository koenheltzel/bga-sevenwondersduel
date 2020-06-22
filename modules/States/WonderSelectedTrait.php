<?php

namespace SWD\States;

use SWD\Wonder;

trait WonderSelectedTrait {

    public function enterStateWonderSelected() {
        // Wonders are selected A-B-B-A, then B-A-A-B. Meaning we switch player when there are 3 or 1 cards left, and when there are 4 cards left (= second draft pool).
        $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        $cards = $this->wonderDeck->getCardsInLocation("selection{$wonderSelectionRound}");
        switch (count($cards)) {
            case 1:
            case 3:
            case 4:
                $this->activeNextPlayer();
                $this->gamestate->nextState( self::STATE_SELECT_WONDER_NAME );
                break;
            case 2:
                // Same player chooses again
                $this->gamestate->nextState( self::STATE_SELECT_WONDER_NAME );
                break;
            case 0:
                $this->gamestate->nextState( self::STATE_NEXT_AGE_NAME );
                break;

        }
    }
}