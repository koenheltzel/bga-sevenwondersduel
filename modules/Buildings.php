<?php

namespace SWD;

/**
 * @property Building[] $array
 */
class Buildings extends Collection {

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

    /**
     * @param $types
     * @return Buildings
     */
    public function filterByAge($age) {
        $buildings = new Buildings();
        foreach ($this->array as $building) {
            if ($building->age == $age) {
                $buildings[] = $building;
            }
        }
        return $buildings;
    }
}