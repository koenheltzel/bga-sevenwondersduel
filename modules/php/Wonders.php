<?php

namespace SWD;

use SevenWondersDuel;

/**
 * @property Wonder[] $array
 */
class Wonders extends Collection {

    public static function createByWonderIds($wonderIds) {
        $wonders = new Wonders();
        foreach($wonderIds as $wonderId) {
            $wonders[] = Wonder::get($wonderId);
        }
        return $wonders;
    }

    public function __construct($wonders = []) {
        $this->array = $wonders;
    }

    public static function getSituation() {
        $selectionRound = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        return [
            'selection' => self::getDeckCardsSorted("selection{$selectionRound}"),
            Player::me()->id => Player::me()->getWondersData(),
            Player::opponent()->id => Player::opponent()->getWondersData(),
        ];
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuel::get()->wonderDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return strcmp($a['location_arg'], $b['location_arg']);});
        return $cards;
    }

}