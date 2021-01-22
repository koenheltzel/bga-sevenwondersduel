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

    public static function getDeckCounts() {
        $deckCounts = [];
        for ($type = 1; $type <= 5; $type++) {
            $deckCounts[$type] = count(Divinities::getDeckCardsSorted("mythology{$type}"));
        }
        return $deckCounts;
    }

    public static function getSituation() {
        $age = (int)SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CURRENT_AGE);
        $spaces = [];
        for ($space = 1; $space <= 6; $space++) {
            $cards = Divinities::getDeckCardsSorted("space{$space}");
            if (count($cards) > 0) {
                $card = array_slice($cards, 0, 1)[0];
                $data = [
                    'type' => (int)$card['type'],
                    'id' => ($age >= 2) ? (int)$card['id'] : 0,
                ];
                if ($age >= 2) {
                    foreach (Players::get() as $player) {
                        $payment = $player->getPaymentPlan(Divinity::get($card['id']));
                        $data['cost'][$player->id] = $payment->totalCost();
                        $data['payment'][$player->id] = $payment;
                        // Can the player afford to activate this Divinity?
                        $data['activatable'][$player->id] = (int)($player->getCoins(true) >= $payment->totalCost());
                    }
                }
                $spaces[$space] = $data;
            }
        }
        return [
            'age' => $age,
            'deckCounts' => self::getDeckCounts(),
            'spaces' => $spaces,
            Player::me()->id => Player::me()->getDivinitiesData(),
            Player::opponent()->id => Player::opponent()->getDivinitiesData(),
        ];
    }

    public static function typesInSelection() {
        $cards = SevenWondersDuelPantheon::get()->divinityDeck->getCardsInLocation('selection');
        $types = [];
        foreach($cards as $card) {
            $types[] = $card['type'];
        }
        return $types;
    }

    public static function enkiInSelection() {
        $cards = SevenWondersDuelPantheon::get()->divinityDeck->getCardsInLocation('selection');
        foreach($cards as $card) {
            if ($card['id'] == 1) {
                return true;
            }
        }
        return false;
    }

    public static function setEnkiProgressTokens() {
        SevenWondersDuelPantheon::get()->progressTokenDeck->shuffle('box');
        SevenWondersDuelPantheon::get()->progressTokenDeck->pickCardsForLocation(2, 'box', 'enki');
    }

    public static function resetEnkiProgressTokens() {
        SevenWondersDuelPantheon::get()->progressTokenDeck->moveAllCardsInLocation('enki', 'box');
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


}