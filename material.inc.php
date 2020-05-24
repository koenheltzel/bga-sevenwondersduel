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
$wonders[1] = (new Wonder(1, clienttranslate("The Appian Way")))
    ->setCost([STONE => 2, CLAY => 2, PAPYRUS => 1])
    ->setCoins(3);

$wonders[2] = (new Wonder(2, clienttranslate("Circus Maximus")))
    ->setCost([STONE => 2, WOOD => 1, GLASS => 1]);

$wonders[3] = (new Wonder(3, clienttranslate("The Colossus")))
    ->setCost([CLAY => 3, GLASS => 1]);

$wonders[4] = (new Wonder(4, clienttranslate("The Great Library")))
    ->setCost([WOOD => 3, GLASS => 1, PAPYRUS => 1]);

$wonders[5] = (new Wonder(5, clienttranslate("The Great Lighthouse")))
    ->setCost([WOOD => 1, STONE => 1, PAPYRUS => 2])
    ->setResourceChoice([WOOD, STONE, CLAY]);

$wonders[6] = (new Wonder(6, clienttranslate("The Hanging Gardens")))
    ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1])
    ->setCoins(6);

$wonders[7] = (new Wonder(7, clienttranslate("The Mausoleum")))
    ->setCost([CLAY => 2, GLASS => 2, PAPYRUS => 1]);

$wonders[8] = (new Wonder(8, clienttranslate("Piraeus")))
    ->setCost([WOOD => 2, STONE => 1, CLAY => 1])
    ->setResourceChoice([PAPYRUS, GLASS]);

$wonders[9] = (new Wonder(9, clienttranslate("The Pyramids")))
    ->setCost([STONE => 3, PAPYRUS => 1]);

$wonders[10] = (new Wonder(10, clienttranslate("The Sphinx")))
    ->setCost([STONE => 1, CLAY => 1, GLASS => 1]);

$wonders[11] = (new Wonder(11, clienttranslate("The Statue of Zeus")))
    ->setCost([STONE => 1, WOOD => 1, CLAY => 2, PAPYRUS => 2]);

$wonders[12] = (new Wonder(12, clienttranslate("The Temple of Artemis")))
    ->setCost([WOOD => 1, STONE => 1, GLASS => 1, PAPYRUS => 1])
    ->setCoins(12);

//     _                  ___
//    / \   __ _  ___    |_ _|
//   / _ \ / _` |/ _ \    | |
//  / ___ \ (_| |  __/    | |
// /_/   \_\__, |\___|   |___|
//         |___/

$age1 = [];

$age1[13] = (new Building(13, 1, clienttranslate("Lumber Yard"), TYPE_BROWN))
    ->setResources([WOOD => 1]);

$age1[14] = (new Building(14, 1, clienttranslate("Logging Camp"), TYPE_BROWN))
    ->setCost([COINS => 1])
    ->setResources([WOOD => 1]);

$age1[15] = (new Building(15, 1, clienttranslate("Clay Pool"), TYPE_BROWN))
    ->setResources([CLAY => 1]);

$age1[16] = (new Building(16, 1, clienttranslate("Clay Pit"), TYPE_BROWN))
    ->setCost([COINS => 1])
    ->setResources([CLAY => 1]);

$age1[17] = (new Building(17, 1, clienttranslate("Quarry"), TYPE_BROWN))
    ->setResources([STONE => 1]);

$age1[18] = (new Building(18, 1, clienttranslate("Stone Pit"), TYPE_BROWN))
    ->setCost([COINS => 1])
    ->setResources([STONE => 1]);

$age1[19] = (new Building(19, 1, clienttranslate("Glassworks"), TYPE_GREY))
    ->setCost([COINS => 1])
    ->setResources([GLASS => 1]);

$age1[20] = (new Building(20, 1, clienttranslate("Press"), TYPE_GREY))
    ->setCost([COINS => 1])
    ->setResources([PAPYRUS => 1]);

