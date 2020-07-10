<?php

function arrayWithPropertyAsKeys($array, $property, $subArray = false, $unsetProperty = false) {
    $new = [];
    foreach ($array as $value) {
        $tmpValue = $value;
        if ($unsetProperty) {
            unset($tmpValue[$property]);
        }
        if ($subArray) {
            if (!isset($new[$value[$property]])) $new[$value[$property]] = [];
            $new[$value[$property]][] = $tmpValue;
        }
        else {
            $new[$value[$property]] = $tmpValue;
        }
    }
    return $new;
}