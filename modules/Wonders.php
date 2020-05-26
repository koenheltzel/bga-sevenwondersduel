<?php

namespace SWD;

class Wonders implements \ArrayAccess, \Iterator {

    /**
     * @var Wonder[] array
     */
    private $array = array();

    public static function createByWonderIds($wonderIds) {
        $wonders = new Wonders();
        foreach($wonderIds as $wonderId) {
            $wonders[] = Wonder::get($wonderId);
        }
        return $wonders;
    }

    public function __construct($wonders = []) {
        $this->array = $wonders;
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