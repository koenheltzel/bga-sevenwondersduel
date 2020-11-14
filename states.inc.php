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
 * states.inc.php
 *
 * SevenWondersDuel game states description
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
    SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuel::STATE_PLACE_INFLUENCE_ID,
    SevenWondersDuel::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_REMOVE_INFLUENCE_ID,
    SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID, // If remove influence is skipp
    SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // In case of instant victory
];

$constructBuildingTransitions = array_merge($militaryTokenTransitions, [
    // Science symbol pair
    SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_ID,
    // Conspirator
    SevenWondersDuel::STATE_CHOOSE_CONSPIRATOR_ACTION_NAME => SevenWondersDuel::STATE_CHOOSE_CONSPIRATOR_ACTION_ID,
    // Politician
    SevenWondersDuel::STATE_SENATE_ACTIONS_NAME => SevenWondersDuel::STATE_SENATE_ACTIONS_ID,
]);

$conspiracyStateTransitions = [
    // Military conspiracy --> Military track tokens
    SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID, // Conspiracies 2nd/3rd action
    SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuel::STATE_PLACE_INFLUENCE_ID,
    SevenWondersDuel::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_REMOVE_INFLUENCE_ID,
    SevenWondersDuel::STATE_CHOOSE_OPPONENT_BUILDING_NAME => SevenWondersDuel::STATE_CHOOSE_OPPONENT_BUILDING_ID,
    SevenWondersDuel::STATE_CONSTRUCT_BUILDING_FROM_BOX_NAME => SevenWondersDuel::STATE_CONSTRUCT_BUILDING_FROM_BOX_ID,
    SevenWondersDuel::STATE_CONSTRUCT_LAST_ROW_BUILDING_NAME => SevenWondersDuel::STATE_CONSTRUCT_LAST_ROW_BUILDING_ID,
    SevenWondersDuel::STATE_DESTROY_CONSTRUCTED_WONDER_NAME => SevenWondersDuel::STATE_DESTROY_CONSTRUCTED_WONDER_ID,
    SevenWondersDuel::STATE_DISCARD_AVAILABLE_CARD_NAME => SevenWondersDuel::STATE_DISCARD_AVAILABLE_CARD_ID,
    SevenWondersDuel::STATE_LOCK_PROGRESS_TOKEN_NAME => SevenWondersDuel::STATE_LOCK_PROGRESS_TOKEN_ID,
    SevenWondersDuel::STATE_MOVE_DECREE_NAME => SevenWondersDuel::STATE_MOVE_DECREE_ID,
    SevenWondersDuel::STATE_SWAP_BUILDING_NAME => SevenWondersDuel::STATE_SWAP_BUILDING_ID,
    SevenWondersDuel::STATE_TAKE_BUILDING_NAME => SevenWondersDuel::STATE_TAKE_BUILDING_ID,
    SevenWondersDuel::STATE_TAKE_UNCONSTRUCTED_WONDER_NAME => SevenWondersDuel::STATE_TAKE_UNCONSTRUCTED_WONDER_ID,
    SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME => SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID,
];

