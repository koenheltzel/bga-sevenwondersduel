<?php

namespace SWD;

class Payment
{

    /**
     * @var PaymentStep[] array
     */
    public $steps = [];

    public function __construct() {

    }

    public function addStep($cost, $itemId, $string) {
        $this->steps[] = new PaymentStep($cost, $itemId, $string);
    }

    public function totalCost() {
        $cost = 0;
        foreach($this->steps as $row) {
            $cost += $row->cost;
        }
        return $cost;
    }

}

class PaymentStep
{

    public $cost = 0;
    public $string = "";
    public $itemId;

    public function __construct($cost, $itemId, $string) {
        $this->cost = $cost;
        $this->string = $string;
        $this->itemId = $itemId;
    }

}