$age1[21] = (new Building(21, 1, clienttranslate("Guard Tower"), TYPE_RED))
    ->setMilitary(1);

$age1[22] = (new Building(22, 1, clienttranslate("Workshop"), TYPE_GREEN))
    ->setCost([PAPYRUS => 1])
    ->setVictoryPoints(1)
    ->setScientificSymbol(5);

$age1[23] = (new Building(23, 1, clienttranslate("Apothecary"), TYPE_GREEN))
    ->setCost([GLASS => 1])
    ->setScientificSymbol(7);

$age1[24] = (new Building(24, 1, clienttranslate("Stone Reserve"), TYPE_YELLOW))
    ->setCost([COINS => 3])
    ->setFixedPriceResources([STONE => 1]);

$age1[25] = (new Building(25, 1, clienttranslate("Clay Reserve"), TYPE_YELLOW))
    ->setCost([COINS => 3])
    ->setFixedPriceResources([CLAY => 1]);

$age1[26] = (new Building(26, 1, clienttranslate("Wood Reserve"), TYPE_YELLOW))
    ->setCost([COINS => 3])
    ->setFixedPriceResources([WOOD => 1]);

$age1[27] = (new Building(27, 1, clienttranslate("Stable"), TYPE_RED))
    ->setCost([WOOD => 1])
    ->setMilitary(1);

$age1[28] = (new Building(28, 1, clienttranslate("Garrison"), TYPE_RED))
    ->setCost([CLAY => 1])
    ->setMilitary(1);

$age1[29] = (new Building(29, 1, clienttranslate("Palisade"), TYPE_RED))
    ->setCost([COINS => 2])
    ->setMilitary(1);

$age1[30] = (new Building(30, 1, clienttranslate("Scriptorium"), TYPE_GREEN))
    ->setCost([COINS => 2])
    ->setScientificSymbol(6);

$age1[31] = (new Building(31, 1, clienttranslate("Pharmacist"), TYPE_GREEN))
    ->setCost([COINS => 2])
    ->setScientificSymbol(4);

$age1[32] = (new Building(32, 1, clienttranslate("Theater"), TYPE_BLUE))
    ->setVictoryPoints(3);

$age1[33] = (new Building(33, 1, clienttranslate("Altar"), TYPE_BLUE))
    ->setVictoryPoints(3);

$age1[34] = (new Building(34, 1, clienttranslate("Baths"), TYPE_BLUE))
    ->setCost([STONE => 1])
    ->setVictoryPoints(3);

$age1[35] = (new Building(35, 1, clienttranslate("Tavern"), TYPE_YELLOW))
    ->setCoins(4)
    ->setFixedPriceResources([WOOD => 1]);

//     _                  ___ ___
//    / \   __ _  ___    |_ _|_ _|
//   / _ \ / _` |/ _ \    | | | |
//  / ___ \ (_| |  __/    | | | |
// /_/   \_\__, |\___|   |___|___|
//         |___/

$age2 = [];

$age2[36] = (new Building(36, 2, clienttranslate("Sawmill"), TYPE_BROWN))
    ->setCost([COINS => 2])
    ->setResources([WOOD => 2]);

$age2[37] = (new Building(37, 2, clienttranslate("Brickyard"), TYPE_BROWN))
    ->setCost([COINS => 2])
    ->setResources([CLAY => 2]);

$age2[38] = (new Building(38, 2, clienttranslate("Shelf Quarry"), TYPE_BROWN))
    ->setCost([COINS => 2])
    ->setResources([STONE => 2]);

$age2[39] = (new Building(39, 2, clienttranslate("Glass-Blower"), TYPE_GREY))
    ->setResources([GLASS => 1]);

$age2[40] = (new Building(40, 2, clienttranslate("Drying Room"), TYPE_GREY))
    ->setResources([PAPYRUS => 1]);

