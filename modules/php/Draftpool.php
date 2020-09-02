<?php


namespace SWD;


use SevenWondersDuelAgora;

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
            [5,7],
            [4,6,8],
            [3,5,7,9],
            [2,4,6,8,10],
            [3,5,7,9],
            [4,6,8],
            [5,7],
        ]
    ];

    public static function buildingAvailable($buildingId) {
        $age = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation("age{$age}");
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

    public static function get() {
        $age = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation("age{$age}");
        $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

        $draftpool = [
            'age' => $age,
            'discardGain' => [
                Player::me()->id => Player::me()->calculateDiscardGain(),
                Player::opponent()->id => Player::opponent()->calculateDiscardGain(),
            ],
            'cards' => []
        ];

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
                    $available = Draftpool::buildingAvailable($building->id);
                    if (!$cardvisible && $available) {
                        $cardvisible = true;
                    }
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
                            $players = SevenWondersDuelAgora::get()->loadPlayersBasicInfos();
                            $playerIds = array_keys($players);
                            foreach ($playerIds as $playerId) {
                                $payment = Player::get($playerId)->getPaymentPlan($building);
                                $position['cost'][$playerId] = $payment->totalCost();
                                $position['payment'][$playerId] = $payment;
                                $position['hasLinkedBuilding'][$playerId] = Player::get($playerId)->hasBuilding($building->linkedBuilding);
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
        return $draftpool;
    }

    public static function countCardsInCurrentAge() {
        $age = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CURRENT_AGE);
        $cards = SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation("age{$age}");
        return count($cards);
    }

    /**
     * @return array
     */
    public static function getRows($age): array {
        if (SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::OPTION_AGORA)) {
            return self::$agoraAges[$age];
        }
        return self::$ages[$age];
    }

}