$machinestates = [

    // The initial state. Please do not modify.
    SevenWondersDuel::STATE_GAME_SETUP_ID => [
        "name" => SevenWondersDuel::STATE_GAME_SETUP_NAME,
        "description" => "",
        "type" => "manager",
        "action" => "enterStateGameSetup",
        "transitions" => ["" => SevenWondersDuel::STATE_SELECT_WONDER_ID]
    ],

    SevenWondersDuel::STATE_SELECT_WONDER_ID => [
        "name" => SevenWondersDuel::STATE_SELECT_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must choose a wonder'),
        "descriptionmyturn" => clienttranslate('${you} must choose a wonder'),
        "type" => "activeplayer",
        "action" => "enterStateSelectWonder",
        "args" => "argSelectWonder",
        "possibleactions" => [
            "actionSelectWonder",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_WONDER_SELECTED_NAME => SevenWondersDuel::STATE_WONDER_SELECTED_ID,
            SevenWondersDuel::STATE_CONSPIRE_NAME => SevenWondersDuel::STATE_CONSPIRE_ID,
            SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuel::STATE_PLACE_INFLUENCE_ID,
        ]
    ],

    SevenWondersDuel::STATE_WONDER_SELECTED_ID => [
        "name" => SevenWondersDuel::STATE_WONDER_SELECTED_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStateWonderSelected",
        "updateGameProgression" => true,
        "transitions" => [
            SevenWondersDuel::STATE_SELECT_WONDER_NAME => SevenWondersDuel::STATE_SELECT_WONDER_ID,
            SevenWondersDuel::STATE_NEXT_AGE_NAME => SevenWondersDuel::STATE_NEXT_AGE_ID
        ]
    ],

    SevenWondersDuel::STATE_NEXT_AGE_ID => [
        "name" => SevenWondersDuel::STATE_NEXT_AGE_NAME,
        "description" => clienttranslate('Preparing Age ${ageRoman}...'),
        "descriptionmyturn" => clienttranslate('Preparing Age ${ageRoman}...'),
        "type" => "game",
        "action" => "enterStateNextAge",
        "args" => "argNextAge",
        "transitions" => [
            SevenWondersDuel::STATE_SELECT_START_PLAYER_NAME => SevenWondersDuel::STATE_SELECT_START_PLAYER_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID
        ]
    ],

    SevenWondersDuel::STATE_SELECT_START_PLAYER_ID => [
        "name" => SevenWondersDuel::STATE_SELECT_START_PLAYER_NAME,
        "description" => clienttranslate('${actplayer} must choose who begins Age ${ageRoman}'),
        "descriptionmyturn" => clienttranslate('${you} must choose who begins Age ${ageRoman}'),
        "type" => "activeplayer",
        "action" => "enterStateSelectStartPlayer",
        "args" => "argSelectStartPlayer",
        "possibleactions" => [
            "actionSelectStartPlayer",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_START_PLAYER_SELECTED_NAME => SevenWondersDuel::STATE_START_PLAYER_SELECTED_ID
        ]
    ],

    SevenWondersDuel::STATE_START_PLAYER_SELECTED_ID => [
        "name" => SevenWondersDuel::STATE_START_PLAYER_SELECTED_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStateStartPlayerSelected",
        "transitions" => [
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
        ]
    ],
    SevenWondersDuel::STATE_PLAYER_TURN_ID => [
        "name" => SevenWondersDuel::STATE_PLAYER_TURN_NAME,
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
            "actionPrepareConspiracy",
        ],
        "transitions" => array_merge(
            $conspiracyStateTransitions, // Support all conspiracy actions
            $constructBuildingTransitions, // Support all construct building actions
            [
                SevenWondersDuel::STATE_PLAYER_TURN_NAME=> SevenWondersDuel::STATE_PLAYER_TURN_ID, // After triggering conspiracy
                SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME=> SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuel::STATE_CHOOSE_OPPONENT_BUILDING_NAME => SevenWondersDuel::STATE_CHOOSE_OPPONENT_BUILDING_ID,
                SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME => SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID,
                SevenWondersDuel::STATE_CHOOSE_DISCARDED_BUILDING_NAME => SevenWondersDuel::STATE_CHOOSE_DISCARDED_BUILDING_ID,
                SevenWondersDuel::STATE_TRIGGER_UNPREPARED_CONSPIRACY_NAME => SevenWondersDuel::STATE_TRIGGER_UNPREPARED_CONSPIRACY_ID,
                SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuel::STATE_PLACE_INFLUENCE_ID,
                SevenWondersDuel::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_REMOVE_INFLUENCE_ID,
                SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID, // If remove influence is skipped
                SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // For immediate victories, skipping special wonder action states.
                SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuel::STATE_CHOOSE_CONSPIRATOR_ACTION_ID => [
        "name" => SevenWondersDuel::STATE_CHOOSE_CONSPIRATOR_ACTION_NAME,
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
            SevenWondersDuel::STATE_CONSPIRE_NAME => SevenWondersDuel::STATE_CONSPIRE_ID,
            SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuel::STATE_PLACE_INFLUENCE_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_CONSPIRE_ID => [
        "name" => SevenWondersDuel::STATE_CONSPIRE_NAME,
        "description" => clienttranslate('${actplayer} must choose a Conspiracy'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Conspiracy'),
        "type" => "activeplayer",
        "action" => "enterStateConspire",
        "args" => "argConspire",
        "possibleactions" => [
            "actionChooseConspiracy",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_NAME => SevenWondersDuel::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_ID => [
        "name" => SevenWondersDuel::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_NAME,
        "description" => clienttranslate('${actplayer} must choose to place the other Conspiracy card on the top or the bottom of the deck'),
        "descriptionmyturn" => clienttranslate('${you} must choose to place the other Conspiracy card on the top or the bottom of the deck'),
        "type" => "activeplayer",
        "action" => "enterStateChooseConspireRemnantPosition",
        "args" => "argChooseConspireRemnantPosition",
        "possibleactions" => [
            "actionChooseConspireRemnantPosition",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_WONDER_SELECTED_NAME => SevenWondersDuel::STATE_WONDER_SELECTED_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_SENATE_ACTIONS_ID => [
        "name" => SevenWondersDuel::STATE_SENATE_ACTIONS_NAME,
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
            SevenWondersDuel::STATE_SENATE_ACTIONS_NAME => SevenWondersDuel::STATE_SENATE_ACTIONS_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuel::STATE_PLACE_INFLUENCE_ID => [
        "name" => SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME,
        "description" => clienttranslate('${actplayer} must choose a Senate chamber to add an Influence cube to'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Senate chamber to add an Influence cube to'),
        "type" => "activeplayer",
        "action" => "enterStatePlaceInfluence",
        "args" => "argPlaceInfluence",
        "possibleactions" => [
            "actionPlaceInfluence",
        ],
        "transitions" => array_merge($militaryTokenTransitions, [ // Remove and Move maybe are obvious, but Place after Place can happen too (Conspirator Place Influence on Military Decree, which triggers Military Token with Place Influence action).
            SevenWondersDuel::STATE_SENATE_ACTIONS_NAME => SevenWondersDuel::STATE_SENATE_ACTIONS_ID, // Going back to Senate Actions after a Senate Action triggered the Military Decree which collected a Military Token.
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID, // After Conspiracy Coup, after military token action.
            SevenWondersDuel::STATE_WONDER_SELECTED_NAME => SevenWondersDuel::STATE_WONDER_SELECTED_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuel::STATE_MOVE_INFLUENCE_ID => [
        "name" => SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME,
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
            SevenWondersDuel::STATE_SENATE_ACTIONS_NAME => SevenWondersDuel::STATE_SENATE_ACTIONS_ID, // Going back to Senate Actions after a Senate Action triggered the Military Decree which collected a Military Token.
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuel::STATE_REMOVE_INFLUENCE_ID => [
        "name" => SevenWondersDuel::STATE_REMOVE_INFLUENCE_NAME,
        "description" => clienttranslate('${actplayer} must choose one of the opponent\'s Influence cubes to remove'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Senate chamber to remove one of your opponent\'s Influence cubes from'),
        "type" => "activeplayer",
        "action" => "enterStateRemoveInfluence",
        "args" => "argRemoveInfluence",
        "possibleactions" => [
            "actionRemoveInfluence",
        ],
        "transitions" => array_merge($militaryTokenTransitions, [ // When a remove results in the military decree, which results in a military token
            SevenWondersDuel::STATE_SENATE_ACTIONS_NAME => SevenWondersDuel::STATE_SENATE_ACTIONS_ID, // Going back to Senate Actions after a Senate Action triggered the Military Decree which collected a Military Token.
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political supremacy
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuel::STATE_TRIGGER_UNPREPARED_CONSPIRACY_ID => [
        "name" => SevenWondersDuel::STATE_TRIGGER_UNPREPARED_CONSPIRACY_NAME,
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
                SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
                SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // For immediate victories
                SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_ID => [
        "name" => SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must choose a progress token'),
        "descriptionmyturn" => clienttranslate('${you} must choose a progress token'),
        "type" => "activeplayer",
        "action" => "enterStateChooseProgressToken",
        "args" => "argChooseProgressToken",
        "possibleactions" => [
            "actionChooseProgressToken",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID,
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_CHOOSE_OPPONENT_BUILDING_ID => [
        "name" => SevenWondersDuel::STATE_CHOOSE_OPPONENT_BUILDING_NAME,
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
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID, // Conspiracies, when skipping the discard action
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ])
    ],

    SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID => [
        "name" => SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME,
        "description" => clienttranslate('${actplayer} must choose a progress token from the box'),
        "descriptionmyturn" => clienttranslate('${you} must choose a progress token from the box'),
        "type" => "activeplayer",
        "action" => "enterStateChooseProgressTokenFromBox",
        "args" => "argChooseProgressTokenFromBox",
        "possibleactions" => [
            "actionChooseProgressTokenFromBox",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // LAW token > Scientific supremacy
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID, // Conspiracy Espionage
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_CHOOSE_DISCARDED_BUILDING_ID => [
        "name" => SevenWondersDuel::STATE_CHOOSE_DISCARDED_BUILDING_NAME,
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
                SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuel::STATE_CONSTRUCT_BUILDING_FROM_BOX_ID => [
        "name" => SevenWondersDuel::STATE_CONSTRUCT_BUILDING_FROM_BOX_NAME,
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
                SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
                SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
                SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuel::STATE_CONSTRUCT_LAST_ROW_BUILDING_ID => [
        "name" => SevenWondersDuel::STATE_CONSTRUCT_LAST_ROW_BUILDING_NAME,
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
                SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
                SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
                SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
                SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            ]
        )
    ],

    SevenWondersDuel::STATE_DESTROY_CONSTRUCTED_WONDER_ID => [
        "name" => SevenWondersDuel::STATE_DESTROY_CONSTRUCTED_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must choose one of the opponent\'s constructed Wonders and return it to the box'),
        "descriptionmyturn" => clienttranslate('${you} must choose one of the opponent\'s constructed Wonders and return it to the box'),
        "type" => "activeplayer",
        "action" => "enterStateDestroyConstructedWonder",
        "args" => "argDestroyConstructedWonder",
        "possibleactions" => [
            "actionDestroyConstructedWonder",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_DISCARD_AVAILABLE_CARD_ID => [
        "name" => SevenWondersDuel::STATE_DISCARD_AVAILABLE_CARD_NAME,
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
            SevenWondersDuel::STATE_DISCARD_AVAILABLE_CARD_NAME => SevenWondersDuel::STATE_DISCARD_AVAILABLE_CARD_ID,
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_LOCK_PROGRESS_TOKEN_ID => [
        "name" => SevenWondersDuel::STATE_LOCK_PROGRESS_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must choose a Progress token from the board, his opponent or the box and lock it away for the rest of the game'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Progress token from the board, your opponent or the box and lock it away for the rest of the game'),
        "type" => "activeplayer",
        "action" => "enterStateLockProgressToken",
        "args" => "argLockProgressToken",
        "possibleactions" => [
            "actionLockProgressToken",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_MOVE_DECREE_ID => [
        "name" => SevenWondersDuel::STATE_MOVE_DECREE_NAME,
        "description" => clienttranslate('${actplayer} must choose a Decree token to move to a Chamber of his choice, under the existing Decree'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Decree token to move to a Chamber of your choice, under the existing Decree'),
        "type" => "activeplayer",
        "action" => "enterStateMoveDecree",
        "args" => "argMoveDecree",
        "possibleactions" => [
            "actionMoveDecree",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuel::STATE_PLACE_INFLUENCE_ID, // Military token 2 if Decree 9 control is lost/gained
            SevenWondersDuel::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_REMOVE_INFLUENCE_ID, // Military token 5 if Decree 9 control is lost/gained
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_SWAP_BUILDING_ID => [
        "name" => SevenWondersDuel::STATE_SWAP_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose 1 Blue or Green card of his opponent and in exchange give them 1 of his cards of the same color'),
        "descriptionmyturn" => clienttranslate('${you} must choose 1 Blue or Green card of your opponent and in exchange give them 1 of your cards of the same color'),
        "type" => "activeplayer",
        "action" => "enterStateSwapBuilding",
        "args" => "argSwapBuilding",
        "possibleactions" => [
            "actionSwapBuilding",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_ID,
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID,
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_TAKE_BUILDING_ID => [
        "name" => SevenWondersDuel::STATE_TAKE_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose 1 Brown or Grey card from his opponent and add it to his city'),
        "descriptionmyturn" => clienttranslate('${you} must choose 1 Brown or Grey card from your opponent and add it to your city'),
        "type" => "activeplayer",
        "action" => "enterStateTakeBuilding",
        "args" => "argTakeBuilding",
        "possibleactions" => [
            "actionTakeBuilding",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_TAKE_UNCONSTRUCTED_WONDER_ID => [
        "name" => SevenWondersDuel::STATE_TAKE_UNCONSTRUCTED_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must take one of the opponent\'s unconstructed Wonders and add it to his city'),
        "descriptionmyturn" => clienttranslate('${you} must take one of the opponent\'s unconstructed Wonders and add it to your city'),
        "type" => "activeplayer",
        "action" => "enterStateTakeUnconstructedWonder",
        "args" => "argTakeUnconstructedWonder",
        "possibleactions" => [
            "actionTakeUnconstructedWonder",
        ],
        "transitions" => [
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_PLAYER_SWITCH_ID => [
        "name" => SevenWondersDuel::STATE_PLAYER_SWITCH_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStatePlayerSwitch",
        "transitions" => [
            SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuel::STATE_CHOOSE_PROGRESS_TOKEN_ID,
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_PLACE_INFLUENCE_NAME => SevenWondersDuel::STATE_PLACE_INFLUENCE_ID,
            SevenWondersDuel::STATE_REMOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_REMOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_MOVE_INFLUENCE_NAME => SevenWondersDuel::STATE_MOVE_INFLUENCE_ID,
            SevenWondersDuel::STATE_PLAYER_SWITCH_NAME => SevenWondersDuel::STATE_PLAYER_SWITCH_ID, // In case all military token actions are skipped.
            SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID, // Political / Military supremacy
            SevenWondersDuel::ZOMBIE_PASS => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuel::STATE_NEXT_PLAYER_TURN_ID => [
        "name" => SevenWondersDuel::STATE_NEXT_PLAYER_TURN_NAME,
        "description" => clienttranslate('End of game victory points count...'),
        "descriptionmyturn" => clienttranslate('End of game victory points count...'),
        "type" => "game",
        "action" => "enterStateNextPlayerTurn",
        "updateGameProgression" => true,
        "transitions" => [
            SevenWondersDuel::STATE_PLAYER_TURN_NAME => SevenWondersDuel::STATE_PLAYER_TURN_ID,
            SevenWondersDuel::STATE_NEXT_AGE_NAME => SevenWondersDuel::STATE_NEXT_AGE_ID,
            SevenWondersDuel::STATE_GAME_END_DEBUG_NAME => SevenWondersDuel::STATE_GAME_END_DEBUG_ID,
            SevenWondersDuel::STATE_GAME_END_NAME => SevenWondersDuel::STATE_GAME_END_ID
        ]
    ],

    SevenWondersDuel::STATE_GAME_END_DEBUG_ID => [
        "name" => SevenWondersDuel::STATE_GAME_END_DEBUG_NAME,
        "description" => clienttranslate("Debug End of game"),
        "descriptionmyturn" => clienttranslate('Debug End of game'),
        "type" => "activeplayer",
        "action" => "enterStateGameEndDebug",
        "args" => "argGameEndDebug",
        "possibleactions" => [
            "dummyAction"
        ],
        "transitions" => [
            SevenWondersDuel::STATE_GAME_END_NAME => SevenWondersDuel::STATE_GAME_END_ID
        ]
    ],

    // Dummy Active player state
//    SevenWondersDuel::STATE_ID_ => [
//        "name" => SevenWondersDuel::STATE_NAME_,
//        "description" => clienttranslate('${actplayer} must play a card or pass'),
//        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
//        "type" => "activeplayer",
//        "action" => "enterState",
//        "possibleactions" => [
//            SevenWondersDuel::STATE_NAME_,
//        ],
//        "transitions" => [
//            SevenWondersDuel::STATE_NAME_ => SevenWondersDuel::STATE_ID_,
//        ]
//    ],

    // Dummy Game state
//    SevenWondersDuel::STATE_ID_ => [
//        "name" => SevenWondersDuel::STATE_NAME_,
//        "description" => '',
//        "descriptionmyturn" => '',
//        "type" => "game",
//        "action" => "enterState",
//        "transitions" => [
//            SevenWondersDuel::STATE_NAME_ => SevenWondersDuel::STATE_ID_,
//        ]
//    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    SevenWondersDuel::STATE_GAME_END_ID => [
        "name" => SevenWondersDuel::STATE_GAME_END_NAME,
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ]

];



