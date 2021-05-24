<?php

namespace SWD;

use SevenWondersDuel;

/**
 * @property MythologyToken[] $array
 */
class MythologyTokens extends Collection {

    public static function createByMythologyTokenIds($mythologyTokenIds) {
        $mythologyTokens = new MythologyTokens();
        foreach($mythologyTokenIds as $mythologyTokenId) {
            $mythologyTokens[] = MythologyToken::get($mythologyTokenId);
        }
        return $mythologyTokens;
    }

    public function __construct($mythologyTokens = []) {
        $this->array = $mythologyTokens;
    }

    public static function getBoardTokens() {
        $age = (int)SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
        if ($age == 0 && SevenWondersDuel::get()->expansionActive()) {
            $age = 1;
        }

        $board = [];
        if ($age == 1) {
            $cards = self::getDeckCardsSorted('board');
            foreach ($cards as $card) {
                $card['rowCol'] = Draftpool::getTokenRowCol($age, $card['location_arg']);
                unset($card['location_arg']);
                $board[] = $card;
            }
        }
        return $board;
    }

    public static function getSituation() {
        return [
            'board' => self::getBoardTokens(),
            Player::me()->id => Player::me()->getMythologyTokenDeckCards(),
            Player::opponent()->id => Player::opponent()->getMythologyTokenDeckCards(),
        ];
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuel::get()->mythologyTokenDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return (int)$a['location_arg'] > (int)$b['location_arg'];});
        return $cards;
    }

    public static function getDeckCardsLocationArgAsKeys($location): array {
        $cards = SevenWondersDuel::get()->mythologyTokenDeck->getCardsInLocation($location);
        $result = [];
        foreach($cards as $card) {
            $result[$card['location_arg']] = $card;
        }
        return $result;
    }

}