<?php

namespace SWD;

use SevenWondersDuelPantheon;

/**
 * @property Divinity[] $array
 */
class Divinities extends Collection {

    public static function createByDivinityIds($divinityIds) {
        $divinities = new Divinities();
        foreach($divinityIds as $divinityId) {
            $divinities[] = Divinity::get($divinityId);
        }
        return $divinities;
    }

    public function __construct($divinities = []) {
        $this->array = $divinities;
    }

//    public static function getSituation() {
//        return [
//            'deckCount' => count(Divinities::getDeckCardsSorted('deck')),
//            Player::me()->id => Player::me()->getDivinitiesData(),
//            Player::opponent()->id => Player::opponent()->getDivinitiesData(),
//        ];
//    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelPantheon::get()->divinityDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return (int)$a['location_arg'] > (int)$b['location_arg'];});
        return $cards;
    }

    public static function getDeckCardsLocationArgAsKeys($location): array {
        $cards = SevenWondersDuelPantheon::get()->divinityDeck->getCardsInLocation($location);
        $result = [];
        foreach($cards as $card) {
            $result[$card['location_arg']] = $card;
        }
        return $result;
    }

    /**
     * @return Divinities
     */
//    public function filterByPrepared($prepared=true) {
//        $divinities = new Divinities();
//        foreach ($this->array as $divinity) {
//            if ($divinity->isPrepared() == $prepared) {
//                $divinities[] = $divinity;
//            }
//        }
//        return $divinities;
//    }

}