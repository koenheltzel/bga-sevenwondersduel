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

// This code get's executed (included?) twice for some reason. Defining constants throws a notice, so check if they are already set.
if (!defined('COINS')) {
    define('COINS', 'coin');
    define('CLAY', 'clay');
    define('WOOD', 'wood');
    define('STONE', 'stone');
    define('GLASS', 'glass');
    define('PAPYRUS', 'papyrus');
    define('LINKED_BUILDING', 'linked');

    define('RESOURCES', [
        COINS => clienttranslate('coin(s)'),
        CLAY => clienttranslate('clay'),
        WOOD => clienttranslate('wood'),
        STONE => clienttranslate('stone'),
        GLASS => clienttranslate('glass'),
        PAPYRUS => clienttranslate('papyrus'),
        LINKED_BUILDING => clienttranslate('linked building'),
    ]);
}
