<?php

namespace SWD;

use SevenWondersDuelAgora;

/**
 * @property ProgressToken[] $array
 */
class ProgressTokens extends Collection {

    public static function createByProgressTokenIds($progressTokenIds) {
        $progressTokens = new ProgressTokens();
        foreach($progressTokenIds as $progressTokenId) {
            $progressTokens[] = ProgressToken::get($progressTokenId);
        }
        return $progressTokens;
    }

    public function __construct($progressTokens = []) {
        $this->array = $progressTokens;
    }

    public static function getSituation() {
        return [
            'board' => self::getDeckCardsSorted("board"),
            Player::me()->id => self::getDeckCardsSorted(Player::me()->id),
            Player::opponent()->id => self::getDeckCardsSorted(Player::opponent()->id),
        ];
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelAgora::get()->progressTokenDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return (int)$a['location_arg'] > (int)$b['location_arg'];});
        return $cards;
    }

}