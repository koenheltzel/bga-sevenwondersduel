<?php

namespace SWD;

class PaymentPlan
{

    private static $maskCombinations = null; // To prevent passing an array. // TODO check if this is more memory efficient.
    private $item = null; // Private so it's not included to javascript.
    /**
     * @var PaymentPlanStep[] array
     */
    public $steps = [];

    public function __construct($item = null) {
        $this->item = $item;
    }

    public function addStep($resource, $amount, $cost, $itemType, $itemId, $string) {
        $this->steps[] = new PaymentPlanStep($resource, $amount, $cost, $itemType, $itemId, $string);
    }

    private static function applyMask($cost, $mask) {
        $flat = self::toCostFlat($cost);
        return array_count_values(array_intersect_key($flat, array_flip($mask)));
    }

    private static function toCostFlat($cost) {
        $flat = [];
        foreach($cost as $resource => $amount) {
            $flat = array_pad($flat , count($flat) + $amount , $resource);
        }
        return $flat;
    }

    public function calculate(Player $player, $print = false, $printChoices = false) {
        if($print) print "<PRE>Calculate cost for player to buy “{$this->item->name}\" card.</PRE>";

        $costLeft = $this->item->cost;
//        if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";

        if ($this->item instanceof Building && $player->hasBuilding($this->item->linkedBuilding)) {
            // Player has the linked building, so no building cost.
            $linkedBuilding = Building::get($this->item->linkedBuilding);
            $string = "Construction is free through linked building “{$linkedBuilding->name}”.";
            if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
            $this->addStep(LINKED_BUILDING, 1, 0, Item::TYPE_BUILDING, $this->item->linkedBuilding, $string);
        }
        else {
            // Coins in the cost
            if (isset($costLeft[COINS])) {
                $resource = COINS;
                $string = "Pay {$costLeft[COINS]} {$resource}.";
                if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                $this->addStep(COINS, $costLeft[COINS], $costLeft[COINS], null, null, $string);

                unset($costLeft[$resource]);
//                if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
            }
            //
            if(count($costLeft) > 0) {
                // What can the player produce with basic brown / grey cards?
                foreach ($player->getBuildings()->filterByTypes([Building::TYPE_BROWN, Building::TYPE_GREY]) as $building) {
                    foreach($building->resources as $resource => $amount) {
                        if (array_key_exists($resource, $costLeft)) {
                            $canProduce = min($costLeft[$resource], $amount);

                            $string = "Produce {$canProduce} {$resource} with building “{$building->name}”.";
                            $this->addStep($resource, $canProduce, 0, Item::TYPE_BUILDING, $building->id, $string);
                            if($print) print "<PRE>$string</PRE>";
                            $costLeft[$resource] -= $canProduce;
                            if ($costLeft[$resource] <= 0) {
                                unset($costLeft[$resource]);
                            }
//                            if($print && $costLeft > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                        }
                    }
                }

                // Is there a progress token we should consider? Architecture and Masonry provide a 2 resource discount.
                $discountProgressToken = null;
                if($this->item instanceof Wonder && $player->hasProgressToken(2)) $discountProgressToken = ProgressToken::get(2); // Architecture
                if($this->item instanceof Building && $this->item->type == Building::TYPE_BLUE && $player->hasProgressToken(5)) $discountProgressToken = ProgressToken::get(5); // Masonry

                $costLeftFlat = self::toCostFlat($costLeft);
                $indexes = array_keys($costLeftFlat); // Something like [0,1,2,3]
                $maskCombinations = $discountProgressToken ? self::getMaskCombinations($indexes) : [$indexes];

                // What about resource "choice" cards? In order to make the most optimal choice we should consider all combinations
                // and the costs of the remaining resources to pick the cheapest solution.
                $choices = [];
                $choiceItems = [];
                $costLeftKeys = array_keys($costLeft);
                foreach (array_merge($player->getBuildings()->filterByTypes([Building::TYPE_YELLOW])->array, $player->getWonders()->array) as $item) {
                    if (count($item->resourceChoice) > 0 && ($item instanceof Building || $item->isConstructed())) {
                        $relevantResourceChoices = [];
                        foreach($item->resourceChoice as $resource) {
                            if (in_array($resource, $costLeftKeys)) {
                                $relevantResourceChoices[] = $resource;
                            }
                        }
                        $choices[] = $relevantResourceChoices;
                        $choiceItems[] = $item;
                    }
                }

                if (count($choices) > 0) {
                    if($printChoices) print "<PRE>=========================================================</PRE>";
                    $combinations = $this->combinations($choices);
                    /** @var PaymentPlan $cheapestCombinationPayment */
                    $cheapestCombinationPayment = null;
                    $cheapestCombinationIndex = null;
                    $cheapestCombinationMaskIndex = null;
                    foreach($combinations as $combinationIndex => $combination) {
                        $combinationCost = array_count_values($combination);
                        if($printChoices) print "<PRE>Considering combination of choice card resources: " . print_r($combination, true) . "</PRE>";
                        foreach($maskCombinations as $maskCombinationIndex => $mask) {
                            $costLeftCopy = $costLeft;
                            $costLeftMasked = self::applyMask($costLeft, $mask);
                            $resourcesFound = false;
                            foreach ($costLeftMasked as $resource => $amount) {
                                if(isset($combinationCost[$resource])) {
                                    $resourcesFound = true;
                                    $costLeftMasked[$resource] -= min($costLeftMasked[$resource], $combinationCost[$resource]);
                                    if ($costLeftMasked[$resource] <= 0) {
                                        unset($costLeftMasked[$resource]);
                                    }
                                }
                            }
                            if ($resourcesFound) {
                                if($printChoices) print "<PRE>Considering combination of choice card resources: " . print_r($combination, true) . "</PRE>";
                                if($printChoices) print "<PRE>Resources needed afterwards: " . print_r($costLeftMasked, true) . "</PRE>";
                                $tmpPayment = self::resourceCostToPlayer($player, $costLeftMasked, null, $printChoices);
                                if(is_null($cheapestCombinationPayment) || $tmpPayment->totalCost() < $cheapestCombinationPayment->totalCost()) {
                                    $cheapestCombinationPayment = $tmpPayment;
                                    $cheapestCombinationIndex = $combinationIndex;
                                    $cheapestCombinationMaskIndex = $maskCombinationIndex;
                                }
                                if($printChoices) print "<PRE>Cost to player: " . print_r($tmpPayment->totalCost(), true) . "</PRE>";
                            }
                            if($printChoices) print "<PRE>=========================================================</PRE>";
                        }
                    }
                    if (!is_null($cheapestCombinationPayment)) {
                        $mask = $maskCombinations[$cheapestCombinationMaskIndex];
                        foreach($combinations[$cheapestCombinationIndex] as $choiceItemIndex => $resource) {
                            if (isset($costLeft[$resource])) {
                                if (!in_array($choiceItemIndex, $mask)) {
                                    
                                }
                                else {
                                    
                                }
                                $item = $choiceItems[$choiceItemIndex];
                                if ($item instanceof Building) {
                                    $string = "Produce 1 {$resource} with building “{$item->name}”.";
                                    if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                                    $this->addStep($resource, 1, 0, Item::TYPE_BUILDING, $item->id, $string);
                                }
                                if ($item instanceof Wonder) {
                                    $string = "Produce 1 {$resource} with wonder “{$item->name}”.";
                                    if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                                    $this->addStep($resource, 1, 0, Item::TYPE_WONDER, $item->id, $string);
                                }
                                self::subtractResource($costLeft, $resource);
//                                if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                            }
                        }
                        $discounted = array_diff(array_keys($costLeftFlat), $mask);
                        foreach ($discounted as $flatCostIndex) {
                            $resource = $costLeftFlat[$flatCostIndex];
                            $string = "{$resource} discounted thanks to Progress token “{$discountProgressToken->name}”.";
                            if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                            $this->addStep($resource, 1, 0, Item::TYPE_PROGRESSTOKEN, $discountProgressToken->id, $string);

                            self::subtractResource($costLeft, $resource);
//                            if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                        }
                        if($printChoices) print "<PRE>Cheapest combination: " . print_r([$combinations[$cheapestCombinationIndex], $cheapestCombinationPayment], true) . "</PRE>";
                    }
                }

                // Any remaining cost should be paid with coins - let's calculate how much:
                self::resourceCostToPlayer($player, $costLeft, $this, $print);

            } // End if costLeft > 0

            $this->sortSteps($this->item->cost);
        }

        if($print) print "<PRE>Total cost: {$this->totalCost()} coin(s)</PRE>";
    }

    private static function subtractResource(&$cost, $resource) {
        $cost[$resource] -= 1;
        if ($cost[$resource] <= 0) {
            unset($cost[$resource]);
        }
    }

    /**
     * Gets the "mask" calculations, aka all combinations of indexes but with 2 excluded (for Progress tokens Architecture & Masonry
     * @param $indexes
     * @param int $level
     * @param int $levelStart
     * @param array $selectedIndexes
     * @param int $excludeCount
     */
    private static function getMaskCombinations($indexes, $level = 0, $levelStart = 0, $selectedIndexes = [], $excludeCount = 2) {
        if ($level == 0) self::$maskCombinations = [];
        for($i = $levelStart; $i < count($indexes) - ($excludeCount - $level); $i++) {
            $selectedIndexes[] = $i;

            // Right amount of indexes reached.
            if ($level == count($indexes) - 1 - $excludeCount) {
                self::$maskCombinations[] = $selectedIndexes;
            }
            else {
                // We need to move deeper
                self::getMaskCombinations($indexes, $level + 1, $i + 1, $selectedIndexes);
            }
            array_pop($selectedIndexes);
        }
        if ($level == 0) return self::$maskCombinations;
    }

    /**
     * If the player needs to buy a resource with coins, how much is it?
     * @param $costLeft
     * @param PaymentPlan|null $payment
     * @param bool $print
     * @return PaymentPlan|null
     */
    public static function resourceCostToPlayer(Player $player, $costLeft, $payment = null, $print = false) {
        if(is_null($payment)) $payment = new PaymentPlan();

        // Any fixed price resources (Stone Reserve, Clay Reserve, Wood Reserve)?
        foreach ($player->getBuildings()->filterByTypes([Building::TYPE_YELLOW]) as $building) {
            foreach($building->fixedPriceResources as $resource => $price) {
                if (array_key_exists($resource, $costLeft)) {
                    for ($i = 0; $i < $costLeft[$resource]; $i++) {
                        $string = "Pay {$price} coin(s) for 1 {$resource} using the fixed cost building “{$building->name}” offers.";
                        $payment->addStep($resource, 1, $price, Item::TYPE_BUILDING, $building->id, $string);
                        if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                    }
                    unset($costLeft[$resource]);
//                    if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                }
            }
        }

        // What should the player pay for the remaining resources?
        foreach ($costLeft as $resource => $amount) {
            $opponentResourceCount = $player->getOpponent()->resourceCount($resource);
            for($i = 0; $i < $amount; $i++) {
                $cost = 2 + $opponentResourceCount;
                $string = null;
                if ($opponentResourceCount > 0) {
                    $color = in_array($resource, [GLASS, PAPYRUS]) ? clienttranslate('grey') : clienttranslate('brown');
                    $string = "Pay {$cost} coins for 1 {$resource} because opponent can produce {$opponentResourceCount} {$resource} with {$color} card(s).";
                } else {
                    $string = "Pay {$cost} coins for 1 {$resource}.";
                }
                if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                $payment->addStep($resource, 1, $cost, null, null, $string);
            }
            unset($costLeft[$resource]);
        }

        return $payment;
    }

    /**
     * Thanks to Krzysztof https://stackoverflow.com/a/8567199
     */
    private function combinations($arrays, $i = 0) {
        // Custom by Koen, in case of 1 record, it wouldn't return the possibilities as separate records.
        if($i == 0 && count($arrays) == 1) {
            $result = [];
            foreach($arrays[0] as $resource) {
                $result[] = [$resource];
            }
            return $result;
        }
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

    public function totalCost() {
        $cost = 0;
        foreach($this->steps as $row) {
            $cost += $row->cost;
        }
        return $cost;
    }

    public function sortSteps($cost) {
        $resources = array_keys($cost);
        $sortedSteps = [];
        foreach($resources as $resource) {
            $tmpSteps = array_filter($this->steps, function($step)use($resource){
                return $step->resource == $resource;
            });
            $sortedSteps = array_merge($sortedSteps, $tmpSteps);
        }
        $this->steps = $sortedSteps;
    }

    /**
     * @return Item
     */
    public function getItem() {
        return $this->item;
    }

}

class PaymentPlanStep
{

    public $resource;
    public $amount;
    public $cost = 0;
    public $itemType;
    public $itemId;
    public $string = "";

    public function __construct($resource, $amount, $cost, $itemType, $itemId, $string) {
        $this->resource = $resource;
        $this->amount = $amount;
        $this->cost = $cost;
        $this->itemType = $itemType;
        $this->itemId = $itemId;
        $this->string = $string;
    }

}