<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuelPantheon implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * SevenWondersDuelPantheon game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

$militaryTokenTransitions = [
    // From military building / track
    SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_ID,
    SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_ID,
    SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID, // If remove influence is skipp
    SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // In case of instant victory
];

$constructBuildingTransitions = array_merge($militaryTokenTransitions, [
    // Science symbol pair
    SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_ID,
    // Conspirator
    SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRATOR_ACTION_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRATOR_ACTION_ID,
    // Politician
    SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_ID,
    // Mythology Token
    SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
]);

$constructWonderTransitions = array_merge($militaryTokenTransitions, [
    SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_TRIGGER_UNPREPARED_CONSPIRACY_NAME => SevenWondersDuelPantheon::STATE_TRIGGER_UNPREPARED_CONSPIRACY_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_DECK_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_DECK_ID,
]);

$divinityStateTransitions = array_merge($militaryTokenTransitions, [
    SevenWondersDuelPantheon::STATE_DECONSTRUCT_WONDER_NAME => SevenWondersDuelPantheon::STATE_DECONSTRUCT_WONDER_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_ENKI_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_ENKI_PROGRESS_TOKEN_ID,
    SevenWondersDuelPantheon::STATE_PLACE_SNAKE_TOKEN_NAME => SevenWondersDuelPantheon::STATE_PLACE_SNAKE_TOKEN_ID,
    SevenWondersDuelPantheon::STATE_DISCARD_AGE_CARD_NAME => SevenWondersDuelPantheon::STATE_DISCARD_AGE_CARD_ID,
    SevenWondersDuelPantheon::STATE_PLACE_MINERVA_TOKEN_NAME => SevenWondersDuelPantheon::STATE_PLACE_MINERVA_TOKEN_ID,
    SevenWondersDuelPantheon::STATE_DISCARD_MILITARY_TOKEN_NAME => SevenWondersDuelPantheon::STATE_DISCARD_MILITARY_TOKEN_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_TOP_CARDS_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_TOP_CARDS_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_DECK_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_DECK_ID,
    SevenWondersDuelPantheon::STATE_CONSTRUCT_WONDER_WITH_DISCARDED_BUILDING_NAME => SevenWondersDuelPantheon::STATE_CONSTRUCT_WONDER_WITH_DISCARDED_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_TAKE_BUILDING_NAME => SevenWondersDuelPantheon::STATE_TAKE_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_NAME => SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_ID,
    SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Mars, instant military victory
]);

$conspiracyStateTransitions = [
    // Military conspiracy --> Military track tokens
    SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID, // Conspiracies 2nd/3rd action
    SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_ID,
    SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_CONSTRUCT_BUILDING_FROM_BOX_NAME => SevenWondersDuelPantheon::STATE_CONSTRUCT_BUILDING_FROM_BOX_ID,
    SevenWondersDuelPantheon::STATE_CONSTRUCT_LAST_ROW_BUILDING_NAME => SevenWondersDuelPantheon::STATE_CONSTRUCT_LAST_ROW_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_DESTROY_CONSTRUCTED_WONDER_NAME => SevenWondersDuelPantheon::STATE_DESTROY_CONSTRUCTED_WONDER_ID,
    SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_NAME => SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_ID,
    SevenWondersDuelPantheon::STATE_LOCK_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_LOCK_PROGRESS_TOKEN_ID,
    SevenWondersDuelPantheon::STATE_MOVE_DECREE_NAME => SevenWondersDuelPantheon::STATE_MOVE_DECREE_ID,
    SevenWondersDuelPantheon::STATE_SWAP_BUILDING_NAME => SevenWondersDuelPantheon::STATE_SWAP_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_TAKE_BUILDING_NAME => SevenWondersDuelPantheon::STATE_TAKE_BUILDING_ID,
    SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_NAME => SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_ID,
    SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID,
];