$age2[41] = (new Building(41, 2, clienttranslate("Walls"), TYPE_RED))
    ->setCost([STONE => 2])
    ->setMilitary(2);

$age2[42] = (new Building(42, 2, clienttranslate("Forum"), TYPE_YELLOW))
    ->setCost([COINS => 3, CLAY => 2])
    ->setResourceChoice([GLASS, PAPYRUS]);

$age2[43] = (new Building(43, 2, clienttranslate("Caravansery"), TYPE_YELLOW))
    ->setCost([COINS => 2, GLASS => 1, PAPYRUS => 1])
    ->setResourceChoice([WOOD, CLAY, STONE]);

$age2[44] = (new Building(44, 2, clienttranslate("Customs House"), TYPE_YELLOW))
    ->setCost([COINS => 4])
    ->setFixedPriceResources([PAPYRUS => 1, GLASS => 1]);

$age2[45] = (new Building(45, 2, clienttranslate("Courthouse"), TYPE_BLUE))
    ->setCost([WOOD => 2, GLASS => 1])
    ->setVictoryPoints(5);

$age2[46] = (new Building(46, 2, clienttranslate("Horse Breeders"), TYPE_RED))
    ->setCost([CLAY => 1, WOOD => 1])
    ->setLinkedBuilding(27) // Stable
    ->setMilitary(1);

$age2[47] = (new Building(47, 2, clienttranslate("Barracks"), TYPE_RED))
    ->setCost([COINS => 3])
    ->setLinkedBuilding(28) // Garrison
    ->setMilitary(1);

$age2[48] = (new Building(48, 2, clienttranslate("Archery Range"), TYPE_RED))
    ->setCost([STONE => 1, WOOD => 1, PAPYRUS => 1])
    ->setMilitary(2);

$age2[49] = (new Building(49, 2, clienttranslate("Parade Ground"), TYPE_RED))
    ->setCost([CLAY => 2, GLASS => 1])
    ->setMilitary(2);

$age2[50] = (new Building(50, 2, clienttranslate("Library"), TYPE_GREEN))
    ->setCost([STONE => 1, WOOD => 1, GLASS => 1])
    ->setLinkedBuilding(30) // Scriptorium
    ->setScientificSymbol(6)
    ->setVictoryPoints(2);

$age2[51] = (new Building(51, 2, clienttranslate("Dispensary"), TYPE_GREEN))
    ->setCost([CLAY => 2, STONE => 1])
    ->setLinkedBuilding(31) // Pharmacist
    ->setScientificSymbol(4)
    ->setVictoryPoints(2);

$age2[52] = (new Building(52, 2, clienttranslate("School"), TYPE_GREEN))
    ->setCost([WOOD => 1, PAPYRUS => 2])
    ->setScientificSymbol(7)
    ->setVictoryPoints(1);

$age2[53] = (new Building(53, 2, clienttranslate("Laboratory"), TYPE_GREEN))
    ->setCost([WOOD => 1, GLASS => 2])
    ->setScientificSymbol(5)
    ->setVictoryPoints(1);

$age2[54] = (new Building(54, 2, clienttranslate("Statue"), TYPE_BLUE))
    ->setCost([CLAY => 2])
    ->setLinkedBuilding(32) // Theater
    ->setVictoryPoints(4);

$age2[55] = (new Building(55, 2, clienttranslate("Temple"), TYPE_BLUE))
    ->setCost([WOOD => 1, PAPYRUS => 1])
    ->setLinkedBuilding(33) // Altar
    ->setVictoryPoints(4);

$age2[56] = (new Building(56, 2, clienttranslate("Aqueduct"), TYPE_BLUE))
    ->setCost([STONE => 3])
    ->setLinkedBuilding(34) // Baths
    ->setVictoryPoints(5);

$age2[57] = (new Building(57, 2, clienttranslate("Rostrum"), TYPE_BLUE))
    ->setCost([STONE => 1, WOOD => 1])
    ->setVictoryPoints(4);

