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
 * stats.inc.php
 *
 * SevenWondersDuel game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = [

    // Statistics global to table
    "table" => [

        "turns_number" => [
            "id" => 10,
            "name" => totranslate("Number of turns"),
            "type" => "int"
        ],
        "civilian_victory" => [
            "id" => 20,
            "name" => totranslate("Civilian Victory"),
            "type" => "int"
        ],
        "scientific_supremacy" => [
            "id" => 30,
            "name" => totranslate("Scientific Supremacy"),
            "type" => "int"
        ],
        "military_supremacy" => [
            "id" => 40,
            "name" => totranslate("Military Supremacy"),
            "type" => "int"
        ],
        "political_supremacy" => [
            "id" => 42,
            "name" => totranslate("Political Supremacy"),
            "type" => "int"
        ],
        "draw" => [
            "id" => 41,
            "name" => totranslate("Draw"),
            "type" => "int"
        ],

        /*
                Examples:


                "table_teststat1" => array(   "id"=> 10,
                                        "name" => totranslate("table test stat 1"),
                                        "type" => "int" ),

                "table_teststat2" => array(   "id"=> 11,
                                        "name" => totranslate("table test stat 2"),
                                        "type" => "float" )
        */
    ],

    // Statistics existing for each player
    "player" => [

        "turns_number" => [
            "id" => 10,
            "name" => totranslate("Number of turns"),
            "type" => "int"
        ],
        "civilian_victory" => [
            "id" => 20,
            "name" => totranslate("Civilian Victory"),
            "type" => "int"
        ],
        "scientific_supremacy" => [
            "id" => 30,
            "name" => totranslate("Scientific Supremacy"),
            "type" => "int"
        ],
        "military_supremacy" => [
            "id" => 40,
            "name" => totranslate("Military Supremacy"),
            "type" => "int"
        ],
        "political_supremacy" => [
            "id" => 42,
            "name" => totranslate("Political Supremacy"),
            "type" => "int"
        ],
        "draw" => [
            "id" => 41,
            "name" => totranslate("Draw"),
            "type" => "int"
        ],
        "victory_points" => [
            "id" => 50,
            "name" => totranslate("Victory Points (VP)"),
            "type" => "int"
        ],
        "vp_blue" => [
            "id" => 60,
            "name" => totranslate("VP from blue cards"),
            "type" => "int"
        ],
        "vp_green" => [
            "id" => 70,
            "name" => totranslate("VP from green cards"),
            "type" => "int"
        ],
        "vp_yellow" => [
            "id" => 80,
            "name" => totranslate("VP from yellow cards"),
            "type" => "int"
        ],
        "vp_purple" => [
            "id" => 90,
            "name" => totranslate("VP from purple cards"),
            "type" => "int"
        ],
        "vp_wonders" => [
            "id" => 100,
            "name" => totranslate("VP from Wonders"),
            "type" => "int"
        ],
        "vp_progress_tokens" => [
            "id" => 110,
            "name" => totranslate("VP from Progress Tokens"),
            "type" => "int"
        ],
        "vp_coins" => [
            "id" => 120,
            "name" => totranslate("VP from Coins"),
            "type" => "int"
        ],
        "vp_military" => [
            "id" => 130,
            "name" => totranslate("VP from Conflict Pawn position"),
            "type" => "int"
        ],
        "brown_cards" => [
            "id" => 140,
            "name" => totranslate("Brown cards"),
            "type" => "int"
        ],
        "grey_cards" => [
            "id" => 150,
            "name" => totranslate("Grey cards"),
            "type" => "int"
        ],
        "yellow_cards" => [
            "id" => 160,
            "name" => totranslate("Yellow cards"),
            "type" => "int"
        ],
        "red_cards" => [
            "id" => 170,
            "name" => totranslate("Red cards"),
            "type" => "int"
        ],
        "blue_cards" => [
            "id" => 180,
            "name" => totranslate("Blue cards"),
            "type" => "int"
        ],
        "green_cards" => [
            "id" => 190,
            "name" => totranslate("Green cards"),
            "type" => "int"
        ],
        "purple_cards" => [
            "id" => 200,
            "name" => totranslate("Purple cards"),
            "type" => "int"
        ],
        "buildings_constructed" => [
            "id" => 205,
            "name" => totranslate("Buildings constructed"),
            "type" => "int"
        ],
        "wonders_constructed" => [
            "id" => 210,
            "name" => totranslate("Wonders constructed"),
            "type" => "int"
        ],
        "progress_tokens" => [
            "id" => 220,
            "name" => totranslate("Progress Tokens"),
            "type" => "int"
        ],
        "shields" => [
            "id" => 230,
            "name" => totranslate("Shields (Conflict Pawn steps)"),
            "type" => "int"
        ],
        "science_symbols" => [
            "id" => 240,
            "name" => totranslate("Science symbols (unique)"),
            "type" => "int"
        ],
        "extra_turns" => [
            "id" => 250,
            "name" => totranslate("Extra turns"),
            "type" => "int"
        ],
        "discarded_cards" => [
            "id" => 260,
            "name" => totranslate("Discarded cards"),
            "type" => "int"
        ],
        "chained_constructions" => [
            "id" => 270,
            "name" => totranslate("Chained constructions"),
            "type" => "int"
        ],
        "conspiracies_prepared" => [
            "id" => 400,
            "name" => totranslate("Conspiracies prepared"),
            "type" => "int"
        ],
        "conspiracies_triggered" => [
            "id" => 410,
            "name" => totranslate("Conspiracies triggered"),
            "type" => "int"
        ],

        /*
                Examples:


                "player_teststat1" => array(   "id"=> 10,
                                        "name" => totranslate("player test stat 1"),
                                        "type" => "int" ),

                "player_teststat2" => array(   "id"=> 11,
                                        "name" => totranslate("player test stat 2"),
                                        "type" => "float" )

        */
    ],

    "value_labels" => [
        20 => [ // Civilian Victory
            0 => totranslate("No"),
            1 => totranslate("Yes"),
        ],
        30 => [ // Scientific Supremacy
            0 => totranslate("No"),
            1 => totranslate("Yes"),
        ],
        40 => [ // Military Supremacy
            0 => totranslate("No"),
            1 => totranslate("Yes"),
        ],
        41 => [ // Draw
            0 => totranslate("No"),
            1 => totranslate("Yes"),
        ],
    ],

];
