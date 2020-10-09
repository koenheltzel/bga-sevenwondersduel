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
        $situation = self::getDeckCardsSorted("board");
        foreach($situation as &$card) {
            if ((int)$card['type_arg'] == 0) {
                $card['id'] = 17;
            }
        }
        return $situation;
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelAgora::get()->decreeDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return (int)$a['location_arg'] > (int)$b['location_arg'];});
        return $cards;
    }

    /*
     * Array
     * (
     *     [3] => Array
     *         (
     *             [card_id] => 3
     *             [card_type] =>
     *             [card_type_arg] => 1
     *             [card_location] => board
     *             [card_location_arg] => 31
     *         )
     *
     * )
     */
    public static function getChamberDecrees(int $chamber): array {
        return self::getCollectionFromDB( "SELECT * FROM decree WHERE card_location = 'board' AND card_location_arg >= {$chamber}0 AND card_location_arg <= {$chamber}9 ORDER BY card_location_arg" );
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