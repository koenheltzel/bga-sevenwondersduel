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
use SWD\ProgressToken;

//SevenWondersDuel::dump(time(), 'TWICE?');

// This code get's executed (included?) twice for some reason. Defining constants throws a notice, so check if they are already set.
if (!defined('COINS')) {
    define('COINS', 'Coin(s)');
    define('CLAY', 'Clay');
    define('WOOD', 'Wood');
    define('STONE', 'Stone');
    define('GLASS', 'Glass');
    define('PAPYRUS', 'Papyrus');

    define('TYPE_BROWN', 'TYPE_BROWN');
    define('TYPE_GREY', 'TYPE_GREY');
    define('TYPE_BLUE', 'TYPE_BLUE');
    define('TYPE_GREEN', 'TYPE_GREEN');
    define('TYPE_YELLOW', 'TYPE_YELLOW');
    define('TYPE_RED', 'TYPE_RED');
    define('TYPE_PURPLE', 'TYPE_PURPLE');

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

    $age1[1] = (new Building(1, 1, clienttranslate("Lumber Yard"), TYPE_BROWN))
        ->setResources([WOOD => 1]);

    $age1[2] = (new Building(2, 1, clienttranslate("Stone Pit"), TYPE_BROWN))
        ->setCost([COINS => 1])
        ->setResources([STONE => 1]);

    $age1[3] = (new Building(3, 1, clienttranslate("Clay Pool"), TYPE_BROWN))
        ->setResources([CLAY => 1]);

    $age1[4] = (new Building(4, 1, clienttranslate("Logging Camp"), TYPE_BROWN))
        ->setCost([COINS => 1])
        ->setResources([WOOD => 1]);

    $age1[5] = (new Building(5, 1, clienttranslate("Quarry"), TYPE_BROWN))
        ->setResources([STONE => 1]);

    $age1[6] = (new Building(6, 1, clienttranslate("Clay Pit"), TYPE_BROWN))
        ->setCost([COINS => 1])
        ->setResources([CLAY => 1]);

    $age1[7] = (new Building(7, 1, clienttranslate("Glassworks"), TYPE_GREY))
        ->setCost([COINS => 1])
        ->setResources([GLASS => 1]);

    $age1[8] = (new Building(8, 1, clienttranslate("Press"), TYPE_GREY))
        ->setCost([COINS => 1])
        ->setResources([PAPYRUS => 1]);

    $age1[9] = (new Building(9, 1, clienttranslate("Stable"), TYPE_RED))
        ->setCost([WOOD => 1])
        ->setMilitary(1);

    $age1[10] = (new Building(10, 1, clienttranslate("Garrison"), TYPE_RED))
        ->setCost([CLAY => 1])
        ->setMilitary(1);

    $age1[11] = (new Building(11, 1, clienttranslate("Palisade"), TYPE_RED))
        ->setCost([COINS => 2])
        ->setMilitary(1);

    $age1[12] = (new Building(12, 1, clienttranslate("Guard Tower"), TYPE_RED))
        ->setMilitary(1);

    $age1[13] = (new Building(13, 1, clienttranslate("Scriptorium"), TYPE_GREEN))
        ->setCost([COINS => 2])
        ->setScientificSymbol(6);

    $age1[14] = (new Building(14, 1, clienttranslate("Workshop"), TYPE_GREEN))
        ->setCost([PAPYRUS => 1])
        ->setVictoryPoints(1)
        ->setScientificSymbol(5);

    $age1[15] = (new Building(15, 1, clienttranslate("Pharmacist"), TYPE_GREEN))
        ->setCost([COINS => 2])
        ->setScientificSymbol(4);

    $age1[16] = (new Building(16, 1, clienttranslate("Apothecary"), TYPE_GREEN))
        ->setCost([GLASS => 1])
        ->setScientificSymbol(7);

    $age1[17] = (new Building(17, 1, clienttranslate("Tavern"), TYPE_YELLOW))
        ->setCoins(4)
        ->setFixedPriceResources([WOOD => 1]);

    $age1[18] = (new Building(18, 1, clienttranslate("Stone Reserve"), TYPE_YELLOW))
        ->setCost([COINS => 3])
        ->setFixedPriceResources([STONE => 1]);

    $age1[19] = (new Building(19, 1, clienttranslate("Clay Reserve"), TYPE_YELLOW))
        ->setCost([COINS => 3])
        ->setFixedPriceResources([CLAY => 1]);

    $age1[20] = (new Building(20, 1, clienttranslate("Wood Reserve"), TYPE_YELLOW))
        ->setCost([COINS => 3])
        ->setFixedPriceResources([WOOD => 1]);

    $age1[21] = (new Building(21, 1, clienttranslate("Theater"), TYPE_BLUE))
        ->setVictoryPoints(3);

    $age1[22] = (new Building(22, 1, clienttranslate("Altar"), TYPE_BLUE))
        ->setVictoryPoints(3);

    $age1[23] = (new Building(23, 1, clienttranslate("Baths"), TYPE_BLUE))
        ->setCost([STONE => 1])
        ->setVictoryPoints(3);

//     _                  ___ ___
//    / \   __ _  ___    |_ _|_ _|
//   / _ \ / _` |/ _ \    | | | |
//  / ___ \ (_| |  __/    | | | |
// /_/   \_\__, |\___|   |___|___|
//         |___/

    $age2 = [];

    $age2[24] = (new Building(24, 2, clienttranslate("Sawmill"), TYPE_BROWN))
        ->setCost([COINS => 2])
        ->setResources([WOOD => 2]);

    $age2[25] = (new Building(25, 2, clienttranslate("Shelf Quarry"), TYPE_BROWN))
        ->setCost([COINS => 2])
        ->setResources([STONE => 2]);

    $age2[26] = (new Building(26, 2, clienttranslate("Brickyard"), TYPE_BROWN))
        ->setCost([COINS => 2])
        ->setResources([CLAY => 2]);

    $age2[27] = (new Building(27, 2, clienttranslate("Glass-Blower"), TYPE_GREY))
        ->setResources([GLASS => 1]);

    $age2[28] = (new Building(28, 2, clienttranslate("Drying Room"), TYPE_GREY))
        ->setResources([PAPYRUS => 1]);

    $age2[29] = (new Building(29, 2, clienttranslate("Horse Breeders"), TYPE_RED))
        ->setCost([CLAY => 1, WOOD => 1])
        ->setLinkedBuilding(9) // Stable
        ->setMilitary(1);

    $age2[30] = (new Building(30, 2, clienttranslate("Barracks"), TYPE_RED))
        ->setCost([COINS => 3])
        ->setLinkedBuilding(10) // Garrison
        ->setMilitary(1);

    $age2[31] = (new Building(31, 2, clienttranslate("Walls"), TYPE_RED))
        ->setCost([STONE => 2])
        ->setMilitary(2);

    $age2[32] = (new Building(32, 2, clienttranslate("Archery Range"), TYPE_RED))
        ->setCost([STONE => 1, WOOD => 1, PAPYRUS => 1])
        ->setMilitary(2);

    $age2[33] = (new Building(33, 2, clienttranslate("Parade Ground"), TYPE_RED))
        ->setCost([CLAY => 2, GLASS => 1])
        ->setMilitary(2);

    $age2[34] = (new Building(34, 2, clienttranslate("School"), TYPE_GREEN))
        ->setCost([WOOD => 1, PAPYRUS => 2])
        ->setScientificSymbol(7)
        ->setVictoryPoints(1);

    $age2[35] = (new Building(35, 2, clienttranslate("Laboratory"), TYPE_GREEN))
        ->setCost([WOOD => 1, GLASS => 2])
        ->setScientificSymbol(5)
        ->setVictoryPoints(1);

    $age2[36] = (new Building(36, 2, clienttranslate("Dispensary"), TYPE_GREEN))
        ->setCost([CLAY => 2, STONE => 1])
        ->setLinkedBuilding(15) // Pharmacist
        ->setScientificSymbol(4)
        ->setVictoryPoints(2);

    $age2[37] = (new Building(37, 2, clienttranslate("Library"), TYPE_GREEN))
        ->setCost([STONE => 1, WOOD => 1, GLASS => 1])
        ->setLinkedBuilding(13) // Scriptorium
        ->setScientificSymbol(6)
        ->setVictoryPoints(2);

    $age2[38] = (new Building(38, 2, clienttranslate("Brewery"), TYPE_YELLOW))
        ->setCoins(6);

    $age2[39] = (new Building(39, 2, clienttranslate("Forum"), TYPE_YELLOW))
        ->setCost([COINS => 3, CLAY => 2])
        ->setResourceChoice([GLASS, PAPYRUS]);

    $age2[40] = (new Building(40, 2, clienttranslate("Caravansery"), TYPE_YELLOW))
        ->setCost([COINS => 2, GLASS => 1, PAPYRUS => 1])
        ->setResourceChoice([WOOD, CLAY, STONE]);

    $age2[41] = (new Building(41, 2, clienttranslate("Customs House"), TYPE_YELLOW))
        ->setCost([COINS => 4])
        ->setFixedPriceResources([PAPYRUS => 1, GLASS => 1]);

    $age2[42] = (new Building(42, 2, clienttranslate("Temple"), TYPE_BLUE))
        ->setCost([WOOD => 1, PAPYRUS => 1])
        ->setLinkedBuilding(22) // Altar
        ->setVictoryPoints(4);

    $age2[43] = (new Building(43, 2, clienttranslate("Statue"), TYPE_BLUE))
        ->setCost([CLAY => 2])
        ->setLinkedBuilding(21) // Theater
        ->setVictoryPoints(4);

    $age2[44] = (new Building(44, 2, clienttranslate("Courthouse"), TYPE_BLUE))
        ->setCost([WOOD => 2, GLASS => 1])
        ->setVictoryPoints(5);

    $age2[45] = (new Building(45, 2, clienttranslate("Aqueduct"), TYPE_BLUE))
        ->setCost([STONE => 3])
        ->setLinkedBuilding(23) // Baths
        ->setVictoryPoints(5);

    $age2[46] = (new Building(46, 2, clienttranslate("Rostrum"), TYPE_BLUE))
        ->setCost([STONE => 1, WOOD => 1])
        ->setVictoryPoints(4);

//     _                  ___ ___ ___
//    / \   __ _  ___    |_ _|_ _|_ _|
//   / _ \ / _` |/ _ \    | | | | | |
//  / ___ \ (_| |  __/    | | | | | |
// /_/   \_\__, |\___|   |___|___|___|
//         |___/

    $age3 = [];

    $age3[47] = (new Building(47, 3, clienttranslate("Circus"), TYPE_RED))
        ->setCost([CLAY => 2, STONE => 2])
        ->setLinkedBuilding(33) // Parade Ground
        ->setMilitary(2);

    $age3[48] = (new Building(48, 3, clienttranslate("Arsenal"), TYPE_RED))
        ->setCost([CLAY => 3, WOOD => 2])
        ->setMilitary(3);

    $age3[49] = (new Building(49, 3, clienttranslate("Siege Workshop"), TYPE_RED))
        ->setCost([WOOD => 3, GLASS => 1])
        ->setLinkedBuilding(32) // Archery Range
        ->setMilitary(2);

    $age3[50] = (new Building(50, 3, clienttranslate("Fortifications"), TYPE_RED))
        ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
        ->setLinkedBuilding(11) // Palisade
        ->setMilitary(2);

    $age3[51] = (new Building(51, 3, clienttranslate("Pretorium"), TYPE_RED))
        ->setCost([COINS => 8])
        ->setMilitary(3);

    $age3[52] = (new Building(52, 3, clienttranslate("Academy"), TYPE_GREEN))
        ->setCost([STONE => 1, WOOD => 1, GLASS => 2])
        ->setScientificSymbol(3)
        ->setVictoryPoints(3);

    $age3[53] = (new Building(53, 3, clienttranslate("University"), TYPE_GREEN))
        ->setCost([CLAY => 1, GLASS => 1, PAPYRUS => 1])
        ->setLinkedBuilding(34) // School
        ->setScientificSymbol(1)
        ->setVictoryPoints(2);

    $age3[54] = (new Building(54, 3, clienttranslate("Study"), TYPE_GREEN))
        ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1])
        ->setScientificSymbol(3)
        ->setVictoryPoints(3);

    $age3[55] = (new Building(55, 3, clienttranslate("Observatory"), TYPE_GREEN))
        ->setCost([STONE => 1, PAPYRUS => 2])
        ->setLinkedBuilding(35) // Laboratory
        ->setScientificSymbol(1)
        ->setVictoryPoints(2);

    $age3[56] = (new Building(56, 3, clienttranslate("Arena"), TYPE_YELLOW))
        ->setCost([CLAY => 1, STONE => 1, WOOD => 1])
        ->setLinkedBuilding(38) // Brewery
        ->setCoinsPerWonder(2)
        ->setVictoryPoints(3);

    $age3[57] = (new Building(57, 3, clienttranslate("Chamber Of Commerce"), TYPE_YELLOW))
        ->setCost([PAPYRUS => 2])
        ->setCoinsPerBuildingOfType(TYPE_GREY, 3)
        ->setVictoryPoints(3);

    $age3[58] = (new Building(58, 3, clienttranslate("Port"), TYPE_YELLOW))
        ->setCost([WOOD => 1, GLASS => 1, PAPYRUS => 1])
        ->setCoinsPerBuildingOfType(TYPE_BROWN, 2)
        ->setVictoryPoints(3);

    $age3[59] = (new Building(59, 3, clienttranslate("Lighthouse"), TYPE_YELLOW))
        ->setCost([CLAY => 2, GLASS => 1])
        ->setLinkedBuilding(17) // Tavern
        ->setCoinsPerBuildingOfType(TYPE_YELLOW, 1)
        ->setVictoryPoints(3);

    $age3[60] = (new Building(60, 3, clienttranslate("Armory"), TYPE_YELLOW))
        ->setCost([STONE => 2, GLASS => 1])
        ->setCoinsPerBuildingOfType(TYPE_RED, 1)
        ->setVictoryPoints(3);

    $age3[61] = (new Building(61, 3, clienttranslate("Palace"), TYPE_BLUE))
        ->setCost([CLAY => 1, STONE => 1, WOOD => 1, GLASS => 2])
        ->setVictoryPoints(7);

    $age3[62] = (new Building(62, 3, clienttranslate("Gardens"), TYPE_BLUE))
        ->setCost([CLAY => 2, WOOD => 2])
        ->setLinkedBuilding(43) // Statue
        ->setVictoryPoints(6);

    $age3[63] = (new Building(63, 3, clienttranslate("Pantheon"), TYPE_BLUE))
        ->setCost([CLAY => 1, WOOD => 1, PAPYRUS => 2])
        ->setLinkedBuilding(42) // Temple
        ->setVictoryPoints(6);

    $age3[64] = (new Building(64, 3, clienttranslate("Town Hall"), TYPE_BLUE))
        ->setCost([STONE => 3, WOOD => 2])
        ->setVictoryPoints(7);

    $age3[65] = (new Building(65, 3, clienttranslate("Senate"), TYPE_BLUE))
        ->setCost([CLAY => 2, STONE => 1, PAPYRUS => 1])
        ->setLinkedBuilding(46) // Rostrum
        ->setVictoryPoints(5);

    $age3[66] = (new Building(66, 3, clienttranslate("Obelisk"), TYPE_BLUE))
        ->setCost([STONE => 2, GLASS => 1])
        ->setVictoryPoints(5);



