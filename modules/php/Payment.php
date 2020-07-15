<?php

namespace SWD;

class Payment
{

    private $item = null; // Private so it's not included to javascript.
    /**
     * @var PaymentStep[] array
     */
    public $steps = [];

    public $militaryOldPosition = 0;
    public $militaryNewPosition = 0;
    public $militarySteps = 0;
    public $militaryTokenNumber = 0;
    public $militaryTokenValue = 0;
    public $militaryOpponentPays = 0;

    public $newScientificSymbolPair = false;
    public $urbanismAward = 0;
    public $coinReward = 0;
    public $opponentCoinLoss = 0;
    public $economyProgressTokenCoins = 0;

    public function __construct($item = null) {
        $this->item = $item;
    }

    public function addStep($resource, $amount, $cost, $itemType, $itemId, $string) {
        $this->steps[] = new PaymentStep($resource, $amount, $cost, $itemType, $itemId, $string);
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

class PaymentStep
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