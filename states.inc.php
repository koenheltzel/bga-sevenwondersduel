<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuelAgora implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * SevenWondersDuelAgora game states description
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


$machinestates = [

    // The initial state. Please do not modify.
    SevenWondersDuelAgora::STATE_GAME_SETUP_ID => [
        "name" => SevenWondersDuelAgora::STATE_GAME_SETUP_NAME,
        "description" => "",
        "type" => "manager",
        "action" => "enterStateGameSetup",
        "transitions" => ["" => SevenWondersDuelAgora::STATE_SELECT_WONDER_ID]
    ],

    SevenWondersDuelAgora::STATE_SELECT_WONDER_ID => [
        "name" => SevenWondersDuelAgora::STATE_SELECT_WONDER_NAME,
        "description" => clienttranslate('${actplayer} must choose a wonder'),
        "descriptionmyturn" => clienttranslate('${you} must choose a wonder'),
        "type" => "activeplayer",
        "action" => "enterStateSelectWonder",
        "args" => "argSelectWonder",
        "possibleactions" => [
            "actionSelectWonder",
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_WONDER_SELECTED_NAME => SevenWondersDuelAgora::STATE_WONDER_SELECTED_ID,
            SevenWondersDuelAgora::STATE_CONSPIRE_NAME => SevenWondersDuelAgora::STATE_CONSPIRE_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_WONDER_SELECTED_ID => [
        "name" => SevenWondersDuelAgora::STATE_WONDER_SELECTED_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStateWonderSelected",
        "updateGameProgression" => true,
        "transitions" => [
            SevenWondersDuelAgora::STATE_SELECT_WONDER_NAME => SevenWondersDuelAgora::STATE_SELECT_WONDER_ID,
            SevenWondersDuelAgora::STATE_NEXT_AGE_NAME => SevenWondersDuelAgora::STATE_NEXT_AGE_ID
        ]
    ],

    SevenWondersDuelAgora::STATE_NEXT_AGE_ID => [
        "name" => SevenWondersDuelAgora::STATE_NEXT_AGE_NAME,
        "description" => clienttranslate('Preparing Age ${ageRoman}...'),
        "descriptionmyturn" => clienttranslate('Preparing Age ${ageRoman}...'),
        "type" => "game",
        "action" => "enterStateNextAge",
        "args" => "argNextAge",
        "transitions" => [
            SevenWondersDuelAgora::STATE_SELECT_START_PLAYER_NAME => SevenWondersDuelAgora::STATE_SELECT_START_PLAYER_ID,
            SevenWondersDuelAgora::STATE_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_PLAYER_TURN_ID
        ]
    ],

    SevenWondersDuelAgora::STATE_SELECT_START_PLAYER_ID => [
        "name" => SevenWondersDuelAgora::STATE_SELECT_START_PLAYER_NAME,
        "description" => clienttranslate('${actplayer} must choose who begins Age ${ageRoman}'),
        "descriptionmyturn" => clienttranslate('${you} must choose who begins Age ${ageRoman}'),
        "type" => "activeplayer",
        "action" => "enterStateSelectStartPlayer",
        "args" => "argSelectStartPlayer",
        "possibleactions" => [
            "actionSelectStartPlayer",
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_START_PLAYER_SELECTED_NAME => SevenWondersDuelAgora::STATE_START_PLAYER_SELECTED_ID
        ]
    ],

    SevenWondersDuelAgora::STATE_START_PLAYER_SELECTED_ID => [
        "name" => SevenWondersDuelAgora::STATE_START_PLAYER_SELECTED_NAME,
        "description" => '',
        "descriptionmyturn" => '',
        "type" => "game",
        "action" => "enterStateStartPlayerSelected",
        "transitions" => [
            SevenWondersDuelAgora::STATE_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_PLAYER_TURN_ID => [
        "name" => SevenWondersDuelAgora::STATE_PLAYER_TURN_NAME,
        "description" => clienttranslate('Age ${ageRoman}: ${actplayer} must choose and use an age card'),
        "descriptionmyturn" => clienttranslate('Age ${ageRoman}: ${you} must choose an age card'),
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
        "transitions" => [
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME=> SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_ID,
            SevenWondersDuelAgora::STATE_CHOOSE_OPPONENT_BUILDING_NAME => SevenWondersDuelAgora::STATE_CHOOSE_OPPONENT_BUILDING_ID,
            SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME => SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID,
            SevenWondersDuelAgora::STATE_CHOOSE_DISCARDED_BUILDING_NAME => SevenWondersDuelAgora::STATE_CHOOSE_DISCARDED_BUILDING_ID,
            SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRATOR_ACTION_NAME => SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRATOR_ACTION_ID,
            SevenWondersDuelAgora::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelAgora::STATE_SENATE_ACTIONS_ID,
            SevenWondersDuelAgora::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelAgora::STATE_GAME_END_DEBUG_ID, // For immediate victories, skipping special wonder action states.
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRATOR_ACTION_ID => [
        "name" => SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRATOR_ACTION_NAME,
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
            SevenWondersDuelAgora::STATE_CONSPIRE_NAME => SevenWondersDuelAgora::STATE_CONSPIRE_ID,
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_CONSPIRE_ID => [
        "name" => SevenWondersDuelAgora::STATE_CONSPIRE_NAME,
        "description" => clienttranslate('${actplayer} must choose a Conspiracy'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Conspiracy'),
        "type" => "activeplayer",
        "action" => "enterStateConspire",
        "args" => "argConspire",
        "possibleactions" => [
            "actionChooseConspiracy",
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_NAME => SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_ID,
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_ID => [
        "name" => SevenWondersDuelAgora::STATE_CHOOSE_CONSPIRE_REMNANT_POSITION_NAME,
        "description" => clienttranslate('${actplayer} must choose to place the other Conspiracy card on the top or the bottom of the deck'),
        "descriptionmyturn" => clienttranslate('${you} must choose to place the other Conspiracy card on the top or the bottom of the deck'),
        "type" => "activeplayer",
        "action" => "enterStateChooseConspireRemnantPosition",
        "args" => "argChooseConspireRemnantPosition",
        "possibleactions" => [
            "actionChooseConspireRemnantPosition",
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::STATE_WONDER_SELECTED_NAME => SevenWondersDuelAgora::STATE_WONDER_SELECTED_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_SENATE_ACTIONS_ID => [
        "name" => SevenWondersDuelAgora::STATE_SENATE_ACTIONS_NAME,
        "description" => clienttranslate('${actplayer} must choose a Senate action (${senateActionsLeft} left)'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Senate action (${senateActionsLeft} left)'),
        "type" => "activeplayer",
        "action" => "enterStateSenateActions",
        "args" => "argSenateActions",
        "possibleactions" => [
            "actionSenateActionsPlaceInfluence",
            "actionSenateActionsMoveInfluence",
            "actionSenateActionsSkip",
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_SENATE_ACTIONS_NAME => SevenWondersDuelAgora::STATE_SENATE_ACTIONS_ID,
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_ID => [
        "name" => SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_NAME,
        "description" => clienttranslate('${actplayer} must choose a progress token'),
        "descriptionmyturn" => clienttranslate('${you} must choose a progress token'),
        "type" => "activeplayer",
        "action" => "enterStateChooseProgressToken",
        "args" => "argChooseProgressToken",
        "possibleactions" => [
            "actionChooseProgressToken",
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_CHOOSE_OPPONENT_BUILDING_ID => [
        "name" => SevenWondersDuelAgora::STATE_CHOOSE_OPPONENT_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose one of the opponent\'s ${buildingType} cards to discard'),
        "descriptionmyturn" => clienttranslate('${you} must choose one of the opponent\'s ${buildingType} cards to discard'),
        "type" => "activeplayer",
        "action" => "enterStateChooseOpponentBuilding",
        "args" => "argChooseOpponentBuilding",
        "possibleactions" => [
            "actionChooseOpponentBuilding",
            // If there are no building to discard, this state will be skipped automatically, so no need to have NEXT_PLAYER_TURN as a possible action.
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID => [
        "name" => SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME,
        "description" => clienttranslate('${actplayer} must choose a progress token from the box'),
        "descriptionmyturn" => clienttranslate('${you} must choose a progress token from the box'),
        "type" => "activeplayer",
        "action" => "enterStateChooseProgressTokenFromBox",
        "args" => "argChooseProgressTokenFromBox",
        "possibleactions" => [
            "actionChooseProgressTokenFromBox",
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_CHOOSE_DISCARDED_BUILDING_ID => [
        "name" => SevenWondersDuelAgora::STATE_CHOOSE_DISCARDED_BUILDING_NAME,
        "description" => clienttranslate('${actplayer} must choose a discarded building to construct'),
        "descriptionmyturn" => clienttranslate('${you} must choose a discarded building to construct'),
        "type" => "activeplayer",
        "action" => "enterStateChooseDiscardedBuilding",
        "args" => "argChooseDiscardedBuilding",
        "possibleactions" => [
            "actionChooseDiscardedBuilding",
            // If there is no discarded building to construct, this state will be skipped automatically, so no need to have NEXT_PLAYER_TURN as a possible action.
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
            SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_NAME => SevenWondersDuelAgora::STATE_CHOOSE_PROGRESS_TOKEN_ID,
            SevenWondersDuelAgora::ZOMBIE_PASS => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID,
        ]
    ],

    SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_ID => [
        "name" => SevenWondersDuelAgora::STATE_NEXT_PLAYER_TURN_NAME,
        "description" => clienttranslate('End of game victory points count...'),
        "descriptionmyturn" => clienttranslate('End of game victory points count...'),
        "type" => "game",
        "action" => "enterStateNextPlayerTurn",
        "updateGameProgression" => true,
        "transitions" => [
            SevenWondersDuelAgora::STATE_PLAYER_TURN_NAME => SevenWondersDuelAgora::STATE_PLAYER_TURN_ID,
            SevenWondersDuelAgora::STATE_NEXT_AGE_NAME => SevenWondersDuelAgora::STATE_NEXT_AGE_ID,
            SevenWondersDuelAgora::STATE_GAME_END_DEBUG_NAME => SevenWondersDuelAgora::STATE_GAME_END_DEBUG_ID,
            SevenWondersDuelAgora::STATE_GAME_END_NAME => SevenWondersDuelAgora::STATE_GAME_END_ID
        ]
    ],

    SevenWondersDuelAgora::STATE_GAME_END_DEBUG_ID => [
        "name" => SevenWondersDuelAgora::STATE_GAME_END_DEBUG_NAME,
        "description" => clienttranslate("Debug End of game"),
        "descriptionmyturn" => clienttranslate('Debug End of game'),
        "type" => "activeplayer",
        "action" => "enterStateGameEndDebug",
        "args" => "argGameEndDebug",
        "possibleactions" => [
            "dummyAction"
        ],
        "transitions" => [
            SevenWondersDuelAgora::STATE_GAME_END_NAME => SevenWondersDuelAgora::STATE_GAME_END_ID
        ]
    ],

    // Dummy Active player state
//    SevenWondersDuelAgora::STATE_ID_ => [
//        "name" => SevenWondersDuelAgora::STATE_NAME_,
//        "description" => clienttranslate('${actplayer} must play a card or pass'),
//        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
//        "type" => "activeplayer",
//        "action" => "enterState",
//        "possibleactions" => [
//            SevenWondersDuelAgora::STATE_NAME_,
//        ],
//        "transitions" => [
//            SevenWondersDuelAgora::STATE_NAME_ => SevenWondersDuelAgora::STATE_ID_,
//        ]
//    ],

    // Dummy Game state
//    SevenWondersDuelAgora::STATE_ID_ => [
//        "name" => SevenWondersDuelAgora::STATE_NAME_,
//        "description" => '',
//        "descriptionmyturn" => '',
//        "type" => "game",
//        "action" => "enterState",
//        "transitions" => [
//            SevenWondersDuelAgora::STATE_NAME_ => SevenWondersDuelAgora::STATE_ID_,
//        ]
//    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    SevenWondersDuelAgora::STATE_GAME_END_ID => [
        "name" => SevenWondersDuelAgora::STATE_GAME_END_NAME,
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ]

];