$age2[58] = (new Building(58, 2, clienttranslate("Brewery"), TYPE_YELLOW))
    ->setCoins(6);

//     _                  ___ ___ ___
//    / \   __ _  ___    |_ _|_ _|_ _|
//   / _ \ / _` |/ _ \    | | | | | |
//  / ___ \ (_| |  __/    | | | | | |
// /_/   \_\__, |\___|   |___|___|___|
//         |___/

$age3 = [];

$age3[59] = (new Building(59, 3, clienttranslate("Arsenal"), TYPE_RED))
    ->setCost([CLAY => 3, WOOD => 2])
    ->setMilitary(3);

$age3[60] = (new Building(60, 3, clienttranslate("Pretorium"), TYPE_RED))
    ->setCost([COINS => 8])
    ->setMilitary(3);

$age3[61] = (new Building(61, 3, clienttranslate("Academy"), TYPE_GREEN))
    ->setCost([STONE => 1, WOOD => 1, GLASS => 2])
    ->setScientificSymbol(3)
    ->setVictoryPoints(3);

$age3[62] = (new Building(62, 3, clienttranslate("Study"), TYPE_GREEN))
    ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1])
    ->setScientificSymbol(3)
    ->setVictoryPoints(3);

$age3[63] = (new Building(63, 3, clienttranslate("Chamber Of Commerce"), TYPE_YELLOW))
    ->setCost([PAPYRUS => 2])
    ->setCoinsPerBuildingOfType(TYPE_GREY, 3)
    ->setVictoryPoints(3);

$age3[64] = (new Building(64, 3, clienttranslate("Port"), TYPE_YELLOW))
    ->setCost([WOOD => 1, GLASS => 1, PAPYRUS => 1])
    ->setCoinsPerBuildingOfType(TYPE_BROWN, 2)
    ->setVictoryPoints(3);

$age3[65] = (new Building(65, 3, clienttranslate("Armory"), TYPE_YELLOW))
    ->setCost([STONE => 2, GLASS => 1])
    ->setCoinsPerBuildingOfType(TYPE_RED, 1)
    ->setVictoryPoints(3);

$age3[66] = (new Building(66, 3, clienttranslate("Palace"), TYPE_BLUE))
    ->setCost([CLAY => 1, STONE => 1, WOOD => 1, GLASS => 2])
    ->setVictoryPoints(7);

$age3[67] = (new Building(67, 3, clienttranslate("Town Hall"), TYPE_BLUE))
    ->setCost([STONE => 3, WOOD => 2])
    ->setVictoryPoints(7);

$age3[68] = (new Building(68, 3, clienttranslate("Obelisk"), TYPE_BLUE))
    ->setCost([STONE => 2, GLASS => 1])
    ->setVictoryPoints(5);

$age3[69] = (new Building(69, 3, clienttranslate("Fortifications"), TYPE_RED))
    ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
    ->setLinkedBuilding(29) // Palisade
    ->setMilitary(2);

$age3[70] = (new Building(70, 3, clienttranslate("Siege Workshop"), TYPE_RED))
    ->setCost([WOOD => 3, GLASS => 1])
    ->setLinkedBuilding(48) // Archery Range
    ->setMilitary(2);

$age3[71] = (new Building(71, 3, clienttranslate("Circus"), TYPE_RED))
    ->setCost([CLAY => 2, STONE => 2])
    ->setLinkedBuilding(49) // Parade Ground
    ->setMilitary(2);

$age3[72] = (new Building(72, 3, clienttranslate("University"), TYPE_GREEN))
    ->setCost([CLAY => 1, GLASS => 1, PAPYRUS => 1])
    ->setLinkedBuilding(52) // School
    ->setScientificSymbol(1)
    ->setVictoryPoints(2);

