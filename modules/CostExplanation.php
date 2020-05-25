<?php

namespace SWD;

class CostExplanation
{

    /**
     * @var CostExplanationRow[] array
     */
    public $rows = [];

    public function __construct() {

    }

    public function addRow($cost, $itemId, $string) {
        $this->rows[] = new CostExplanationRow($cost, $itemId, $string);
    }

    public function totalCost() {
        $cost = 0;
        foreach($this->rows as $row) {
            $cost += $row->cost;
        }
        return $cost;
    }

}

class CostExplanationRow
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