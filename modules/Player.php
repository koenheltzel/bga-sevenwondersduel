<?php

namespace SWD;

class Player {

    public $wonderIds = [];
    public $buildingIds = [];
    public $progressTokenIds = [];

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
     * Count resources of the specified type as provided by Brown and Grey cards only.
     * @param $searchResource
     * @return int
     */
    public function resourceCount($searchResource) {
        $count = 0;
        foreach ($this->buildingIds as $id) {
            $building = Building::get($id);
            if (in_array($building->type, [TYPE_BROWN, TYPE_GREY])) {
                foreach($building->resources as $resource => $amount) {
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
    public function calculateCost($buyingItem, $print = false, $printChoices = false) {
        if($print) print "<PRE>Calculate cost for player to buy \"{$buyingItem->name}\" card.</PRE>";

        $costLeft = $buyingItem->cost;
        if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";

        $payment = new Payment();

        // What can the player produce with basic brown / grey cards?
        foreach ($this->buildingIds as $id) {
            $building = Building::get($id);
            if (in_array($building->type, [TYPE_BROWN, TYPE_GREY])) {
                foreach($building->resources as $resource => $amount) {
                    if (array_key_exists($resource, $costLeft)) {
                        $canProduce = min($costLeft[$resource], $amount);
    
                        $string = "Player produces {$canProduce} {$resource} with building \"{$building->name}\".";
                        $payment->addStep(0, $building->id, $string);
                        if($print) print "<PRE>$string</PRE>";
                        $costLeft[$resource] -= $canProduce;
                        if ($costLeft[$resource] <= 0) {
                            unset($costLeft[$resource]);
                        }
                        if($print && $costLeft > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                    }
                }
            }
        }

        // What about resource "choice" cards? In order to make the most optimal choice we should consider all combinations
        // and the costs of the remaining resources to pick the cheapest solution.
        $choices = [];
        $choiceItemIds = [];
        foreach ($this->buildingIds as $id) {
            $building = Building::get($id);
            if (count($building->resourceChoice) > 0) {
                $choices[] = $building->resourceChoice;
                $choiceItemIds[] = "B{$id}";
            }
        }
        foreach ($this->wonderIds as $id) {
            $wonder = Wonder::get($id);
            if (count($wonder->resourceChoice) > 0) {
                $choices[] = $wonder->resourceChoice;
                $choiceItemIds[] = "W{$id}";
            }
        }
        if (count($choices) > 0) {
            if($printChoices) print "<PRE>=========================================================</PRE>";
            $combinations = $this->combinations($choices);
            /** @var Payment $cheapestCombination */
            $cheapestCombination = null;
            $cheapestCombinationIndex = null;
            $cheapestCombinationCostLeft = null;
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
                    if($printChoices) print "<PRE>Considering combination of choice card resources: " . print_r($combination, true) . "</PRE>";
                    if($printChoices) print "<PRE>Resources needed afterwards: " . print_r($costLeftCopy, true) . "</PRE>";
                    $tmpPayment = $this->resourceCostToPlayer($costLeftCopy, null, $printChoices);
                    if(is_null($cheapestCombination) || $tmpPayment->totalCost() < $cheapestCombination->totalCost()) {
                        $cheapestCombination = $tmpPayment;
                        $cheapestCombinationIndex = $combinationIndex;
                        $cheapestCombinationCostLeft = $costLeftCopy;
                    }
                    if($printChoices) print "<PRE>Cost to player: " . print_r($tmpPayment->totalCost(), true) . "</PRE>";
                }
                if($printChoices) print "<PRE>=========================================================</PRE>";
            }
            if (!is_null($cheapestCombination)) {
                $costLeft = $cheapestCombinationCostLeft;
                foreach($combinations[$cheapestCombinationIndex] as $choiceItemIndex => $resource) {
                    $cardType = substr($choiceItemIds[$choiceItemIndex], 0, 1);
                    $cardId = substr($choiceItemIds[$choiceItemIndex], 1);
                    switch ($cardType) {
                        case 'B':
                            $building = Building::get($cardId);
                            $string = "Player produces 1 {$resource} with building \"{$building->name}\".";
                            if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                            $payment->addStep(0, $building->id, $string);
                            break;
                        case 'W':
                            $wonder = Wonder::get($cardId);
                            $string = "Player produces 1 {$resource} with wonder \"{$wonder->name}\".";
                            if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                            $payment->addStep(0, $wonder->id, $string);
                            break;
                    }
                }
                if($printChoices) print "<PRE>Cheapest combination: " . print_r([$combinations[$cheapestCombinationIndex], $cheapestCombination], true) . "</PRE>";
            }
        }

        // Any remaining cost should be paid with coins - let's calculate how much:
        $this->resourceCostToPlayer($costLeft, $payment, $print);

        if($print) print "<PRE>Total cost: {$payment->totalCost()}</PRE>";

        return $payment;
    }


    /**
     * If the player needs to buy a resource with coins, how much is it?
     */
    public function resourceCostToPlayer($costLeft, $payment = null, $print = false) {
        if(is_null($payment)) $payment = new Payment();

        // Any fixed price resources (Stone Reserve, Clay Reserve, Wood Reserve)?
        foreach ($this->buildingIds as $id) {
            $building = Building::get($id);
            if (in_array($building->type, [TYPE_YELLOW])) {
                foreach($building->fixedPriceResources as $resource => $price) {
                    if (array_key_exists($resource, $costLeft)) {
                        $cost = $costLeft[$resource] * $price;
                        $string = "Player pays {$cost} coin(s) for {$costLeft[$resource]} {$resource} using the fixed cost building \"{$building->name}\" offers.";
                        $payment->addStep($cost, $building->id, $string);
                        if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                        unset($costLeft[$resource]);
                        if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
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
            $payment->addStep($cost, null, $string);
            unset($costLeft[$resource]);
        }

        return $payment;
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