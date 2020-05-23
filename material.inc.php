<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * SevenWondersDuel game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/

use SWD\Building;
use SWD\Wonder;

define('COINS', 'COINS');
define('CLAY', 'CLAY');
define('WOOD', 'WOOD');
define('STONE', 'STONE');
define('GLASS', 'GLASS');
define('PAPYRUS', 'PAPYRUS');

define('TYPE_BROWN', 'TYPE_BROWN');
define('TYPE_GREY', 'TYPE_GREY');
define('TYPE_BLUE', 'TYPE_BLUE');
define('TYPE_GREEN', 'TYPE_GREEN');
define('TYPE_YELLOW', 'TYPE_YELLOW');
define('TYPE_RED', 'TYPE_RED');
define('TYPE_PURPLE', 'TYPE_PURPLE');

$id = 1;

// __        __              _
// \ \      / /__  _ __   __| | ___ _ __ ___
//  \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__/ __|
//   \ V  V / (_) | | | | (_| |  __/ |  \__ \
//    \_/\_/ \___/|_| |_|\__,_|\___|_|  |___/

$wonders = [];
$wonders[] = (new Wonder($id++, clienttranslate("The Appian Way")))
    ->setCost([STONE => 2, CLAY => 2, PAPYRUS => 1])
    ->setCoins(3);

