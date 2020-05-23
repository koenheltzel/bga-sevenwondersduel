<?php

namespace SWD;

class Item
{

    public $id = 0;
    public $name = "";
    public $cost = []; // coins and or resources
    public $resources = []; // and their optional costs
    public $military = 0;
    public $victoryPoints = 0;
    public $coins = 0; // coins as a reward, not cost
    public $scientificSymbol = 0;
    public $playEffects = [];
    public $endEffects = [];

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @param array $cost
     * @return static
     */
    public function setCost($cost) {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @param array $resources
     * @return static
     */
    public function setResources($resources) {
        $this->resources = $resources;
        return $this;
    }

    /**
     * @param int $military
     * @return static
     */
    public function setMilitary($military) {
        $this->military = $military;
        return $this;
    }

    /**
     * @param int $victoryPoints
     * @return static
     */
    public function setVictoryPoints(int $victoryPoints) {
        $this->victoryPoints = $victoryPoints;
        return $this;
    }

    /**
     * @param int $coins
     * @return static
     */
    public function setCoins(int $coins) {
        $this->coins = $coins;
        return $this;
    }

    /**
     * @param int $scientificSymbol
     * @return static
     */
    public function setScientificSymbol(int $scientificSymbol) {
        $this->scientificSymbol = $scientificSymbol;
        return $this;
    }

}