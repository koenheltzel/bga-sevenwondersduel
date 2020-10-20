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
    public function filterByScientificSymbol($symbol) {
        $buildings = new Buildings();
        foreach ($this->array as $building) {
            if ($building->scientificSymbol == $symbol) {
                $buildings[] = $building;
            }
        }
        return $buildings;
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

    public static function getAgeCardsFromBoxByAge($upToAge) {
        $ids = [];
        $ids[1] = [1, 23];
        $ids[2] = [24, 46];
        $ids[3] = [47, 66];
        $results = self::getCollectionFromDB( "SELECT * FROM building WHERE card_location = 'box' AND card_id >= 1 AND card_id <= 66 ORDER BY card_id" );
        $output = [];
        foreach($results as $id => $card) {
            for ($age = 1; $age <= $upToAge; $age++) {
                if ($id >= $ids[$age][0] && $id <= $ids[$age][1]) {
                    $output[$age][] = $id;
                }
            }
        }
        return $output;
    }
}