$wonders[] = (new Wonder($id++, clienttranslate("Circus Maximus")))
    ->setCost([STONE => 2, WOOD => 1, GLASS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Colossus")))
    ->setCost([CLAY => 3, GLASS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Great Library")))
    ->setCost([WOOD => 3, GLASS => 1, PAPYRUS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Great Lighthouse")))
    ->setCost([WOOD => 1, STONE => 1, PAPYRUS => 2]);

$wonders[] = (new Wonder($id++, clienttranslate("The Hanging Gardens")))
    ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1])
    ->setCoins(6);

$wonders[] = (new Wonder($id++, clienttranslate("The Mausoleum")))
    ->setCost([CLAY => 2, GLASS => 2, PAPYRUS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("Piraeus")))
    ->setCost([WOOD => 2, STONE => 1, CLAY => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Pyramids")))
    ->setCost([STONE => 3, PAPYRUS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Sphinx")))
    ->setCost([STONE => 1, CLAY => 1, GLASS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Statue of Zeus")))
    ->setCost([STONE => 1, WOOD => 1, CLAY => 2, PAPYRUS => 2]);

$wonders[] = (new Wonder($id++, clienttranslate("The Temple of Artemis")))
    ->setCost([WOOD => 1, STONE => 1, GLASS => 1, PAPYRUS => 1])
    ->setCoins(12);

//     _                  ___
//    / \   __ _  ___    |_ _|
//   / _ \ / _` |/ _ \    | |
//  / ___ \ (_| |  __/    | |
// /_/   \_\__, |\___|   |___|
//         |___/

$age1 = [];

$age1[] = (new Building($id++, 1, clienttranslate("Lumber Yard"), TYPE_BROWN))
    ->setResources([WOOD => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Logging Camp"), TYPE_BROWN))
    ->setCost([COINS => 1])
    ->setResources([WOOD => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Clay Pool"), TYPE_BROWN))
    ->setResources([CLAY => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Clay Pit"), TYPE_BROWN))
    ->setCost([COINS => 1])
    ->setResources([CLAY => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Quarry"), TYPE_BROWN))
    ->setResources([STONE => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Stone Pit"), TYPE_BROWN))
    ->setCost([COINS => 1])
    ->setResources([STONE => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Glassworks"), TYPE_GREY))
    ->setCost([COINS => 1])
    ->setResources([GLASS => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Press"), TYPE_GREY))
    ->setCost([COINS => 1])
    ->setResources([PAPYRUS => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Guard Tower"), TYPE_RED))
    ->setMilitary(1);

$age1[] = (new Building($id++, 1, clienttranslate("Workshop"), TYPE_GREEN))
    ->setCost([PAPYRUS => 1])
    ->setVictoryPoints(1)
    ->setScientificSymbol(5);

$age1[] = (new Building($id++, 1, clienttranslate("Apothecary"), TYPE_GREEN))
    ->setCost([GLASS => 1])
    ->setScientificSymbol(7);

$age1[] = (new Building($id++, 1, clienttranslate("Stone Reserve"), TYPE_YELLOW))
    ->setCost([COINS => 3])
    ->setFixedPriceResources([STONE => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Clay Reserve"), TYPE_YELLOW))
    ->setCost([COINS => 3])
    ->setFixedPriceResources([CLAY => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Wood Reserve"), TYPE_YELLOW))
    ->setCost([COINS => 3])
    ->setFixedPriceResources([WOOD => 1]);

$age1[] = (new Building($id++, 1, clienttranslate("Stable"), TYPE_RED))
    ->setCost([WOOD => 1])
    ->setMilitary(1);

$age1[] = (new Building($id++, 1, clienttranslate("Garrison"), TYPE_RED))
    ->setCost([CLAY => 1])
    ->setMilitary(1);

$age1[] = (new Building($id++, 1, clienttranslate("Palisade"), TYPE_RED))
    ->setCost([COINS => 2])
    ->setMilitary(1);

$age1[] = (new Building($id++, 1, clienttranslate("Scriptorium"), TYPE_GREEN))
    ->setCost([COINS => 2])
    ->setScientificSymbol(6);

$age1[] = (new Building($id++, 1, clienttranslate("Pharmacist"), TYPE_GREEN))
    ->setCost([COINS => 2])
    ->setScientificSymbol(4);

$age1[] = (new Building($id++, 1, clienttranslate("Theater"), TYPE_BLUE))
    ->setVictoryPoints(3);

$age1[] = (new Building($id++, 1, clienttranslate("Altar"), TYPE_BLUE))
    ->setVictoryPoints(3);

$age1[] = (new Building($id++, 1, clienttranslate("Baths"), TYPE_BLUE))
    ->setCost([STONE => 1])
    ->setVictoryPoints(3);

$age1[] = (new Building($id++, 1, clienttranslate("Tavern"), TYPE_YELLOW))
    ->setCoins(4)
    ->setFixedPriceResources([WOOD => 1]);

//     _                  ___ ___
//    / \   __ _  ___    |_ _|_ _|
//   / _ \ / _` |/ _ \    | | | |
//  / ___ \ (_| |  __/    | | | |
// /_/   \_\__, |\___|   |___|___|
//         |___/

$age2 = [];

$age2[] = (new Building($id++, 2, clienttranslate("Sawmill"), TYPE_BROWN))
    ->setCost([COINS => 2])
    ->setResources([WOOD => 2]);

$age2[] = (new Building($id++, 2, clienttranslate("Brickyard"), TYPE_BROWN))
    ->setCost([COINS => 2])
    ->setResources([CLAY => 2]);

$age2[] = (new Building($id++, 2, clienttranslate("Brickyard"), TYPE_BROWN))
    ->setCost([COINS => 2])
    ->setResources([WOOD => 2]);

$age2[] = (new Building($id++, 2, clienttranslate("Shelf Quarry"), TYPE_BROWN))
    ->setCost([COINS => 2])
    ->setResources([STONE => 2]);

$age2[] = (new Building($id++, 2, clienttranslate("Glass-Blower"), TYPE_GREY))
    ->setResources([GLASS => 1]);

$age2[] = (new Building($id++, 2, clienttranslate("Drying Room"), TYPE_GREY))
    ->setResources([PAPYRUS => 1]);

$age2[] = (new Building($id++, 2, clienttranslate("Walls"), TYPE_RED))
    ->setCost([STONE => 2])
    ->setMilitary(2);

$age2[] = (new Building($id++, 2, clienttranslate("Forum"), TYPE_YELLOW))
    ->setCost([COINS => 3, CLAY => 2])
    ->setResources([/* OR */ [GLASS => 1, PAPYRUS => 1]]);

$age2[] = (new Building($id++, 2, clienttranslate("Caravansery"), TYPE_YELLOW))
    ->setCost([COINS => 2, GLASS => 1, PAPYRUS => 1])
    ->setResources([/* OR */ [WOOD => 1, CLAY => 1, STONE => 1]]);

$age2[] = (new Building($id++, 2, clienttranslate("Customs House"), TYPE_YELLOW))
    ->setCost([COINS => 4])
    ->setFixedPriceResources([PAPYRUS => 1, GLASS => 1]);

$age2[] = (new Building($id++, 2, clienttranslate("Courthouse"), TYPE_BLUE))
    ->setCost([WOOD => 2, GLASS => 1])
    ->setVictoryPoints(5);

$age2[] = (new Building($id++, 2, clienttranslate("Horse Breeders"), TYPE_RED))
    ->setCost([CLAY => 1, WOOD => 1])
    ->setLinkedBuilding(27) // Stable
    ->setMilitary(1);

$age2[] = (new Building($id++, 2, clienttranslate("Barracks"), TYPE_RED))
    ->setCost([COINS => 3])
    ->setLinkedBuilding(28) // Garrison
    ->setMilitary(1);

$age2[] = (new Building($id++, 2, clienttranslate("Archery Range"), TYPE_RED))
    ->setCost([STONE => 1, WOOD => 1, PAPYRUS => 1])
    ->setMilitary(2);

$age2[] = (new Building($id++, 2, clienttranslate("Parade Ground"), TYPE_RED))
    ->setCost([CLAY => 2, GLASS => 1])
    ->setMilitary(2);

$age2[] = (new Building($id++, 2, clienttranslate("Library"), TYPE_GREEN))
    ->setCost([STONE => 1, WOOD => 1, GLASS => 1])
    ->setLinkedBuilding(30) // Scriptorium
    ->setScientificSymbol(6)
    ->setVictoryPoints(2);

$age2[] = (new Building($id++, 2, clienttranslate("Dispensary"), TYPE_GREEN))
    ->setCost([CLAY => 2, STONE => 1])
    ->setLinkedBuilding(31) // Pharmacist
    ->setScientificSymbol(4)
    ->setVictoryPoints(2);

$age2[] = (new Building($id++, 2, clienttranslate("School"), TYPE_GREEN))
    ->setCost([WOOD => 1, PAPYRUS => 2])
    ->setScientificSymbol(7)
    ->setVictoryPoints(1);

$age2[] = (new Building($id++, 2, clienttranslate("Laboratory"), TYPE_GREEN))
    ->setCost([WOOD => 1, GLASS => 2])
    ->setScientificSymbol(5)
    ->setVictoryPoints(1);

$age2[] = (new Building($id++, 2, clienttranslate("Statue"), TYPE_BLUE))
    ->setCost([CLAY => 2])
    ->setLinkedBuilding(32) // Theater
    ->setVictoryPoints(4);

$age2[] = (new Building($id++, 2, clienttranslate("Temple"), TYPE_BLUE))
    ->setCost([WOOD => 1, PAPYRUS => 1])
    ->setLinkedBuilding(33) // Altar
    ->setVictoryPoints(4);

$age2[] = (new Building($id++, 2, clienttranslate("Aqueduct"), TYPE_BLUE))
    ->setCost([STONE => 3])
    ->setLinkedBuilding(34) // Baths
    ->setVictoryPoints(5);

$age2[] = (new Building($id++, 2, clienttranslate("Rostrum"), TYPE_BLUE))
    ->setCost([STONE => 1, WOOD => 1])
    ->setVictoryPoints(4);

$age2[] = (new Building($id++, 2, clienttranslate("Brewery"), TYPE_YELLOW))
    ->setCoins(6);

//     _                  ___ ___ ___
//    / \   __ _  ___    |_ _|_ _|_ _|
//   / _ \ / _` |/ _ \    | | | | | |
//  / ___ \ (_| |  __/    | | | | | |
// /_/   \_\__, |\___|   |___|___|___|
//         |___/

$age3[] = (new Building($id++, 3, clienttranslate("Arsenal"), TYPE_RED))
    ->setCost([CLAY => 3, WOOD => 2])
    ->setMilitary(3);

$age3[] = (new Building($id++, 3, clienttranslate("Pretorium"), TYPE_RED))
    ->setCost([COINS => 8])
    ->setMilitary(3);

$age3[] = (new Building($id++, 3, clienttranslate("Academy"), TYPE_GREEN))
    ->setCost([STONE => 1, WOOD => 1, GLASS => 2])
    ->setScientificSymbol(3)
    ->setVictoryPoints(3);

$age3[] = (new Building($id++, 3, clienttranslate("Study"), TYPE_GREEN))
    ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1])
    ->setScientificSymbol(3)
    ->setVictoryPoints(3);

$age3[] = (new Building($id++, 3, clienttranslate("Chamber Of Commerce"), TYPE_YELLOW))
    ->setCost([PAPYRUS => 2])
    ->setCoinsPerBuildingOfType(TYPE_GREY, 3)
    ->setVictoryPoints(3);

$age3[] = (new Building($id++, 3, clienttranslate("Port"), TYPE_YELLOW))
    ->setCost([WOOD => 1, GLASS => 1, PAPYRUS => 1])
    ->setCoinsPerBuildingOfType(TYPE_BROWN, 2)
    ->setVictoryPoints(3);

$age3[] = (new Building($id++, 3, clienttranslate("Armory"), TYPE_YELLOW))
    ->setCost([STONE => 2, GLASS => 1])
    ->setCoinsPerBuildingOfType(TYPE_RED, 1)
    ->setVictoryPoints(3);



//   ____       _ _     _
//  / ___|_   _(_) | __| |___
// | |  _| | | | | |/ _` / __|
// | |_| | |_| | | | (_| \__ \
//  \____|\__,_|_|_|\__,_|___/



//  ____                                      _____     _
// |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __  ___
// | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \/ __|
// |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | \__ \
// |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|___/
//                  |___/
