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

    public static function get() {
        return [
            Player::me()->id => Draftpool::getPlayer(Player::me()->id),
            Player::opponent()->id => Draftpool::getPlayer(Player::opponent()->id),
        ];
    }

    private static function getPlayer($playerId) {
        $age = SevenWondersDuel::get()->getCurrentAge();
        $cards = SevenWondersDuel::get()->buildingDeck->getCardsInLocation("age{$age}");
        $cards = arrayWithPropertyAsKeys($cards, 'location_arg');

        $draftpool = [];
        $locationArg = 19; // Each age has 20 cards. Make this dynamic when it works: count(self::$ages[$age], COUNT_RECURSIVE) - count(self::$ages[$age]))
        $positionsFound = [];
        for($row_index = count(self::$ages[$age]) - 1; $row_index >= 0; $row_index--) {
            $columns = self::$ages[$age][$row_index];
            foreach($columns as $column) {
                if(isset($cards[$locationArg])) {
                    $building = Building::get($cards[$locationArg]['type_arg']);
                    $position = [
                        'row' => $row_index + 1,
                        'column' => $column
                    ];
                    $positionsFound[] = ($row_index + 1) . "_" . $column;
                    $cardvisible = $row_index % 2 == 0;
                    if (!$cardvisible) {
                        // Determine if card is available because other cards have revealed it.
                        if (!in_array(($row_index + 2) . "_" . ($column - 1), $positionsFound) && !in_array(($row_index + 2) . "_" . ($column + 1), $positionsFound)) {
                            $cardvisible = true;
                        }
                    }
                    if ($cardvisible) {
                        $payment = Player::get($playerId)->calculateCost($building);
                        $position['building'] = $building->id;
                        $position['cost'] = $payment->totalCost();
                        $position['payment'] = $payment;
                        $position['card'] = (int)$cards[$locationArg]['id'];
                    }
                    else {
                        $position['back'] = 73 + $age;
                    }
                    array_unshift($draftpool, $position);
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