<?php

namespace SWD;

class Building extends Item {

    public $age;
    public $type;
    public $chain = null; // coins and or resources
    public $scienceSymbol = null; // coins and or resources
    public $fixedPriceResources = [];
    public $linkedBuilding = 0;
    public $coinsPerBuildingOfType = [];
    public $coinsPerWonder = 0;

    public function __construct($id, $age, $name, $type) {
        $this->age = $age;
        $this->type = $type;
        parent::__construct($id, $name);
    }

    /**
     * @param array $fixedPriceResources
     * @return static
     */
    public function setFixedPriceResources(array $fixedPriceResources) {
        $this->fixedPriceResources = $fixedPriceResources;
        return $this;
    }

    /**
     * @param int $linkedBuilding
     * @return static
     */
    public function setLinkedBuilding(int $linkedBuilding) {
        $this->linkedBuilding = $linkedBuilding;
        return $this;
    }

    /**
     * @param array $coinsNowPerBuildingOfType
     * @return static
     */
    public function setCoinsPerBuildingOfType(string $type, int $coins) {
        $this->coinsNowPerBuildingOfType = [$type => $coins];
        return $this;
    }

    /**
     * @param int $coinsPerWonder
     * @return static
     */
    public function setCoinsPerWonder(int $coinsPerWonder) {
        $this->coinsPerWonder = $coinsPerWonder;
        return $this;
    }

}