$machinestates = [

    // The initial state. Please do not modify.
    SevenWondersDuelPantheon::STATE_GAME_SETUP_ID => [
        "name" => SevenWondersDuelPantheon::STATE_GAME_SETUP_NAME,
        "description" => "",
        "type" => "manager",
        "action" => "enterStateGameSetup",
        "transitions" => ["" => SevenWondersDuelPantheon::STATE_SELECT_WONDER_ID]
    ],

    SevenWondersDuelPantheon::STATE_SELECT_WONDER_ID => [
        "name" => SevenWondersDuelPantheon::STATE_SELECT_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must choose a wonder'),
        "descriptionmyturn" => clienttranslate('${you} must choose a wonder'),
        "type" => "activeplayer",
        "action" => "enterStateSelectWonder",
        "args" => "argSelectWonder",
        "possibleactions" => [
            "actionSelectWonder",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_WONDER_SELECTED_NAME => SevenWondersDuelPantheon::STATE_WONDER_SELECTED_ID,
            SevenWondersDuelPantheon::STATE_CONSPIRE_NAME => SevenWondersDuelPantheon::STATE_CONSPIRE_ID,
            SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_WONDER_SELECTED_ID => [
        "name" => SevenWondersDuelPantheon::STATE_WONDER_SELECTED_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStateWonderSelected",
        "updateGameProgression" => true,
        "transitions" => [
            SevenWondersDuelPantheon::STATE_SELECT_WONDER_NAME => SevenWondersDuelPantheon::STATE_SELECT_WONDER_ID,
            SevenWondersDuelPantheon::STATE_NEXT_AGE_NAME => SevenWondersDuelPantheon::STATE_NEXT_AGE_ID
        ]
    ],

    SevenWondersDuelPantheon::STATE_NEXT_AGE_ID => [
        "name" => SevenWondersDuelPantheon::STATE_NEXT_AGE_NAME,
        "description" => clienttranslate('Preparing Age ${ageRoman}...'),
        "descriptionmyturn" => clienttranslate('Preparing Age ${ageRoman}...'),
        "type" => "game",
        "action" => "enterStateNextAge",
        "args" => "argNextAge",
        "transitions" => [
            SevenWondersDuelPantheon::STATE_SELECT_START_PLAYER_NAME => SevenWondersDuelPantheon::STATE_SELECT_START_PLAYER_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID
        ]
    ],

    SevenWondersDuelPantheon::STATE_SELECT_START_PLAYER_ID => [
        "name" => SevenWondersDuelPantheon::STATE_SELECT_START_PLAYER_NAME,
        "description" => clienttranslate('${actplayer} must choose who begins Age ${ageRoman}'),
        "descriptionmyturn" => clienttranslate('${you} must choose who begins Age ${ageRoman}'),
        "type" => "activeplayer",
        "action" => "enterStateSelectStartPlayer",
        "args" => "argSelectStartPlayer",
        "possibleactions" => [
            "actionSelectStartPlayer",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_START_PLAYER_SELECTED_NAME => SevenWondersDuelPantheon::STATE_START_PLAYER_SELECTED_ID
        ]
    ],

    SevenWondersDuelPantheon::STATE_START_PLAYER_SELECTED_ID => [
        "name" => SevenWondersDuelPantheon::STATE_START_PLAYER_SELECTED_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStateStartPlayerSelected",
        "transitions" => [
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
        ]
    ],
    SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME,
        "description" => '', // Set in onEnterPlayerTurn
        "descriptionmyturn" => '', // Set in onEnterPlayerTurn
        "type" => "activeplayer",
        "action" => "enterStatePlayerTurn",
        "args" => "argPlayerTurn",
        "possibleactions" => [
            "actionTriggerConspiracy",
            "actionConstructBuilding",
            "actionDiscardBuilding",
            "actionConstructWonder",
            "actionActivateDivinity",
            "actionPrepareConspiracy",
        ],
        "transitions" => array_merge(
            $conspiracyStateTransitions, // Support all conspiracy actions
            $divinityStateTransitions, // Support all divinity actions
            $constructBuildingTransitions, // Support all construct building actions
            $constructWonderTransitions, // Support all construct wonder actions
            [
                SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID, // After triggering conspiracy
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // For immediate victories, skipping special wonder action states.
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRATOR_ACTION_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRATOR_ACTION_NAME,
        "description" => clienttranslate('${actplayer} must choose to place an Influence cube or to Conspire'),
        "descriptionmyturn" => clienttranslate('${you} must choose to place an Influence cube or to Conspire'),
        "type" => "activeplayer",
        "action" => "enterStateChooseConspiratorAction",
        "args" => "argChooseConspiratorAction",
        "possibleactions" => [
            "actionChooseConspiratorActionPlaceInfluence",
            "actionConspire",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_CONSPIRE_NAME => SevenWondersDuelPantheon::STATE_CONSPIRE_ID,
            SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_CONSPIRE_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CONSPIRE_NAME,
        "description" => clienttranslate('${actplayer} must choose a Conspiracy'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Conspiracy'),
        "type" => "activeplayer",
        "action" => "enterStateConspire",
        "args" => "argConspire",
        "possibleactions" => [
            "actionChooseConspiracy",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_NAME,
        "description" => clienttranslate('${actplayer} must choose to place the other Conspiracy card on the top or the bottom of the deck'),
        "descriptionmyturn" => clienttranslate('${you} must choose to place the other Conspiracy card on the top or the bottom of the deck'),
        "type" => "activeplayer",
        "action" => "enterStateChooseConspireRemnantPosition",
        "args" => "argChooseConspireRemnantPosition",
        "possibleactions" => [
            "actionChooseConspireRemnantPosition",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_WONDER_SELECTED_NAME => SevenWondersDuelPantheon::STATE_WONDER_SELECTED_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_ID => [
        "name" => SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_NAME,
        "description" => clienttranslate('${actplayer} must choose a Senate action (${senateActionsLeft} left)'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Senate action (${senateActionsLeft} left)'),
        "type" => "activeplayer",
        "action" => "enterStateSenateActions",
        "args" => "argSenateActions",
        "possibleactions" => [
            "actionPlaceInfluence",
            "actionMoveInfluence",
            "actionSkipMoveInfluence",
        ],
        "transitions" => array_merge($militaryTokenTransitions, [ // For when the military Decree triggers a Military Token.
            SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_ID => [
        "name" => SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME,
        "description" => clienttranslate('${actplayer} must choose a Senate chamber to add an Influence cube to'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Senate chamber to add an Influence cube to'),
        "type" => "activeplayer",
        "action" => "enterStatePlaceInfluence",
        "args" => "argPlaceInfluence",
        "possibleactions" => [
            "actionPlaceInfluence",
        ],
        "transitions" => array_merge($militaryTokenTransitions, [ // Remove and Move maybe are obvious, but Place after Place can happen too (Conspirator Place Influence on Military Decree, which triggers Military Token with Place Influence action).
            SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_ID, // Going back to Senate Actions after a Senate Action triggered the Military Decree which collected a Military Token.
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID, // After Conspiracy Coup, after military token action.
            SevenWondersDuelPantheon::STATE_WONDER_SELECTED_NAME => SevenWondersDuelPantheon::STATE_WONDER_SELECTED_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID => [
        "name" => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME,
        "description" => clienttranslate('${actplayer} may choose a Senate chamber to move an Influence cube from'),
        "descriptionmyturn" => clienttranslate('${you} may choose a Senate chamber to move an Influence cube from'),
        "type" => "activeplayer",
        "action" => "enterStateMoveInfluence",
        "args" => "argMoveInfluence",
        "possibleactions" => [
            "actionMoveInfluence",
            "actionSkipMoveInfluence",
        ],
        "transitions" => array_merge($militaryTokenTransitions, [ // When a move results in the military decree, which results in a military token
            SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_ID, // Going back to Senate Actions after a Senate Action triggered the Military Decree which collected a Military Token.
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_ID => [
        "name" => SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_NAME,
        "description" => clienttranslate('${actplayer} must choose one of the opponent\'s Influence cubes to remove'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Senate chamber to remove one of your opponent\'s Influence cubes from'),
        "type" => "activeplayer",
        "action" => "enterStateRemoveInfluence",
        "args" => "argRemoveInfluence",
        "possibleactions" => [
            "actionRemoveInfluence",
        ],
        "transitions" => array_merge($militaryTokenTransitions, [ // When a remove results in the military decree, which results in a military token
            SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_ID, // Going back to Senate Actions after a Senate Action triggered the Military Decree which collected a Military Token.
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuelPantheon::STATE_TRIGGER_UNPREPARED_CONSPIRACY_ID => [
        "name" => SevenWondersDuelPantheon::STATE_TRIGGER_UNPREPARED_CONSPIRACY_NAME,
        "description" => clienttranslate('${actplayer} may choose to trigger an unprepared Conspiracy'),
        "descriptionmyturn" => clienttranslate('${you} may choose to trigger an unprepared Conspiracy'),
        "type" => "activeplayer",
        "action" => "enterStateTriggerUnpreparedConspiracy",
        "args" => "argTriggerUnpreparedConspiracy",
        "possibleactions" => [
            "actionTriggerConspiracy",
            "actionSkipTriggerUnpreparedConspiracy",
        ],
        "transitions" => array_merge(
            $conspiracyStateTransitions, // Support all conspiracy actions
            [
                SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // For immediate victories
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME,
        "description" => clienttranslate('${actplayer} must choose a Divinity and place it face down in the Pantheon'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Divinity and place it face down in the Pantheon'),
        "type" => "activeplayer",
        "action" => "enterStateChooseAndPlaceDivinity",
        "args" => "argChooseAndPlaceDivinity",
        "possibleactions" => [
            "actionChooseAndPlaceDivinity",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_DECONSTRUCT_WONDER_ID => [
        "name" => SevenWondersDuelPantheon::STATE_DECONSTRUCT_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must choose a constructed Wonder to discard the Age card from'),
        "descriptionmyturn" => clienttranslate('${you} must choose a constructed Wonder to discard the Age card from'),
        "type" => "activeplayer",
        "action" => "enterStateDeconstructWonder",
        "args" => "argDeconstructWonder",
        "possibleactions" => [
            "actionDeconstructWonder",
        ],
        "transitions" => array_merge(
            [
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CONSTRUCT_WONDER_WITH_DISCARDED_BUILDING_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CONSTRUCT_WONDER_WITH_DISCARDED_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose a discarded card and construct a Wonder for free using that card'),
        "descriptionmyturn" => clienttranslate('${you} must choose a discarded card and construct a Wonder for free using that card'),
        "type" => "activeplayer",
        "action" => "enterStateConstructWonderWithDiscardedBuilding",
        "args" => "argConstructWonderWithDiscardedBuilding",
        "possibleactions" => [
            "actionConstructWonder",
        ],
        "transitions" => array_merge(
            $constructWonderTransitions, // Support all construct building actions
            [
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_ENKI_PROGRESS_TOKEN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_ENKI_PROGRESS_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must choose one of the two Progress Tokens on Enki'),
        "descriptionmyturn" => clienttranslate('${you} must choose one of the two Progress Tokens on Enki'),
        "type" => "activeplayer",
        "action" => "enterStateChooseEnkiProgressToken",
        "args" => "argChooseEnkiProgressToken",
        "possibleactions" => [
            "actionChooseEnkiProgressToken",
        ],
        "transitions" => array_merge(
            [
                SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID,
                SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_ID, // Symbol pair of Law and Ishtar.
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_PLACE_SNAKE_TOKEN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_PLACE_SNAKE_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must place the Snake token on an opponent\'s green card'),
        "descriptionmyturn" => clienttranslate('${you} must place the Snake token on an opponent\'s green card'),
        "type" => "activeplayer",
        "action" => "enterStatePlaceSnakeToken",
        "args" => "argPlaceSnakeToken",
        "possibleactions" => [
            "actionPlaceSnakeToken",
        ],
        "transitions" => array_merge(
            [
                SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID,
                SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_DISCARD_AGE_CARD_ID => [
        "name" => SevenWondersDuelPantheon::STATE_DISCARD_AGE_CARD_NAME,
        "description" => clienttranslate('${actplayer} must discard a card (face up or down) from the structure'),
        "descriptionmyturn" => clienttranslate('${you} must discard a card (face up or down) from the structure'),
        "type" => "activeplayer",
        "action" => "enterStateDiscardAgeCard",
        "args" => "argDiscardAgeCard",
        "possibleactions" => [
            "actionDiscardAgeCard",
        ],
        "transitions" => array_merge(
            [
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_PLACE_MINERVA_TOKEN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_PLACE_MINERVA_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must place the Minerva pawn on any space of the Military Track'),
        "descriptionmyturn" => clienttranslate('${you} must place the Minerva pawn on any space of the Military Track'),
        "type" => "activeplayer",
        "action" => "enterStatePlaceMinervaToken",
        "args" => "argPlaceMinervaToken",
        "possibleactions" => [
            "actionPlaceMinervaToken",
        ],
        "transitions" => array_merge(
            [
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_DISCARD_MILITARY_TOKEN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_DISCARD_MILITARY_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must choose and discard a Military token without applying its effect'),
        "descriptionmyturn" => clienttranslate('${you} must choose and discard a Military token without applying its effect'),
        "type" => "activeplayer",
        "action" => "enterStateDiscardMilitaryToken",
        "args" => "argDiscardMilitaryToken",
        "possibleactions" => [
            "actionDiscardMilitaryToken",
        ],
        "transitions" => array_merge(
            [
                SevenWondersDuelPantheon::STATE_APPLY_MILITARY_TOKEN_NAME => SevenWondersDuelPantheon::STATE_APPLY_MILITARY_TOKEN_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_APPLY_MILITARY_TOKEN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_APPLY_MILITARY_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must choose and apply the effect of another Military token, then discard it'),
        "descriptionmyturn" => clienttranslate('${you} must choose and apply the effect of another Military token, then discard it'),
        "type" => "activeplayer",
        "action" => "enterStateApplyMilitaryToken",
        "args" => "argApplyMilitaryToken",
        "possibleactions" => [
            "actionApplyMilitaryToken",
        ],
        "transitions" => array_merge(
            $militaryTokenTransitions,
            [
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_TOP_CARDS_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_TOP_CARDS_NAME,
        "description" => clienttranslate('${actplayer} must choose a Divinity card and activate it for free'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Divinity card and activate it for free'),
        "type" => "activeplayer",
        "action" => "enterStateChooseDivinityFromTopCards",
        "args" => "argChooseDivinityFromTopCards",
        "possibleactions" => [
            "actionChooseDivinityFromTopCards",
        ],
        "transitions" => array_merge(
            $divinityStateTransitions,
            [
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_DECK_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_DECK_NAME,
        "description" => clienttranslate('${actplayer} must choose a Divinity deck to reveal and choose a Divinity from'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Divinity deck to reveal and choose a Divinity from'),
        "type" => "activeplayer",
        "action" => "enterStateChooseDivinityDeck",
        "args" => "argChooseDivinityDeck",
        "possibleactions" => [
            "actionChooseDivinityDeck",
        ],
        "transitions" => array_merge(
            [
                SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_DECK_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_DECK_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_DECK_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_DECK_NAME,
        "description" => clienttranslate('${actplayer} must choose a Divinity from the ${mythologyType} Mythology deck and activate it for free'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Divinity from the ${mythologyType} Mythology deck and activate it for free'),
        "type" => "activeplayer",
        "action" => "enterStateChooseDivinityFromDeck",
        "args" => "argChooseDivinityFromDeck",
        "possibleactions" => [
            "actionChooseDivinityFromDeck",
        ],
        "transitions" => array_merge(
            $divinityStateTransitions,
            [
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_ID => [
    "name" => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME,
    "description" => clienttranslate('${actplayer} must choose a progress token'),
    "descriptionmyturn" => clienttranslate('${you} must choose a progress token'),
    "type" => "activeplayer",
    "action" => "enterStateChooseProgressToken",
    "args" => "argChooseProgressToken",
    "possibleactions" => [
        "actionChooseProgressToken",
    ],
    "transitions" => [
        SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID,
        SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
        SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
        SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
        SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_ID, // Pantheon when collecting Progress Token Law if you already have Divinity Ishtar
        SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
        SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
    ]
],

    SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose one of the opponent\'s ${buildingTypeTranslatable} cards to discard'),
        "descriptionmyturn" => clienttranslate('${you} must choose one of the opponent\'s ${buildingTypeTranslatable} cards to discard'),
        "type" => "activeplayer",
        "action" => "enterStateChooseOpponentBuilding",
        "args" => "argChooseOpponentBuilding",
        "possibleactions" => [
            "actionChooseOpponentBuilding",
            // If there are no building to discard, this state will be skipped automatically, so no need to have NEXT_PLAYER_TURN as a possible action.
        ],
        "transitions" => array_merge($militaryTokenTransitions, [
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID, // Conspiracies, when skipping the discard action
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME,
        "description" => clienttranslate('${actplayer} must choose a progress token from the box'),
        "descriptionmyturn" => clienttranslate('${you} must choose a progress token from the box'),
        "type" => "activeplayer",
        "action" => "enterStateChooseProgressTokenFromBox",
        "args" => "argChooseProgressTokenFromBox",
        "possibleactions" => [
            "actionChooseProgressTokenFromBox",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // LAW token > Scientific supremacy
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID, // Conspiracy Espionage
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose a discarded building to construct'),
        "descriptionmyturn" => clienttranslate('${you} must choose a discarded building to construct'),
        "type" => "activeplayer",
        "action" => "enterStateChooseDiscardedBuilding",
        "args" => "argChooseDiscardedBuilding",
        "possibleactions" => [
            "actionChooseDiscardedBuilding",
            // If there is no discarded building to construct, this state will be skipped automatically, so no need to have NEXT_PLAYER_TURN as a possible action.
        ],
        "transitions" => array_merge(
            $constructBuildingTransitions, // Support all construct building actions
            [
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CONSTRUCT_BUILDING_FROM_BOX_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CONSTRUCT_BUILDING_FROM_BOX_NAME,
        "description" => clienttranslate('${actplayer} must choose a Building removed from the game up to the current Age to play for free'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Building removed from the game up to the current Age to play for free'),
        "type" => "activeplayer",
        "action" => "enterStateConstructBuildingFromBox",
        "args" => "argConstructBuildingFromBox",
        "possibleactions" => [
            "actionConstructBuildingFromBox",
        ],
        "transitions" => array_merge(
            $constructBuildingTransitions, // Support all construct building actions
            [
                SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_CONSTRUCT_LAST_ROW_BUILDING_ID => [
        "name" => SevenWondersDuelPantheon::STATE_CONSTRUCT_LAST_ROW_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose a Building from the last row of the Age structure (excluding Senators) and construct it for free'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Building from the last row of the Age structure (excluding Senators) and construct it for free'),
        "type" => "activeplayer",
        "action" => "enterStateConstructLastRowBuilding",
        "args" => "argConstructLastRowBuilding",
        "possibleactions" => [
            "actionConstructBuilding",
        ],
        "transitions" => array_merge(
            $constructBuildingTransitions, // Support all construct building actions
            [
                SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
                SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
                SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuelPantheon::STATE_DESTROY_CONSTRUCTED_WONDER_ID => [
        "name" => SevenWondersDuelPantheon::STATE_DESTROY_CONSTRUCTED_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must choose one of the opponent\'s constructed Wonders and return it to the box'),
        "descriptionmyturn" => clienttranslate('${you} must choose one of the opponent\'s constructed Wonders and return it to the box'),
        "type" => "activeplayer",
        "action" => "enterStateDestroyConstructedWonder",
        "args" => "argDestroyConstructedWonder",
        "possibleactions" => [
            "actionDestroyConstructedWonder",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_ID => [
        "name" => SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_NAME,
        "description" => '', // Set in onEnterDiscardAvailableCard
        "descriptionmyturn" => '', // Set in onEnterDiscardAvailableCard
        "type" => "activeplayer",
        "action" => "enterStateDiscardAvailableCard",
        "args" => "argDiscardAvailableCard",
        "possibleactions" => [
            "actionDiscardAvailableCard",
            "actionSkipDiscardAvailableCard",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_NAME => SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_ID,
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_LOCK_PROGRESS_TOKEN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_LOCK_PROGRESS_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must choose a Progress token from the board, his opponent or the box and lock it away for the rest of the game'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Progress token from the board, your opponent or the box and lock it away for the rest of the game'),
        "type" => "activeplayer",
        "action" => "enterStateLockProgressToken",
        "args" => "argLockProgressToken",
        "possibleactions" => [
            "actionLockProgressToken",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_MOVE_DECREE_ID => [
        "name" => SevenWondersDuelPantheon::STATE_MOVE_DECREE_NAME,
        "description" => clienttranslate('${actplayer} must choose a Decree token to move to a Chamber of his choice, under the existing Decree'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Decree token to move to a Chamber of your choice, under the existing Decree'),
        "type" => "activeplayer",
        "action" => "enterStateMoveDecree",
        "args" => "argMoveDecree",
        "possibleactions" => [
            "actionMoveDecree",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_ID, // Military token 2 if Decree 9 control is lost/gained
            SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_ID, // Military token 5 if Decree 9 control is lost/gained
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_SWAP_BUILDING_ID => [
        "name" => SevenWondersDuelPantheon::STATE_SWAP_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose 1 Blue or Green card of his opponent and in exchange give them 1 of his cards of the same color'),
        "descriptionmyturn" => clienttranslate('${you} must choose 1 Blue or Green card of your opponent and in exchange give them 1 of your cards of the same color'),
        "type" => "activeplayer",
        "action" => "enterStateSwapBuilding",
        "args" => "argSwapBuilding",
        "possibleactions" => [
            "actionSwapBuilding",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_TAKE_BUILDING_ID => [
        "name" => SevenWondersDuelPantheon::STATE_TAKE_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose 1 Brown or Grey card from his opponent and add it to his city'),
        "descriptionmyturn" => clienttranslate('${you} must choose 1 Brown or Grey card from your opponent and add it to your city'),
        "type" => "activeplayer",
        "action" => "enterStateTakeBuilding",
        "args" => "argTakeBuilding",
        "possibleactions" => [
            "actionTakeBuilding",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_ID => [
        "name" => SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must take one of the opponent\'s unconstructed Wonders and add it to his city'),
        "descriptionmyturn" => clienttranslate('${you} must take one of the opponent\'s unconstructed Wonders and add it to your city'),
        "type" => "activeplayer",
        "action" => "enterStateTakeUnconstructedWonder",
        "args" => "argTakeUnconstructedWonder",
        "possibleactions" => [
            "actionTakeUnconstructedWonder",
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID => [
        "name" => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStatePlayerSwitch",
        "transitions" => [
            SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_ID,
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelPantheon::STATE_SENATE_ACTIONS_ID,
            SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_NAME => SevenWondersDuelPantheon::STATE_PLAYER_SWITCH_ID, // In case all military token actions are skipped.
            SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_NAME => SevenWondersDuelPantheon::STATE_CHOOSE_AND_PLACE_DIVINITY_ID,
            SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuelPantheon::ZOMBIE_PASS => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_ID => [
        "name" => SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME,
        "description" => clienttranslate('End of game victory points count...'),
        "descriptionmyturn" => clienttranslate('End of game victory points count...'),
        "type" => "game",
        "action" => "enterStateNextPlayerTurn",
        "updateGameProgression" => true,
        "transitions" => [
            SevenWondersDuelPantheon::STATE_PLAYER_TURN_NAME => SevenWondersDuelPantheon::STATE_PLAYER_TURN_ID,
            SevenWondersDuelPantheon::STATE_NEXT_AGE_NAME => SevenWondersDuelPantheon::STATE_NEXT_AGE_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID,
            SevenWondersDuelPantheon::STATE_GAME_END_NAME => SevenWondersDuelPantheon::STATE_GAME_END_ID
        ]
    ],

    SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_ID => [
        "name" => SevenWondersDuelPantheon::STATE_GAME_END_DEBUG_NAME,
        "description" => clienttranslate("Debug End of game"),
        "descriptionmyturn" => clienttranslate('Debug End of game'),
        "type" => "activeplayer",
        "action" => "enterStateGameEndDebug",
        "args" => "argGameEndDebug",
        "possibleactions" => [
            "dummyAction"
        ],
        "transitions" => [
            SevenWondersDuelPantheon::STATE_GAME_END_NAME => SevenWondersDuelPantheon::STATE_GAME_END_ID
        ]
    ],

    // Dummy Active player state
//    SevenWondersDuelPantheon::STATE_ID_ => [
//        "name" => SevenWondersDuelPantheon::STATE_NAME_,
//        "description" => clienttranslate('${actplayer} must play a card or pass'),
//        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
//        "type" => "activeplayer",
//        "action" => "enterState",
//        "possibleactions" => [
//            SevenWondersDuelPantheon::STATE_NAME_,
//        ],
//        "transitions" => [
//            SevenWondersDuelPantheon::STATE_NAME_ => SevenWondersDuelPantheon::STATE_ID_,
//        ]
//    ],

    // Dummy Game state
//    SevenWondersDuelPantheon::STATE_ID_ => [
//        "name" => SevenWondersDuelPantheon::STATE_NAME_,
//        "description" => '',
//        "descriptionmyturn" => '',
//        "type" => "game",
//        "action" => "enterState",
//        "transitions" => [
//            SevenWondersDuelPantheon::STATE_NAME_ => SevenWondersDuelPantheon::STATE_ID_,
//        ]
//    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    SevenWondersDuelPantheon::STATE_GAME_END_ID => [
        "name" => SevenWondersDuelPantheon::STATE_GAME_END_NAME,
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ]

];



