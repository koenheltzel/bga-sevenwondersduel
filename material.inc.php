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
    ->setCost([STONE => 2, CLAY => 2, PAPYRUS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("Circus Maximus")))
    ->setCost([STONE => 2, WOOD => 1, GLASS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Colossus")))
    ->setCost([CLAY => 3, GLASS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Great Library")))
    ->setCost([WOOD => 3, GLASS => 1, PAPYRUS => 1]);

$wonders[] = (new Wonder($id++, clienttranslate("The Great Lighthouse")))
    ->setCost([WOOD => 1, STONE => 1, PAPYRUS => 2]);

$wonders[] = (new Wonder($id++, clienttranslate("The Hanging Gardens")))
    ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1]);

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
    ->setCost([WOOD => 1, STONE => 1, GLASS => 1, PAPYRUS => 1]);

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

//self::dump('wonders', $wonders);
//self::dump('age1', $age1);
//exit;

//     _                  ___ ___
//    / \   __ _  ___    |_ _|_ _|
//   / _ \ / _` |/ _ \    | | | |
//  / ___ \ (_| |  __/    | | | |
// /_/   \_\__, |\___|   |___|___|
//         |___/



//     _                  ___ ___ ___
//    / \   __ _  ___    |_ _|_ _|_ _|
//   / _ \ / _` |/ _ \    | | | | | |
//  / ___ \ (_| |  __/    | | | | | |
// /_/   \_\__, |\___|   |___|___|___|
//         |___/



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
