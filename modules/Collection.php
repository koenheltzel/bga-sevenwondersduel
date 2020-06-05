<?php

namespace SWD;

class Collection implements \ArrayAccess, \Iterator {

    private $position = 0;
    /**
     * @var Item[] array
     */
    public $array = array();

    public function getDeckCards() {
        $cards = [];
        foreach ($this->array as $item) {
            $cards[] = [
                'type' => $item->name,
                'type_arg' => $item->id,
                'nbr' => 1
            ];
        }
        return $cards;
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
}