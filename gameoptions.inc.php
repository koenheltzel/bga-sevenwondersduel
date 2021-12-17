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
 * gameoptions.inc.php
 *
 * SevenWondersDuel game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in sevenwondersduel.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = [

    /*
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  description => this text will be displayed underneath the option when this value is selected to explain what it does
                            //  beta=true => this option is in beta version right now.
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'), 'description' => totranslate('this option does X'), 'beta' => true, 'nobeginner' => true )
                        )
            )

    */

    105 => [
        'name' => totranslate('Expansion: Pantheon'),
        'values' => [
            0 => ['name' => totranslate('No')],
            1 => [
                'name' => totranslate('Yes'),
                'tmdisplay' => totranslate('Expansion: Pantheon'),
                'description' => totranslate('Pantheon offers players the ability to invoke powerful Divinities to benefit from their powers. During Age I, the players will choose the Divinities they will be able to invoke during Ages II and III.'),
                'beta' => false,
                'nobeginner' => true
            ]
        ],
        'default' => 0
    ],
//    106 => [
//        'name' => totranslate('Guarantee Pantheon Wonders (testing option)'),
//        'values' => [
//            0 => ['name' => totranslate('No')],
//            1 => [
//                'name' => totranslate('Yes'),
//                'description' => totranslate('Guarantees the inclusion of the 2 Pantheon Wonders for testing purposes.'),
//                'beta' => true,
//            ]
//        ],
//        'default' => 0,
//        'displaycondition' => [ // Note: do not display this option unless these conditions are met
//            [
//                'type' => 'otheroption',
//                'id' => 105,
//                'value' => 1
//            ]
//        ],
//    ],
//    107 => [
//        'name' => totranslate('Guarantee Pantheon Progress Tokens (testing option)'),
//        'values' => [
//            0 => ['name' => totranslate('No')],
//            1 => [
//                'name' => totranslate('Yes'),
//                'description' => totranslate('Guarantees the inclusion of the 3 Pantheon Progress Tokens for testing purposes.'),
//                'beta' => true,
//            ]
//        ],
//        'default' => 0,
//        'displaycondition' => [ // Note: do not display this option unless these conditions are met
//            [
//                'type' => 'otheroption',
//                'id' => 105,
//                'value' => 1
//            ]
//        ],
//    ],

    110 => [
        'name' => totranslate('Expansion: Agora'),
        'values' => [
            0 => ['name' => totranslate('No')],
            1 => [
                'name' => totranslate('Yes'),
                'tmdisplay' => totranslate('Expansion: Agora'),
                'description' => totranslate('Agora adds Senators and their influence on the Senate. Try to control these Chambers to benefit from Decrees or call on Conspirators who could very well overthrow the situation.'),
                'beta' => false,
                'nobeginner' => true
            ]
        ],
        'default' => 0
    ],
//    111 => [
//        'name' => totranslate('Guarantee Agora Wonders (testing option)'),
//        'values' => [
//            0 => ['name' => totranslate('No')],
//            1 => [
//                'name' => totranslate('Yes'),
//                'description' => totranslate('Guarantees the inclusion of the 2 Agora Wonders for testing purposes.'),
//                'beta' => true,
//            ]
//        ],
//        'default' => 0,
//        'displaycondition' => [ // Note: do not display this option unless these conditions are met
//            [
//                'type' => 'otheroption',
//                'id' => 110,
//                'value' => 1
//            ]
//        ],
//    ],
//    112 => [
//        'name' => totranslate('Guarantee Agora Progress Tokens (testing option)'),
//        'values' => [
//            0 => ['name' => totranslate('No')],
//            1 => [
//                'name' => totranslate('Yes'),
//                'description' => totranslate('Guarantees the inclusion of the 2 Agora Progress Tokens for testing purposes.'),
//                'beta' => true,
//            ]
//        ],
//        'default' => 0,
//        'displaycondition' => [ // Note: do not display this option unless these conditions are met
//            [
//                'type' => 'otheroption',
//                'id' => 110,
//                'value' => 1
//            ]
//        ],
//    ]

];


