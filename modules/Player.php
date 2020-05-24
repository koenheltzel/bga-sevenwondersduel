<?php

namespace SWD;

class Player {

    public $items = [];

    public static $instances = [];

    /**
     * @return Player
     */
    public static function me() {
        return self::$instances[1];
    }

    /**
     * @return Player
     */
    public static function opponent() {
        return self::$instances[2];
    }

    public function __construct($id) {
        self::$instances[$id] = $this;
    }

    /**
     * @param Item $buyingItem
     */
    public function pay($buyingItem) {
        global $items;
        $costLeft = $buyingItem->cost;
        print "<PRE>" . print_r($costLeft, true) . "</PRE>";

        // What can the player produce with basic brown / grey cards?
        foreach ($this->items as $id) {
            /** @var Item $item */
            $item = $items[$id];
            foreach($item->resources as $resource => $amount) {
                if (array_key_exists($resource, $costLeft)) {
                    $canPay = min($costLeft[$resource], $amount);
                    print "<PRE>Player pays {$canPay} {$resource} with building {$item->name}.</PRE>";
                    $costLeft[$resource] -= $canPay;
                    if ($costLeft[$resource] <= 0) {
                        unset($costLeft[$resource]);
                    }
                    print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                }
            }
        }

        // What should the played pay for the remaining resources?
        foreach ($costLeft as $resource => $amount) {
            $opponentHas = 0;
            $cost = $amount * 2 + $opponentHas;
            if ($opponentHas > 0) {
                print "<PRE>Player pays {$cost} coins for {$amount} {$resource} because opponent can produce {$opponentHas} {$resource}.</PRE>";
            }
            else {
                print "<PRE>Player pays {$cost} coins for {$amount} {$resource}.</PRE>";
            }

        }
    }

}