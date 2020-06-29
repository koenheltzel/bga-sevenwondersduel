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
        $selectionRound = SevenWondersDuel::get()->getWonderSelectionRound();
        return [
            'selection' => SevenWondersDuel::get()->wonderDeck->getCardsInLocation("selection{$selectionRound}"),
            Player::me()->id => Player::me()->getWondersData(),
            Player::opponent()->id => Player::opponent()->getWondersData(),
        ];
    }

}