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

    public function resourceCount($searchResource) {
        global $items;
        $count = 0;
        foreach ($this->items as $id) {
            /** @var Building $item */
            $item = $items[$id];
            if ($item instanceof Building && in_array($item->type, [TYPE_BROWN, TYPE_GREY])) {
                foreach($item->resources as $resource => $amount) {
                    if ($searchResource == $resource) {
                        $count += $amount;
                    }
                }
            }
        }
        return $count;
    }

    /**
     * @param Item $buyingItem
     */
    public function calculateCost($buyingItem, $print = false) {
        if($print) print "<PRE>Calculate cost for player to buy \"{$buyingItem->name}\" card.</PRE>";
        global $items;
        $costLeft = $buyingItem->cost;
        if($print) print "<PRE>" . print_r($costLeft, true) . "</PRE>";

        $costExplanation = new CostExplanation();

        // What can the player produce with basic brown / grey cards?
        foreach ($this->items as $id) {
            /** @var Item $item */
            $item = $items[$id];
            foreach($item->resources as $resource => $amount) {
                if (array_key_exists($resource, $costLeft)) {
                    $canProduce = min($costLeft[$resource], $amount);

                    $string = "Player produces {$canProduce} {$resource} with building \"{$item->name}\".";
                    $costExplanation->addRow(0, $item->id, $string);
                    if($print) print "<PRE>$string</PRE>";
                    $costLeft[$resource] -= $canProduce;
                    if ($costLeft[$resource] <= 0) {
                        unset($costLeft[$resource]);
                    }
                    if($print) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                }
            }
        }

        // What about resource "choice" cards? In order to make the most optimal choice we should consider all combinations
        // and the costs of the remaining resources to pick the cheapest solution.
        $choices = [];
        $itemIds = [];
        foreach ($this->items as $id) {
            /** @var Building $item */
            $item = $items[$id];
            if (count($item->resourceChoice) > 0) {
                $choices[] = $item->resourceChoice;
                $itemIds[] = array_fill(0, count($item->resourceChoice), $item->id);
            }
//            foreach($item->resourceChoice as $resource) {
//                print "<PRE>" . print_r($item->name . " " . $resource, true) . "</PRE>";
//            }
        }
        if (count($choices) > 0) {
            if($print) print "<PRE>=========================================================</PRE>";
            $combinations = $this->combinations($choices);
            /** @var CostExplanation $cheapestCombination */
            $cheapestCombination = null;
            foreach($combinations as $combinationIndex => $combination) {
                $costLeftCopy = $costLeft;
                $combination = array_count_values($combination);
                $resourcesFound = false;
                foreach ($costLeftCopy as $resource => $amount) {
                    if(isset($combination[$resource])) {
                        $resourcesFound = true;
                        $costLeftCopy[$resource] -= $combination[$resource];
                        if ($costLeftCopy[$resource] <= 0) {
                            unset($costLeftCopy[$resource]);
                        }
                    }
                }
                if ($resourcesFound) {
                    if($print) print "<PRE>Considering combination of choice card resources: " . print_r($combination, true) . "</PRE>";
                    if($print) print "<PRE>Resources needed afterwards: " . print_r($costLeftCopy, true) . "</PRE>";
                    $tmpCostExplanation = $this->resourceCostToPlayer($costLeftCopy, null, $print);
                    if(is_null($cheapestCombination) || $tmpCostExplanation->totalCost() < $cheapestCombination->totalCost()) {
                        $cheapestCombination = $tmpCostExplanation;
                    }
                    if($print) print "<PRE>Cost to player: " . print_r($tmpCostExplanation->totalCost(), true) . "</PRE>";
                }
                if($print) print "<PRE>=========================================================</PRE>";
            }
            if (is_null($cheapestCombination)) {
                // TODO we need $costLeftCopy of the cheapest combination here.
            }
        }
//        exit;

        $this->resourceCostToPlayer($costLeft, $costExplanation, $print);

        if($print) print "<PRE>Total cost: {$costExplanation->totalCost()}</PRE>";

        return $costExplanation;
    }


    /**
     * If the player needs to buy a resource with coins, how much is it?
     */
    public function resourceCostToPlayer($costLeft, $costExplanation = null, $print = false) {
        global $items;

        if(is_null($costExplanation)) $costExplanation = new CostExplanation();

        // Any fixed price resources (Stone Reserve, Clay Reserve, Wood Reserve)?
        foreach ($this->items as $id) {
            /** @var Building $item */
            $item = $items[$id];
            if ($item instanceof Building) {
                foreach($item->fixedPriceResources as $resource => $amount) {
                    if (array_key_exists($resource, $costLeft)) {
                        $cost = $costLeft[$resource] * $amount;
                        $string = "Player pays {$cost} coin(s) for {$amount} {$resource} using the fixed cost building \"{$item->name}\" offers.";
                        $costExplanation->addRow($cost, $item->id, $string);
                        if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                        unset($costLeft[$resource]);
                        if($print) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                    }
                }
            }
        }

        // What should the player pay for the remaining resources?
        foreach ($costLeft as $resource => $amount) {
            $opponentResourceCount = Player::opponent()->resourceCount($resource);
            $cost = $amount * 2 + $opponentResourceCount;
            $string = null;
            if ($opponentResourceCount > 0) {
                $string = "Player pays {$cost} coins for {$amount} {$resource} because opponent can produce {$opponentResourceCount} {$resource}.";
            }
            else {
                $string = "Player pays {$cost} coins for {$amount} {$resource}.";
            }
            if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
            $costExplanation->addRow($cost, $item->id, $string);
            unset($costLeft[$resource]);
        }

        return $costExplanation;
    }

    /**
     * Thanks to Krzysztof https://stackoverflow.com/a/8567199
     */
    private function combinations($arrays, $i = 0) {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->combinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }

}