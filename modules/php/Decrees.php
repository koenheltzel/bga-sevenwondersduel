<?php

namespace SWD;

use SevenWondersDuelAgora;

/**
 * @property Decree[] $array
 */
class Decrees extends Collection {

    public static function createByDecreeIds($decreeIds) {
        $decrees = new Decrees();
        foreach($decreeIds as $decreeId) {
            $decrees[] = Decree::get($decreeId);
        }
        return $decrees;
    }

    public function __construct($decrees = []) {
        $this->array = $decrees;
    }

    public static function getSituation() {
        return self::getDeckCardsSorted("board");
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelAgora::get()->decreeDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return strcmp($a['location_arg'], $b['location_arg']);});
        return $cards;
    }

    /**
     * @return Decrees
     */
//    public function filterByConstructed() {
//        $decrees = new Decrees();
//        foreach ($this->array as $decree) {
//            if ($decree->isConstructed()) {
//                $decrees[] = $decree;
//            }
//        }
//        return $decrees;
//    }

}