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

    public function getDeckCards($idMin = 0, $idMax = 999) {
        $cards = [];
        foreach ($this->array as $item) {
            if ($item->id >= $idMin && $item->id <= $idMax) {
                $cards[] = [
                    'type' => $item->type,
                    'type_arg' => $item->id,
                    'nbr' => 1
                ];
            }
        }
        return $cards;
    }

    public static function getSituation() {
        $age = (int)SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CURRENT_AGE);
        $deckCounts = [];
        for ($type = 1; $type <= 5; $type++) {
            $deckCounts[$type] = count(Divinities::getDeckCardsSorted("mythology{$type}"));
        }
        $spaces = [];
        for ($space = 1; $space <= 5; $space++) {
            $cards = Divinities::getDeckCardsSorted("space{$space}");
            if (count($cards) > 0) {
                $card = array_slice($cards, 0, 1)[0];
                $spaces[$space] = $age >= 2 ? (int)$card['id'] : (int)$card['type'];
            }
        }
        return [
            'age' => $age,
            'deckCounts' => $deckCounts,
            'spaces' => $spaces,
            Player::me()->id => Player::me()->getDivinitiesData(),
            Player::opponent()->id => Player::opponent()->getDivinitiesData(),
        ];
    }

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