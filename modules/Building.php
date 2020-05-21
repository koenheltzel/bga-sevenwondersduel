<?php

namespace SWD;

class Building extends Item {

    public $age;
    public $type;
    public $chain = null; // coins and or resources

    public function __construct($id, $age, $name, $type) {
        $this->age = $age;
        $this->type = $type;
        parent::__construct($id, $name);
    }

}