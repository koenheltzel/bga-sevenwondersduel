<?php

namespace SWD;

class Item
{

    public $name = "";
    public $cost = []; // coins and or resources
    public $resources = []; // and their optional costs
    public $military = 0;
    public $victoryPoints = 0;
    public $playEffects = [];
    public $endEffects = [];

    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @param array $cost
     * @return Item
     */
    public function setCost($cost) {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @param array $resources
     * @return Item
     */
    public function setResources($resources) {
        $this->resources = $resources;
        return $this;
    }

    /**
     * @param int $military
     * @return Item
     */
    public function setMilitary($military) {
        $this->military = $military;
        return $this;
    }

}