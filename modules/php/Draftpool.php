<?php


namespace SWD;


use SevenWondersDuel;

class Draftpool extends Base
{

    private static $ages = [
        1 => [
            [5,7],
            [4,6,8],
            [3,5,7,9],
            [2,4,6,8,10],
            [1,3,5,7,9,11],
        ],
        2 => [
            [1,3,5,7,9,11],
            [2,4,6,8,10],
            [3,5,7,9],
            [4,6,8],
            [5,7],
        ],
        3 => [
            [5,7],
            [4,6,8],
            [3,5,7,9],
            [4,8],
            [3,5,7,9],
            [4,6,8],
            [5,7],
        ]
    ];
    private static $agoraAges = [
        1 => [
            [5,7,9],
            [4,6,8,10],
            [3,5,7,9,11],
            [2,4,6,8,10,12],
            [1,3,5,7,9,11,13],
        ],
        2 => [
            [1,3,5,7,9,11,13],
            [2,4,6,8,10,12],
            [3,5,7,9,11],
            [4,6,8,10],
            [5,7,9],
        ],
        3 => [
            [6,8],
            [5,7,9],
            [4,6,8,10],
            [3,5,7,9,11],
            [4,6,8,10],
            [5,7,9],
            [6,8],
        ]
    ];

    public static function buildingRow($buildingId) {
        $draftpool = Draftpool::get();
        foreach($draftpool['cards'] as $card) {
            if (isset($card['building']) && $card['building'] == $buildingId) {
                return $card['row'];
            }
        }
        return null;
    }

    public static function buildingAvailable($buildingId) {
        $ids = SevenWondersDuel::get()->getAvailableCardIds();
        return in_array((int)$buildingId, $ids);
    }

    public static function buildingRevealable($age, $buildingId) {
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

        $rows = self::getRows($age);
        $locationArg = (count($rows, COUNT_RECURSIVE) - count($rows)) - 1;
        $positionsFound = [];
        for($row_index = count($rows) - 1; $row_index >= 0; $row_index--) {
            $columns = $rows[$row_index];
            foreach($columns as $column) {
                if(isset($cards[$locationArg])) {
                    if ($cards[$locationArg]['id'] == $buildingId) {
                        // Last row is always available
                        $available = $row_index == count($rows) - 1;
                        // Determine if card is available because other cards have revealed it.
                        if (!$available && !in_array(($row_index + 2) . "_" . ($column - 1), $positionsFound) && !in_array(($row_index + 2) . "_" . ($column + 1), $positionsFound)) {
                            $available = true;
                        }
                        return $available;
                    }
                    else {
                        $positionsFound[] = ($row_index + 1) . "_" . $column;
                    }
                }
                $locationArg--;
            }
        }
        return false;
    }

    public static function revealCards() {
        return self::get(true);
    }

    public static function get($revealCards = false) {
        $actualAge = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
        $age = $actualAge;
        if ($actualAge == 0 && SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::OPTION_AGORA)) {
            $age = 1;
        }

        $draftpool = [
            'age' => $actualAge,
            'discardGain' => [
                Player::me()->id => Player::me()->calculateDiscardGain(),
                Player::opponent()->id => Player::opponent()->calculateDiscardGain(),
            ],
            'cards' => []
        ];

        if ($age > 0) { // Check needed for Agora Wonders which trigger game states outside of the wonder selection realm
            $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
            $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

            $availableCardIds = SevenWondersDuel::get()->getAvailableCardIds();
            if (!$revealCards && count($availableCardIds) == 0 && count($cards) > 0) {
                // Needed during the launch of this update when running games don't have the revealed cards value yet.
                $revealCards = true;
            }

            $rows = self::getRows($age);
            $locationArg = (count($rows, COUNT_RECURSIVE) - count($rows)) - 1;
            $positionsFound = [];
            for($row_index = count($rows) - 1; $row_index >= 0; $row_index--) {
                $columns = $rows[$row_index];
                $columns = array_reverse($columns); // Since we do array_unshift later we reverse here, so when updating the draftpool it happens from left to right.
                foreach($columns as $column) {
                    if(isset($cards[$locationArg])) {
                        $building = Building::get($cards[$locationArg]['id']);
                        $position = [
                            'row' => $row_index + 1,
                            'column' => $column,
                        ];
                        $positionsFound[] = ($row_index + 1) . "_" . $column;
                        $cardvisible = $row_index % 2 == 0;
                        // Determine if card is available because other cards have revealed it.
                        $available = Draftpool::buildingRevealable($age, $building->id, $revealCards);

                        if ($available) {
                            if ($revealCards && !in_array($building->id, $availableCardIds)) {
                                // Reveal this card
                                $availableCardIds[] = $building->id;
                            }
                            $available = in_array($building->id, $availableCardIds);
                        }
                        $cardvisible = ($cardvisible || $available);

                        if ($cardvisible) {
                            $position['available'] = $available;
                            $position['building'] = $building->id;
                            $position['spriteXY'] = $building->spriteXY;

                            if ($available) {
                                // Cost and payment plan for each player
                                $position['cost'] = [];
                                $position['discardGain'] = [];
                                $position['payment'] = [];
                                $position['hasLinkedBuilding'] = [];
                                $players = SevenWondersDuel::get()->loadPlayersBasicInfos();
                                $playerIds = array_keys($players);
                                foreach ($playerIds as $playerId) {
                                    $payment = Player::get($playerId)->getPaymentPlan($building);
                                    $position['cost'][$playerId] = $payment->totalCost();
                                    $position['payment'][$playerId] = $payment;
                                    $position['hasLinkedBuilding'][$playerId] = Player::get($playerId)->hasBuilding($building->linkedBuilding)
                                        || (Player::get($playerId)->hasDecree(15) && Player::get($playerId)->getOpponent()->hasBuilding($building->linkedBuilding));
                                }
                            }
                        }
                        else {
                            $position['spriteXY'] = Building::getBackSpriteXY($building->age);
                        }
                        array_unshift($draftpool['cards'], $position);
                    }
                    $locationArg--;
                }
            }

            if ($revealCards) {
                SevenWondersDuel::get()->setAvailableCardIds($availableCardIds);
            }
        }

        return $draftpool;
    }

    public static function countCardsInCurrentAge() {
        $age = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        return count($cards);
    }

    /**
     * @return array
     */
    public static function getRows($age): array {
        if ($age > 0) {
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::OPTION_AGORA)) {
                return self::$agoraAges[$age];
            }
            return self::$ages[$age];
        }
        else {
            // Return empty age structure with at least 1 row, which gets checked by getLastRowBuildings
            return [[]];
        }
    }

    public static function getLastRowBuildings() {
        $age = SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        $rows = self::getRows($age);

        $maxLocationArg = count($rows[0]) - 1;
        $buildingIds = [];
        foreach ($cards as $card) {
            if ((int)$card['location_arg'] <= $maxLocationArg) {
                $buildingIds[] = $card['id'];
            }
        }
        return Buildings::createByBuildingIds($buildingIds);
    }

}