//   ____       _ _     _
//  / ___|_   _(_) | __| |___
// | |  _| | | | | |/ _` / __|
// | |_| | |_| | | | (_| \__ \
//  \____|\__,_|_|_|\__,_|___/

    $guilds = [];

    $guilds[67] = (new Building(67, 4, clienttranslate("Merchants Guild"), TYPE_PURPLE))
        ->setCost([CLAY => 1, WOOD => 1, GLASS => 1, PAPYRUS => 1])
        ->setGuildRewardBuildingTypes([TYPE_YELLOW]);

    $guilds[68] = (new Building(68, 4, clienttranslate("Shipowners Guild"), TYPE_PURPLE))
        ->setCost([CLAY => 1, STONE => 1, GLASS => 1, PAPYRUS => 1])
        ->setGuildRewardBuildingTypes([TYPE_BROWN, TYPE_GREY]);

    $guilds[69] = (new Building(69, 4, clienttranslate("Builders Guild"), TYPE_PURPLE))
        ->setCost([STONE => 2, CLAY => 1, WOOD => 1, GLASS => 1])
        ->setGuildRewardWonders(true);

    $guilds[70] = (new Building(70, 4, clienttranslate("Magistrates Guild"), TYPE_PURPLE))
        ->setCost([WOOD => 2, CLAY => 1, PAPYRUS => 1])
        ->setGuildRewardBuildingTypes([TYPE_BLUE]);

    $guilds[71] = (new Building(71, 4, clienttranslate("Scientists Guild"), TYPE_PURPLE))
        ->setCost([CLAY => 2, WOOD => 2])
        ->setGuildRewardBuildingTypes([TYPE_GREEN]);

    $guilds[72] = (new Building(72, 4, clienttranslate("Moneylenders Guild"), TYPE_PURPLE))
        ->setCost([STONE => 2, WOOD => 2])
        ->setGuildRewardCoinTriplets(true);

    $guilds[73] = (new Building(73, 4, clienttranslate("Tacticians Guild"), TYPE_PURPLE))
        ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
        ->setGuildRewardBuildingTypes([TYPE_RED]);

//  ____                                      _____     _
// |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __  ___
// | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \/ __|
// |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | \__ \
// |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|___/
//                  |___/

    $progressTokens = [];

    $guilds[1] = (new ProgressToken(1, clienttranslate("Agriculture")))
        ->setCoins(6)
        ->setVictoryPoints(4);

    $guilds[2] = (new ProgressToken(2, clienttranslate("Architecture")));

    $guilds[3] = (new ProgressToken(3, clienttranslate("Economy")));

    $guilds[4] = (new ProgressToken(4, clienttranslate("Law")))
        ->setScientificSymbol(2);

    $guilds[5] = (new ProgressToken(5, clienttranslate("Masonry")));

    $guilds[6] = (new ProgressToken(6, clienttranslate("Mathematics")));

    $guilds[7] = (new ProgressToken(7, clienttranslate("Philosophy")))
        ->setVictoryPoints(7);

    $guilds[8] = (new ProgressToken(8, clienttranslate("Strategy")));

    $guilds[9] = (new ProgressToken(9, clienttranslate("Theology")));

    $guilds[10] = (new ProgressToken(10, clienttranslate("Urbanism")))
        ->setCoins(6);

    $buildings = $age1 + $age2 + $age3 + $guilds;
}
