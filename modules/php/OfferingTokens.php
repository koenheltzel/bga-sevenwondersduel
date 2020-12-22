<?php

namespace SWD;

use SevenWondersDuelPantheon;

/**
 * @property OfferingToken[] $array
 */
class OfferingTokens extends Collection {

    public static function createByOfferingTokenIds($offeringTokenIds) {
        $offeringTokens = new OfferingTokens();
        foreach($offeringTokenIds as $offeringTokenId) {
            $offeringTokens[] = OfferingToken::get($offeringTokenId);
        }
        return $offeringTokens;
    }

    public function __construct($offeringTokens = []) {
        $this->array = $offeringTokens;
    }

    public static function getSituation() {
        $age = (int)SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CURRENT_AGE);
        if ($age == 0 && SevenWondersDuelPantheon::get()->expansionActive()) {
            $age = 1;
        }

        $board = [];
        if ($age == 2) {
            $cards = self::getDeckCardsSorted('board');
            foreach ($cards as $card) {
                $card['rowCol'] = Draftpool::getTokenRowCol($age, $card['location_arg']);
                unset($card['location_arg']);
                $board[] = $card;
            }
        }

        return [
            'board' => $board,
            Player::me()->id => Player::me()->getOfferingTokenDeckCards(),
            Player::opponent()->id => Player::opponent()->getOfferingTokenDeckCards(),
        ];
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelPantheon::get()->offeringTokenDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return (int)$a['location_arg'] > (int)$b['location_arg'];});
        return $cards;
    }

    public static function getDeckCardsLocationArgAsKeys($location): array {
        $cards = SevenWondersDuelPantheon::get()->offeringTokenDeck->getCardsInLocation($location);
        $result = [];
        foreach($cards as $card) {
            $result[$card['location_arg']] = $card;
        }
        return $result;
    }

}