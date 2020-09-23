<?php

namespace SWD;

use SevenWondersDuelAgora;

/**
 * @property Conspiracy[] $array
 */
class Conspiracies extends Collection {

    public static function createByConspiracyIds($conspiracyIds) {
        $conspiracies = new Conspiracies();
        foreach($conspiracyIds as $conspiracyId) {
            $conspiracies[] = Conspiracy::get($conspiracyId);
        }
        return $conspiracies;
    }

    public function __construct($conspiracies = []) {
        $this->array = $conspiracies;
    }

    public static function getSituation() {
        return [
            'deckCount' => count(Conspiracies::getDeckCardsSorted('deck')),
            Player::me()->id => Player::me()->getConspiraciesData(),
            Player::opponent()->id => Player::opponent()->getConspiraciesData(),
        ];
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelAgora::get()->conspiracyDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return strcmp($a['location_arg'], $b['location_arg']);});
        return $cards;
    }

    public static function getDeckCardsLocationArgAsKeys($location): array {
        $cards = SevenWondersDuelAgora::get()->conspiracyDeck->getCardsInLocation($location);
        $result = [];
        foreach($cards as $card) {
            $result[$card['location_arg']] = $card;
        }
        return $result;
    }

    public function conspire() {
        // Do it like this so the top most card is displayed first (left). This can be relevant information because it could be
        // the card the opponent did not choose last round and put back on top of the deck.
        SevenWondersDuelAgora::get()->conspiracyDeck->pickCardsForLocation(1, 'deck', 'conspire', 0);
        SevenWondersDuelAgora::get()->conspiracyDeck->pickCardsForLocation(1, 'deck', 'conspire', 1);
    }
    public function conspireChoice($conspiracyId) {
        SevenWondersDuelAgora::get()->conspiracyDeck->insertCardOnExtremePosition($conspiracyId, Player::getActive()->id, true);
    }
    public function conspireRemnantPosition($top = true) {
        $cards = SevenWondersDuelAgora::get()->conspiracyDeck->getCardsInLocation('conspire');
        $card = array_shift($cards);
        SevenWondersDuelAgora::get()->conspiracyDeck->insertCardOnExtremePosition($card['id'], 'deck', $top);
    }

    /**
     * @return Conspiracies
     */
    public function filterByConstructed() {
        $conspiracies = new Conspiracies();
        foreach ($this->array as $conspiracy) {
            if ($conspiracy->isConstructed()) {
                $conspiracies[] = $conspiracy;
            }
        }
        return $conspiracies;
    }

}