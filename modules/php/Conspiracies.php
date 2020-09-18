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
        $selectionRound = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CURRENT_WONDER_SELECTION_ROUND);
        return [
            'selection' => self::getDeckCardsSorted("selection{$selectionRound}"),
            Player::me()->id => Player::me()->getConspiraciesData(),
            Player::opponent()->id => Player::opponent()->getConspiraciesData(),
        ];
    }

    public static function getDeckCardsSorted($location): array {
        $cards = SevenWondersDuelAgora::get()->conspiracyDeck->getCardsInLocation($location);
        usort($cards, function($a, $b) {return strcmp($a['location_arg'], $b['location_arg']);});
        return $cards;
    }

    public function conspire() {
        SevenWondersDuelAgora::get()->conspiracyDeck->pickCardsForLocation(2, 'deck', 'conspire');
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