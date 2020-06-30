<?php

namespace SWD;

use SevenWondersDuel;

class Player extends \APP_DbObject{

    public $id = null;
    private $wonderIds = [];
    private $buildingIds = [];
    public $progressTokenIds = [];

    public static $instances = [];

    /**
     * @return Player
     */
    public static function get($playerId) {
        if (!isset(self::$instances[$playerId])) {
            self::$instances[$playerId] = new Player($playerId);
        }
        return self::$instances[$playerId];
    }

    /**
     * @return Player
     */
    public static function me() {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return self::get(1);
        } else {
            $playerId = SevenWondersDuel::get()->getCurrentPlayerId();
            return self::get($playerId);
        }
    }

    /**
     * @return Player
     */
    public static function opponent($meId = null) {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return self::get(2);
        } else {
            if (is_null($meId)) $meId = SevenWondersDuel::get()->getCurrentPlayerId();
            $players = SevenWondersDuel::get()->loadPlayersBasicInfos();
            $playerIds = array_keys($players);
            foreach ($playerIds as $playerId) {
                // This is a 2-player game so we just look for a playerId not matching currentPlayerId.
                if ($playerId <> $meId) {
                    return self::get($playerId);
                }
            }
            throw new BgaSystemException(self::_("Opponent object couldn't be constructed."));
        }
    }

    private function __construct($id) {
        $this->id = $id;
        self::$instances[$id] = $this;
    }

    public function getCoins() {
        return (int)$this->getValue("player_coins");
    }

    public function increaseCoins($increase) {
        return $this->increaseValue("player_coins", $increase);
    }

    // get score
    function getScore() {
        return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='{$this->id}'");
    }
    // set score
    function setScore($score) {
        $this->DbQuery("UPDATE player SET player_score='$score' WHERE player_id='{$this->id}'");
    }
    // increment score (can be negative too)
    function increaseScore($increase) {
        $count = $this->getScore();
        if ($increase != 0) {
            $count += $increase;
            $this->setScore($count);
        }
        return $count;
    }

    public function getValue(string $column) {
        return self::getUniqueValueFromDB( "SELECT `$column` FROM player WHERE player_id='{$this->id}'" );
    }

    public function setValue(string $column, mixed $value) {
        self::DbQuery( "UPDATE player SET `$column` = {$this->escapeStringForDB($value)} WHERE player_id='{$this->id}'" );
    }

    public function increaseValue(string $column, int $increase) {
        self::DbQuery( "UPDATE player SET `$column` = `$column` + $increase WHERE player_id='{$this->id}'" );
    }

    /**
     * Count resources of the specified type as provided by Brown and Grey cards only.
     * @param $searchResource
     * @return int
     */
    public function resourceCount($searchResource) {
        $count = 0;
        foreach ($this->getBuildingIds() as $id) {
            $building = Building::get($id);
            if (in_array($building->type, [Building::TYPE_BROWN, Building::TYPE_GREY])) {
                foreach($building->resources as $resource => $amount) {
                    if ($searchResource == $resource) {
                        $count += $amount;
                    }
                }
            }
        }
        return $count;
    }

    public function calculateDiscardGain() {
        return 2 + count($this->getBuildings()->filterByTypes([Building::TYPE_YELLOW])->array);
    }

    /**
     * @param Item $buyingItem
     */
    public function calculateCost($buyingItem, $print = false, $printChoices = false) {
        if($print) print "<PRE>Calculate cost for player to buy \"{$buyingItem->name}\" card.</PRE>";

        $costLeft = $buyingItem->cost;
        if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";

        $payment = new Payment();
        if (isset($costLeft[COINS])) {
            $resource = COINS;
            $string = "Player pays {$costLeft[COINS]} {$resource}.";
            if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
            $payment->addStep(COINS, $costLeft[COINS], $costLeft[COINS], null, null, $string);

            unset($costLeft[$resource]);
            if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
        }

        if(count($costLeft) > 0) {
            // What can the player produce with basic brown / grey cards?
            foreach ($this->getBuildings()->filterByTypes([Building::TYPE_BROWN, Building::TYPE_GREY]) as $building) {
                foreach($building->resources as $resource => $amount) {
                    if (array_key_exists($resource, $costLeft)) {
                        $canProduce = min($costLeft[$resource], $amount);

                        $string = "Player produces {$canProduce} {$resource} with building \"{$building->name}\".";
                        $payment->addStep($resource, $canProduce, 0, Item::TYPE_BUILDING, $building->id, $string);
                        if($print) print "<PRE>$string</PRE>";
                        $costLeft[$resource] -= $canProduce;
                        if ($costLeft[$resource] <= 0) {
                            unset($costLeft[$resource]);
                        }
                        if($print && $costLeft > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                    }
                }
            }

            // What about resource "choice" cards? In order to make the most optimal choice we should consider all combinations
            // and the costs of the remaining resources to pick the cheapest solution.
            $choices = [];
            $choiceItemIds = [];
            foreach ($this->getBuildings() as $building) {
                if (count($building->resourceChoice) > 0) {
                    $choices[] = $building->resourceChoice;
                    $choiceItemIds[] = Item::TYPE_BUILDING . $building->id;
                }
            }
            foreach ($this->getWonders() as $wonder) {
                if ($wonder->isConstructed() && count($wonder->resourceChoice) > 0) {
                    $choices[] = $wonder->resourceChoice;
                    $choiceItemIds[] = Item::TYPE_WONDER . $wonder->id;;
                }
            }
            if (count($choices) > 0) {
                if($printChoices) print "<PRE>=========================================================</PRE>";
                $combinations = $this->combinations($choices);
//            print "<PRE>" . print_r($combinations, true) . "</PRE>";
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
                        $itemType = substr($choiceItemIds[$choiceItemIndex], 0, 1);
                        $cardId = substr($choiceItemIds[$choiceItemIndex], 1);
                        switch ($itemType) {
                            case Item::TYPE_BUILDING:
                                $building = Building::get($cardId);
                                $string = "Player produces 1 {$resource} with building \"{$building->name}\".";
                                if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                                $payment->addStep($resource, 1, 0, Item::TYPE_BUILDING, $building->id, $string);
                                break;
                            case Item::TYPE_WONDER:
                                $wonder = Wonder::get($cardId);
                                $string = "Player produces 1 {$resource} with wonder \"{$wonder->name}\".";
                                if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                                $payment->addStep($resource, 1, 0, Item::TYPE_WONDER, $wonder->id, $string);
                                break;
                        }
                    }
                    if($printChoices) print "<PRE>Cheapest combination: " . print_r([$combinations[$cheapestCombinationIndex], $cheapestCombination], true) . "</PRE>";
                }
            }

            // Any remaining cost should be paid with coins - let's calculate how much:
            $this->resourceCostToPlayer($costLeft, $payment, $print);
        }

        if($print) print "<PRE>Total cost: {$payment->totalCost()} coin(s)</PRE>";

        return $payment;
    }


    /**
     * If the player needs to buy a resource with coins, how much is it?
     * @param $costLeft
     * @param Payment|null $payment
     * @param bool $print
     * @return Payment|null
     */
    public function resourceCostToPlayer($costLeft, $payment = null, $print = false) {
        if(is_null($payment)) $payment = new Payment();

        // Any fixed price resources (Stone Reserve, Clay Reserve, Wood Reserve)?
        foreach ($this->getBuildings()->filterByTypes([Building::TYPE_YELLOW]) as $building) {
            foreach($building->fixedPriceResources as $resource => $price) {
                if (array_key_exists($resource, $costLeft)) {
                    $cost = $costLeft[$resource] * $price;
                    $string = "Player pays {$cost} coin(s) for {$costLeft[$resource]} {$resource} using the fixed cost building \"{$building->name}\" offers.";
                    $payment->addStep($resource, $costLeft[$resource], $cost, Item::TYPE_BUILDING, $building->id, $string);
                    if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
                    unset($costLeft[$resource]);
                    if($print && count($costLeft) > 0) print "<PRE>" . print_r($costLeft, true) . "</PRE>";
                }
            }
        }

        // What should the player pay for the remaining resources?
        foreach ($costLeft as $resource => $amount) {
            $opponentResourceCount = Player::opponent($this->id)->resourceCount($resource);
            $cost = $amount * 2 + $opponentResourceCount;
            $string = null;
            if ($opponentResourceCount > 0) {
                $string = "Player pays {$cost} coins for {$amount} {$resource} because opponent can produce {$opponentResourceCount} {$resource}.";
            } else {
                $string = "Player pays {$cost} coins for {$amount} {$resource}.";
            }
            if($print) print "<PRE>" . print_r($string, true) . "</PRE>";
            $payment->addStep($resource, $amount, $cost, null, null, $string);
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

    /**
     * @return Wonders
     */
    public function getWonders(): Wonders {
        return Wonders::createByWonderIds($this->getWonderIds());
    }

    public function getWondersData(): array {
        $cards = $this->getWonderDeckCards();
        $rows = [];
        foreach($cards as $card) {
            $wonder = Wonder::get($card['type_arg']);
            $row = [];
            $row['card'] = (int)$card['id'];
            $row['wonder'] = $wonder->id;
            $row['constructed'] = $wonder->isConstructed();
            $payment = $this->calculateCost($wonder);
            $row['cost'] = $row['constructed'] ? -1 : $payment->totalCost();
            $row['payment'] = $payment;
            $rows[] = $row;
        }
        return $rows;
    }

    public function getWonderDeckCards(): array {
        return Wonders::getDeckCardsSorted($this->id);
    }

    public function getBuildingDeckCards(): array {
        return SevenWondersDuel::get()->buildingDeck->getCardsInLocation($this->id);
    }

    /**
     * @return Buildings
     */
    public function getBuildings(): Buildings {
        return Buildings::createByBuildingIds($this->getBuildingIds());
    }

    /**
     * @return array
     */
    public function getProgressTokens(): array {
        return $this->progressTokenIds;
    }

    public function hasProgressToken($id) : bool {
        return in_array($id, $this->progressTokenIds);
    }

    /**
     * @return array
     */
    public function getBuildingIds(): array {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return $this->buildingIds;
        }
        else {
            $buildingIds = [];
            foreach ($this->getBuildingDeckCards() as $cardId => $card) {
                $buildingIds[] = $card['type_arg'];
            }
            return $buildingIds;
        }
    }

    /**
     * @return array
     */
    public function getWonderIds(): array {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return $this->wonderIds;
        }
        else {
            $wonderIds = [];
            foreach ($this->getWonderDeckCards() as $cardId => $card) {
                $wonderIds[] = $card['type_arg'];
            }
            return $wonderIds;
        }
    }

    /**
     * @param array $wonderIds
     */
    public function setWonderIds(array $wonderIds): void {
        $this->wonderIds = $wonderIds;
    }

    /**
     * @param array $buildingIds
     */
    public function setBuildingIds(array $buildingIds): void {
        $this->buildingIds = $buildingIds;
    }

}