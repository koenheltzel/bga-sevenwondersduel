<?php

namespace SWD;

class Buildings implements \ArrayAccess, \Iterator {

    private $position = 0;
    /**
     * @var Building[] array
     */
    private $array = array();

    public static function createByBuildingIds($buildingIds) {
        $buildings = new Buildings();
        foreach($buildingIds as $buildingId) {
            $buildings[] = Building::get($buildingId);
        }
        return $buildings;
    }

    public function __construct($buildings = []) {
        $this->array = $buildings;
    }

    // Required ArrayAccess functions:

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    // Required Iterator functions:

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->array[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->array[$this->position]);
    }

    /**
     * @param $types
     * @return Buildings
     */
    public function filterByTypes($types) {
        $buildings = new Buildings();
        foreach ($this->array as $building) {
            if (in_array($building->type, $types)) {
                $buildings[] = $building;
            }
        }
        return $buildings;
    }
}