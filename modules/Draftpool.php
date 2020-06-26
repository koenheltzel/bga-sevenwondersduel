<?php


namespace SWD;


use SevenWondersDuel;

class Draftpool
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

    public static function buildingAvailable($buildingId) {
        $age = SevenWondersDuel::get()->getCurrentAge();
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

        $locationArg = 19; // Each age has 20 cards. Make this dynamic when it works: count(self::$ages[$age], COUNT_RECURSIVE) - count(self::$ages[$age]))
        $positionsFound = [];
        for($row_index = count(self::$ages[$age]) - 1; $row_index >= 0; $row_index--) {
            $columns = self::$ages[$age][$row_index];
            foreach($columns as $column) {
                if(isset($cards[$locationArg])) {
                    $building = Building::get($cards[$locationArg]['type_arg']);
                    if ($building->id == $buildingId) {
                        // Last row is always available
                        $available = $row_index == count(self::$ages[$age]) - 1;
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
        $age = SevenWondersDuel::get()->getCurrentAge();
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

        $draftpool = [
            'age' => $age,
            'cards' => []
        ];

        $locationArg = 19; // Each age has 20 cards. Make this dynamic when it works: count(self::$ages[$age], COUNT_RECURSIVE) - count(self::$ages[$age]))
        $positionsFound = [];
        for($row_index = count(self::$ages[$age]) - 1; $row_index >= 0; $row_index--) {
            $columns = self::$ages[$age][$row_index];
            foreach($columns as $column) {
                if(isset($cards[$locationArg])) {
                    $building = Building::get($cards[$locationArg]['type_arg']);
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
                        $position['card'] = (int)$cards[$locationArg]['id'];

                        // Cost and payment plan for each player
                        $position['cost'] = [];
                        $position['discardGain'] = [];
                        $position['payment'] = [];
                        $players = SevenWondersDuel::get()->loadPlayersBasicInfos();
                        $playerIds = array_keys($players);
                        foreach ($playerIds as $playerId) {
                            $payment = Player::get($playerId)->calculateCost($building);
                            $position['cost'][$playerId] = $payment->totalCost();
                            $position['payment'][$playerId] = $payment;
                            $position['discardGain'][$playerId] = Player::me()->calculateDiscardGain($building);
                        }
                    }
                    else {
                        $position['back'] = 73 + $age;
                    }
                    array_unshift($draftpool['cards'], $position);
                }
                $locationArg--;
            }
        }
        return $draftpool;
    }

    public static function countCardsInCurrentAge() {
        $age = SevenWondersDuel::get()->getCurrentAge();
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        return count($cards);
    }

}