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
                    $canProduce = min($costLeft[$resource], $amount);
                    print "<PRE>Player produces {$canProduce} {$resource} with building {$item->name}.</PRE>";
                    $costLeft[$resource] -= $canProduce;
                    if ($costLeft[$resource] <= 0) {
                        unset($costLeft[$resource]);
                    }
                    print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                }
            }
        }

        $cost = 0;

        // Any fixed price resources?
        foreach ($this->items as $id) {
            /** @var Building $item */
            $item = $items[$id];
            if ($item instanceof Building) {
                foreach($item->fixedPriceResources as $resource => $amount) {
                    if (array_key_exists($resource, $costLeft)) {
                        $tmpCost = $costLeft[$resource] * $amount;
                        print "<PRE>Player pays {$tmpCost} coins for {$amount} {$resource} because of the fixed cost through building {$item->name}.</PRE>";
                        $cost += $tmpCost;
                        unset($costLeft[$resource]);
                        print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                    }
                }
            }
        }

        // What should the player pay for the remaining resources?
        foreach ($costLeft as $resource => $amount) {
            $opponentHas = 0;
            $tmpCost = $amount * 2 + $opponentHas;
            $cost += $tmpCost;
            unset($costLeft[$resource]);
            if ($opponentHas > 0) {
                print "<PRE>Player pays {$tmpCost} coins for {$amount} {$resource} because opponent can produce {$opponentHas} {$resource}.</PRE>";
            }
            else {
                print "<PRE>Player pays {$tmpCost} coins for {$amount} {$resource}.</PRE>";
            }

        }

        print "<PRE>Total cost: {$cost}</PRE>";
    }

}