$age3[73] = (new Building(73, 3, clienttranslate("Observatory"), TYPE_GREEN))
    ->setCost([STONE => 1, PAPYRUS => 2])
    ->setLinkedBuilding(53) // Laboratory
    ->setScientificSymbol(1)
    ->setVictoryPoints(2);

$age3[74] = (new Building(74, 3, clienttranslate("Gardens"), TYPE_BLUE))
    ->setCost([CLAY => 2, WOOD => 2])
    ->setLinkedBuilding(54) // Statue
    ->setVictoryPoints(6);

$age3[75] = (new Building(75, 3, clienttranslate("Pantheon"), TYPE_BLUE))
    ->setCost([CLAY => 1, WOOD => 1, PAPYRUS => 2])
    ->setLinkedBuilding(55) // Temple
    ->setVictoryPoints(6);

$age3[76] = (new Building(76, 3, clienttranslate("Senate"), TYPE_BLUE))
    ->setCost([CLAY => 2, STONE => 1, PAPYRUS => 1])
    ->setLinkedBuilding(57) // Rostrum
    ->setVictoryPoints(5);

$age3[77] = (new Building(77, 3, clienttranslate("Lighthouse"), TYPE_YELLOW))
    ->setCost([CLAY => 2, GLASS => 1])
    ->setLinkedBuilding(35) // Tavern
    ->setCoinsPerBuildingOfType(TYPE_YELLOW, 1)
    ->setVictoryPoints(3);

$age3[78] = (new Building(78, 3, clienttranslate("Arena"), TYPE_YELLOW))
    ->setCost([CLAY => 1, STONE => 1, WOOD => 1])
    ->setLinkedBuilding(58) // Brewery
    ->setCoinsPerWonder(2)
    ->setVictoryPoints(3);



//   ____       _ _     _
//  / ___|_   _(_) | __| |___
// | |  _| | | | | |/ _` / __|
// | |_| | |_| | | | (_| \__ \
//  \____|\__,_|_|_|\__,_|___/

$guilds = [];

$guilds[79] = (new Building(79, 4, clienttranslate("Merchants Guild"), TYPE_PURPLE))
    ->setCost([CLAY => 1, WOOD => 1, GLASS => 1, PAPYRUS => 1])
    ->setGuildRewardBuildingTypes([TYPE_YELLOW]);

$guilds[80] = (new Building(80, 4, clienttranslate("Shipowners Guild"), TYPE_PURPLE))
    ->setCost([CLAY => 1, STONE => 1, GLASS => 1, PAPYRUS => 1])
    ->setGuildRewardBuildingTypes([TYPE_BROWN, TYPE_GREY]);

$guilds[81] = (new Building(81, 4, clienttranslate("Builders Guild"), TYPE_PURPLE))
    ->setCost([STONE => 2, CLAY => 1, WOOD => 1, GLASS => 1])
    ->setGuildRewardWonders(true);

$guilds[82] = (new Building(82, 4, clienttranslate("Magistrates Guild"), TYPE_PURPLE))
    ->setCost([WOOD => 2, CLAY => 1, PAPYRUS => 1])
    ->setGuildRewardBuildingTypes([TYPE_BLUE]);

$guilds[83] = (new Building(83, 4, clienttranslate("Scientists Guild"), TYPE_PURPLE))
    ->setCost([CLAY => 2, WOOD => 2])
    ->setGuildRewardBuildingTypes([TYPE_GREEN]);

$guilds[84] = (new Building(84, 4, clienttranslate("Moneylenders Guild"), TYPE_PURPLE))
    ->setCost([STONE => 2, WOOD => 2])
    ->setGuildRewardCoinTriplets(true);

$guilds[85] = (new Building(85, 4, clienttranslate("Tacticians Guild"), TYPE_PURPLE))
    ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
    ->setGuildRewardBuildingTypes([TYPE_RED]);

//  ____                                      _____     _
// |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __  ___
// | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \/ __|
// |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | \__ \
// |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|___/
//                  |___/
