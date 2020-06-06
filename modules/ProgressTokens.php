<?php

namespace SWD;

/**
 * @property ProgressToken[] $array
 */
class ProgressTokens extends Collection {

    public static function createByWonderIds($progressTokenIds) {
        $progressTokens = new ProgressTokens();
        foreach($progressTokenIds as $progressTokenId) {
            $progressTokens[] = ProgressToken::get($progressTokenId);
        }
        return $progressTokens;
    }

    public function __construct($progressTokens = []) {
        $this->array = $progressTokens;
    }

}