<?php

namespace SWD;

use SevenWondersDuelAgora;

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
        $selectionRound = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        return [
            'selection' => self::getDeckCardsSorted("selection{$selectionRound}"),
            Player::me()->id => Player::me()->getWondersData(),
            Player::opponent()->id => Player::opponent()->getWondersData(),
        ];
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelAgora::get()->wonderDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return (int)$a['location_arg'] > (int)$b['location_arg'];});
        return $cards;
    }

    /**
     * @return Wonders
     */
    public function filterByConstructed($constructed = true) {
        $wonders = new Wonders();
        foreach ($this->array as $wonder) {
            if ($wonder->isConstructed() == $constructed) {
                $wonders[] = $wonder;
            }
        }
        return $wonders;
    }

}