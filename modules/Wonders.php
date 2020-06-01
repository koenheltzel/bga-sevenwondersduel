<?php

namespace SWD;

/**
 * @property Wonder[] $array
 */
class Wonders extends Collection {

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

}