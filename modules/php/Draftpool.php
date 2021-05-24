<?php


namespace SWD;


use SevenWondersDuelPantheon;

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

    private static $pantheonAgeTokens = [
        1 => [2, 4, 9, 11, 13],
        2 => [6, 8, 10],
        3 => []
    ];
    private static $agoraPantheonAgeTokens = [
        1 => [4, 6, 12, 14, 17],
        2 => [7, 9, 12],
        3 => []
    ];

    public static function getTokenRowCol($age, $location) {
        $tokensArray = [];
        $ageArray = [];
        if (SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::OPTION_AGORA)) {
            $tokensArray = self::$agoraPantheonAgeTokens;
            $ageArray = self::$agoraAges;
        }
        else {
            $tokensArray = self::$pantheonAgeTokens;
            $ageArray = self::$ages;
        }
        $buildingIndex = $tokensArray[$age][$location];
        foreach($ageArray[$age] as $rowIndex => $row) {
            if ($buildingIndex < count($row)) {
                return [$rowIndex + 1, $row[$buildingIndex]];
            }
            else {
                $buildingIndex -= count($row);
            }
        }
    }

    public static function getBuildingRowCol($age, $buildingId) {
        $cards = SevenWondersDuelPantheon::get()->buildingDeck->getCardsInLocation("age{$age}");
        $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

        $rows = self::getRows($age);
        $locationArg = (count($rows, COUNT_RECURSIVE) - count($rows)) - 1;
        for($row_index = count($rows) - 1; $row_index >= 0; $row_index--) {
            $columns = $rows[$row_index];
            $columns = array_reverse($columns); // Since we do array_unshift later we reverse here, so when updating the draftpool it happens from left to right.
            foreach($columns as $column) {
                if(isset($cards[$locationArg])) {
                    $building = Building::get($cards[$locationArg]['id']);
                    $row = $row_index + 1;
                    if ($building->id == $buildingId) {
                        return [$row, $column];
                    }
                }
                $locationArg--;
            }
        }
        return null;
    }

    public static function buildingAvailable($buildingId) {
        $ids = SevenWondersDuelPantheon::get()->getAvailableCardIds();
        return in_array((int)$buildingId, $ids);
    }

    public static function buildingRevealable($age, $buildingId) {
        $cards = SevenWondersDuelPantheon::get()->buildingDeck->getCardsInLocation("age{$age}");
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
        $actualAge = SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CURRENT_AGE);
        $age = $actualAge;
        if ($actualAge == 0 && SevenWondersDuelPantheon::get()->expansionActive()) {
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
            $cards = SevenWondersDuelPantheon::get()->buildingDeck->getCardsInLocation("age{$age}");
            $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

            $availableCardIds = SevenWondersDuelPantheon::get()->getAvailableCardIds();
            if (!$revealCards && count($availableCardIds) == 0 && count($cards) > 0) {
                // Needed during the launch of this update when running games don't have the revealed cards value yet.
                $revealCards = true;
            }

            $activePlayer = Player::getActive();
            // Check for Mythology and Offering tokens
            $tokenCards = [];
            if (SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::OPTION_PANTHEON)) {
                if ($age == 1) $tokenCards = MythologyTokens::getDeckCardsSorted('board');
                if ($age == 2) $tokenCards = OfferingTokens::getDeckCardsSorted('board');

                $draftpool['mythologyTokens'] = MythologyTokens::getBoardTokens();
                $draftpool['offeringTokens'] = OfferingTokens::getBoardTokens();
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
                        $row = $row_index + 1;
                        $position = [
                            'row' => $row,
                            'column' => $column,
                            'location' => $locationArg,
                        ];
                        $positionsFound[] = $row . "_" . $column;
                        $cardvisible = $row_index % 2 == 0;
                        // Determine if card is available because other cards have revealed it.
                        $available = Draftpool::buildingRevealable($age, $building->id, $revealCards);

                        if ($available) {
                            if ($revealCards && !in_array($building->id, $availableCardIds)) {
                                // Reveal this card
                                $availableCardIds[] = $building->id;

                                // Check for Mythology and Offering tokens
                                foreach ($tokenCards as $card) {
                                    $cardRowCol = Draftpool::getTokenRowCol($age, $card['location_arg']);
                                    if ($cardRowCol[0] == $row && $cardRowCol[1] == $column) {
                                        // Mythology token
                                        if ($age == 1) {
                                            $mythologyToken = MythologyToken::get($card['id']);
                                            $mythologyToken->take($activePlayer, $building);

                                            $draftpool['mythologyToken'] = true;

                                            $revealCards = false; // Don't reveal the cards just yet, handle the mythology token first (ChooseAndPlaceDivinity)
                                        }
                                        // Offering token
                                        if ($age == 2) {
                                            $offeringToken = OfferingToken::get($card['id']);
                                            $offeringToken->take($activePlayer, $building);
                                        }
                                    }
                                }
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
                                foreach (Players::get() as $tmpPlayer) {
                                    $payment = $tmpPlayer->getPaymentPlan($building);
                                    $position['cost'][$tmpPlayer->id] = $payment->totalCost();
                                    $position['payment'][$tmpPlayer->id] = $payment;
                                    $position['hasLinkedBuilding'][$tmpPlayer->id] = $tmpPlayer->hasBuilding($building->linkedBuilding)
                                        || ($tmpPlayer->hasDecree(15) && $tmpPlayer->getOpponent()->hasBuilding($building->linkedBuilding));
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
                SevenWondersDuelPantheon::get()->setAvailableCardIds($availableCardIds);
            }
        }

        return $draftpool;
    }

    public static function countCardsInCurrentAge() {
        $age = SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuelPantheon::get()->buildingDeck->getCardsInLocation("age{$age}");
        return count($cards);
    }

    /**
     * @return array
     */
    public static function getRows($age): array {
        if ($age > 0) {
            if (SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::OPTION_AGORA)) {
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
        $age = SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuelPantheon::get()->buildingDeck->getCardsInLocation("age{$age}");
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