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
  * sevenwondersduel.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

use SWD\Draftpool;
use SWD\Material;
use SWD\MilitaryTrack;
use SWD\Player;
use SWD\Players;
use SWD\ProgressTokens;
use SWD\Wonder;
use SWD\Wonders;

// SWD namespace autoloader from /modules/php/ folder.
$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'SWD') {
        array_shift($classParts);
        $file = dirname(__FILE__) . "/modules/php/" . implode(DIRECTORY_SEPARATOR, $classParts) . ".php";
        if (file_exists($file)) {
            require_once($file);
        }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once('modules/php/functions.php');
if (0) require_once '_bga_ide_helper.php';


class SevenWondersDuelAgora extends Table
{

    use SWD\States\ChooseDiscardedBuildingTrait;
    use SWD\States\ChooseProgressTokenFromBoxTrait;
    use SWD\States\ChooseOpponentBuildingTrait;
    use SWD\States\ChooseProgressTokenTrait;
    use SWD\States\GameEndDebugTrait;
    use SWD\States\GameSetupTrait;
    use SWD\States\NextAgeTrait;
    use SWD\States\NextPlayerTurnTrait;
    use SWD\States\PlayerTurnTrait;
    use SWD\States\SelectStartPlayerTrait;
    use SWD\States\SelectWonderTrait;
    use SWD\States\StartPlayerSelectedTrait;
    use SWD\States\WonderSelectedTrait;

    /**
     * @var SevenWondersDuelAgora
     */
    public static $instance;

    // Game state ids & names
    const STATE_GAME_SETUP_ID = 1;
    const STATE_GAME_SETUP_NAME = "gameSetup";
    
    const STATE_SELECT_WONDER_ID = 5;
    const STATE_SELECT_WONDER_NAME = "selectWonder";
    
    const STATE_WONDER_SELECTED_ID = 10;
    const STATE_WONDER_SELECTED_NAME = "wonderSelected";
    
    const STATE_NEXT_AGE_ID = 15;
    const STATE_NEXT_AGE_NAME = "nextAge";
    
    const STATE_SELECT_START_PLAYER_ID = 20;
    const STATE_SELECT_START_PLAYER_NAME = "selectStartPlayer";
    
    const STATE_START_PLAYER_SELECTED_ID = 25;
    const STATE_START_PLAYER_SELECTED_NAME = "startPlayerSelected";
    
    const STATE_PLAYER_TURN_ID = 30;
    const STATE_PLAYER_TURN_NAME = "playerTurn";

    const STATE_CHOOSE_PROGRESS_TOKEN_ID = 45;
    const STATE_CHOOSE_PROGRESS_TOKEN_NAME = "chooseProgressToken";

    const STATE_CHOOSE_OPPONENT_BUILDING_ID = 65;
    const STATE_CHOOSE_OPPONENT_BUILDING_NAME = "chooseOpponentBuilding";

    const STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_ID = 75;
    const STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME = "chooseProgressTokenFromBox";

    const STATE_CHOOSE_DISCARDED_BUILDING_ID = 85;
    const STATE_CHOOSE_DISCARDED_BUILDING_NAME = "chooseDiscardedBuilding";

    const STATE_NEXT_PLAYER_TURN_ID = 95;
    const STATE_NEXT_PLAYER_TURN_NAME = "nextPlayerTurn";

    const STATE_GAME_END_DEBUG_ID = 98;
    const STATE_GAME_END_DEBUG_NAME = "gameEndDebug";

    const STATE_GAME_END_ID = 99;
    const STATE_GAME_END_NAME = "gameEnd";

    const ZOMBIE_PASS = "zombiePass";

    // Global value labels
    const VALUE_CURRENT_WONDER_SELECTION_ROUND = "current_wonder_selection_round";
    const VALUE_CURRENT_AGE = "current_age";
    const VALUE_CONFLICT_PAWN_POSITION = "conflict_pawn_position";
    const VALUE_MILITARY_TOKEN1 = "military_token1";
    const VALUE_MILITARY_TOKEN2 = "military_token2";
    const VALUE_MILITARY_TOKEN3 = "military_token3";
    const VALUE_MILITARY_TOKEN4 = "military_token4";
    const VALUE_EXTRA_TURN_NORMAL = "extra_turn";
    const VALUE_EXTRA_TURN_THROUGH_THEOLOGY = "extra_turn_through_theology";
    const VALUE_AGE_START_PLAYER = "age_start_player";
    const VALUE_DISCARD_OPPONENT_BUILDING_WONDER = "discard_opponent_building_type";
    const VALUE_END_GAME_CONDITION = "end_game_condition";

    // Game options (variants)
    const OPTION_AGORA = "option_agora";
    const OPTION_AGORA_WONDERS = "option_agora_wonders";
    const OPTION_AGORA_PROGRESS_TOKENS = "option_agora_progress_tokens";

    // End game scoring categories
    const SCORE_WONDERS = "wonders";
    const SCORE_PROGRESSTOKENS = "progresstokens";
    const SCORE_COINS = "coins";
    const SCORE_MILITARY = "military";

    // End game conditions
    const END_GAME_CONDITION_SCIENTIFIC = 1;
    const END_GAME_CONDITION_MILITARY = 2;
    const END_GAME_CONDITION_NORMAL = 3;
    const END_GAME_CONDITION_NORMAL_AUX = 4;
    const END_GAME_CONDITION_DRAW = 5;

    // Statistics
    const STAT_TURNS_NUMBER = "turns_number";
    const STAT_CIVILIAN_VICTORY = "civilian_victory";
    const STAT_SCIENTIFIC_SUPREMACY = "scientific_supremacy";
    const STAT_MILITARY_SUPREMACY = "military_supremacy";
    const STAT_DRAW = "draw";
    const STAT_VICTORY_POINTS = "victory_points";
    const STAT_VP_BLUE = "vp_blue";
    const STAT_VP_GREEN = "vp_green";
    const STAT_VP_YELLOW = "vp_yellow";
    const STAT_VP_PURPLE = "vp_purple";
    const STAT_VP_WONDERS = "vp_wonders";
    const STAT_VP_PROGRESS_TOKENS = "vp_progress_tokens";
    const STAT_VP_COINS = "vp_coins";
    const STAT_VP_MILITARY = "vp_military";
    const STAT_BROWN_CARDS = "brown_cards";
    const STAT_GREY_CARDS = "grey_cards";
    const STAT_YELLOW_CARDS = "yellow_cards";
    const STAT_RED_CARDS = "red_cards";
    const STAT_BLUE_CARDS = "blue_cards";
    const STAT_GREEN_CARDS = "green_cards";
    const STAT_PURPLE_CARDS = "purple_cards";
    const STAT_BUILDINGS_CONSTRUCTED = "buildings_constructed";
    const STAT_WONDERS_CONSTRUCTED = "wonders_constructed";
    const STAT_PROGRESS_TOKENS = "progress_tokens";
    const STAT_SHIELDS = "shields";
    const STAT_SCIENCE_SYMBOLS = "science_symbols";
    const STAT_EXTRA_TURNS = "extra_turns";
    const STAT_DISCARDED_CARDS = "discarded_cards";
    const STAT_CHAINED_CONSTRUCTIONS = "chained_constructions";

    /**
     * @var Deck
     */
    public $buildingDeck;

    /**
     * @var Deck
     */
    public $wonderDeck;

    /**
     * @var Deck
     */
    public $progressTokenDeck;

    /**
     * @var Deck
     */
    public $decreeDeck;

    /**
     * @var Deck
     */
    public $conspiracyDeck;

    /**
     * @var Deck
     */
    public $influenceCubeDeck;

    public static function get() {
        // We can assume self::$instance exists since SevenWondersDuelAgora's constructor is the entry point for SWD code.
        return self::$instance;
    }

	function __construct( )
	{
	    self::$instance = $this;

        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels( array(
                // Global variables
                self::VALUE_CURRENT_WONDER_SELECTION_ROUND => 10,
                self::VALUE_CURRENT_AGE => 11,
                self::VALUE_CONFLICT_PAWN_POSITION => 12,
                self::VALUE_MILITARY_TOKEN1 => 13,
                self::VALUE_MILITARY_TOKEN2 => 14,
                self::VALUE_MILITARY_TOKEN3 => 15,
                self::VALUE_MILITARY_TOKEN4 => 16,
                self::VALUE_EXTRA_TURN_NORMAL => 17,
                self::VALUE_AGE_START_PLAYER => 18,
                self::VALUE_EXTRA_TURN_THROUGH_THEOLOGY => 19,
                self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER => 20,
                self::VALUE_END_GAME_CONDITION => 21,
                // Game variants
                self::OPTION_AGORA => 110,
                self::OPTION_AGORA_WONDERS => 111,
                self::OPTION_AGORA_PROGRESS_TOKENS => 112,
        ) );

        $this->buildingDeck = self::getNew( "module.common.deck" );
        $this->buildingDeck->init( "building" );

        $this->wonderDeck = self::getNew( "module.common.deck" );
        $this->wonderDeck->init( "wonder" );

        $this->progressTokenDeck = self::getNew( "module.common.deck" );
        $this->progressTokenDeck->init( "progress_token" );

        // Start Agora
        // Checking wether Agora is active isn't possible here. So create the deck objects anyway.
        $this->decreeDeck = self::getNew( "module.common.deck" );
        $this->decreeDeck->init( "decree" );

        $this->conspiracyDeck = self::getNew( "module.common.deck" );
        $this->conspiracyDeck->init( "conspiracy" );

        $this->influenceCubeDeck = self::getNew( "module.common.deck" );
        $this->influenceCubeDeck->init( "influence_cube" );
        // End Agora
	}

    /**
     * The player that started the game. We use this to determine the military track position (because that only gets tracked once).
     * @return mixed
     */
    public function getGameStartPlayerId() {
        $players = $this->loadPlayersBasicInfos();
        return array_shift($players)['player_id'];
    }

    public function getPlayerBasicInfo($playerId) {
        $players = $this->loadPlayersBasicInfos();
        return $players[$playerId];
    }

    /**
     * @param $number 1 to 4
     * @return int token value or 0 if no token
     */
    public function getMilitaryTokenValue($number) {
        return $this->getGameStateValue(constant ( "self::VALUE_MILITARY_TOKEN{$number}"));
    }

    /**
     * @param $number 1 to 4
     * @return int token value or 0 if no token
     */
    public function takeMilitaryToken($number) {
        $value = $this->getMilitaryTokenValue($number);
        if ($value > 0) {
            $this->setGameStateValue(constant ( "self::VALUE_MILITARY_TOKEN{$number}"), 0);
        }
        return $value;
    }

    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "sevenwondersduelagora";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( self::VALUE_CURRENT_WONDER_SELECTION_ROUND, 1);
        self::setGameStateInitialValue( self::VALUE_CURRENT_AGE, 0);
        self::setGameStateInitialValue( self::VALUE_CONFLICT_PAWN_POSITION, 0);
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN1, 5);
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN2, 2);
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN3, 2);
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN4, 5);
        self::setGameStateInitialValue( self::VALUE_EXTRA_TURN_NORMAL, 0);
        self::setGameStateInitialValue( self::VALUE_AGE_START_PLAYER, 0);
        self::setGameStateInitialValue( self::VALUE_EXTRA_TURN_THROUGH_THEOLOGY, 0);
        self::setGameStateInitialValue( self::VALUE_DISCARD_OPPONENT_BUILDING_WONDER, 0);
        self::setGameStateInitialValue( self::VALUE_END_GAME_CONDITION, 0);

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        // Table statistics
        self::initStat('table', self::STAT_TURNS_NUMBER, 0);
        self::initStat('table', self::STAT_CIVILIAN_VICTORY, 0);
        self::initStat('table', self::STAT_SCIENTIFIC_SUPREMACY, 0);
        self::initStat('table', self::STAT_MILITARY_SUPREMACY, 0);
        self::initStat('table', self::STAT_DRAW, 0);
        // Player statistics (for all players)
        self::initStat('player', self::STAT_TURNS_NUMBER, 0);
        self::initStat('player', self::STAT_CIVILIAN_VICTORY, 0);
        self::initStat('player', self::STAT_SCIENTIFIC_SUPREMACY, 0);
        self::initStat('player', self::STAT_MILITARY_SUPREMACY, 0);
        self::initStat('player', self::STAT_DRAW, 0);
        self::initStat('player', self::STAT_VICTORY_POINTS, 0);
        self::initStat('player', self::STAT_VP_BLUE, 0);
        self::initStat('player', self::STAT_VP_GREEN, 0);
        self::initStat('player', self::STAT_VP_YELLOW, 0);
        self::initStat('player', self::STAT_VP_PURPLE, 0);
        self::initStat('player', self::STAT_VP_WONDERS, 0);
        self::initStat('player', self::STAT_VP_PROGRESS_TOKENS, 0);
        self::initStat('player', self::STAT_VP_COINS, 0);
        self::initStat('player', self::STAT_VP_MILITARY, 0);
        self::initStat('player', self::STAT_BROWN_CARDS, 0);
        self::initStat('player', self::STAT_GREY_CARDS, 0);
        self::initStat('player', self::STAT_YELLOW_CARDS, 0);
        self::initStat('player', self::STAT_RED_CARDS, 0);
        self::initStat('player', self::STAT_BLUE_CARDS, 0);
        self::initStat('player', self::STAT_GREEN_CARDS, 0);
        self::initStat('player', self::STAT_PURPLE_CARDS, 0);
        self::initStat('player', self::STAT_WONDERS_CONSTRUCTED, 0);
        self::initStat('player', self::STAT_PROGRESS_TOKENS, 0);
        self::initStat('player', self::STAT_SHIELDS, 0);
        self::initStat('player', self::STAT_SCIENCE_SYMBOLS, 0);
        self::initStat('player', self::STAT_EXTRA_TURNS, 0);
        self::initStat('player', self::STAT_DISCARDED_CARDS, 0);
        self::initStat('player', self::STAT_CHAINED_CONSTRUCTIONS, 0);

        // TODO: setup the initial game situation here

        $this->enterStateGameSetup(); // This state function isn't called automatically apparently.

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score_total score FROM player ";
        $players = self::getCollectionFromDb( $sql );
        $result['players'] = $players;
        $result['startPlayerId'] = $this->getGameStartPlayerId();
        $result['playerIds'] = [];
        foreach($players as $player) {
            $result['playerIds'][] = $player['id'];
        }

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        // Wonder selection stuff
        $result['wondersSituation'] = Wonders::getSituation();

        $me = Player::me();
        $opponent = Player::opponent();
        $result['discardedBuildings'] = SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation('discard', null, 'card_location_arg');
        $result['playerBuildings'] = [
            $me->id => SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation($me->id, null, 'card_location_arg'),
            $opponent->id => SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation($opponent->id, null, 'card_location_arg'),
        ];
        $result['playersSituation'] = Players::getSituation((int)$this->getGameStateValue(self::VALUE_END_GAME_CONDITION) != 0);
        $result['buildings'] = Material::get()->buildings->array;
        $result['wonders'] = Material::get()->wonders->array;
        $result['progressTokens'] = Material::get()->progressTokens->array;
        $result['draftpool'] = [];
        if (count($result['wondersSituation']['selection']) == 0) {
            $result['draftpool'] = Draftpool::get();
        }
        $result['militaryTrack'] = MilitaryTrack::getData();
        $result['progressTokensSituation'] = ProgressTokens::getSituation();
        $result['buildingIdsToLinkIconId'] = Material::get()->buildingIdsToLinkIconId;
        $result['me_id'] = $me->id;
        $result['opponent_id'] = $opponent->id;
        $result['players'] = [
            $me->id => json_decode(json_encode($me), true),
            $opponent->id => json_decode(json_encode($opponent), true),
        ];
        $result['agora'] = (int)$this->getGameStateValue(self::OPTION_AGORA);
        if ($result['agora']) {
            $result['conspiracies'] = Material::get()->conspiracies->array;
            $result['decrees'] = Material::get()->decrees->array;
        }

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $wonderSelection1 = count(Wonders::getDeckCardsSorted("selection1"));
        $wonderSelection2 = count(Wonders::getDeckCardsSorted("selection2"));
        $wonderPercentage = (8 - $wonderSelection1 - $wonderSelection2) / 8;

        $cardsPerAge = 20;
        $totalCards = $cardsPerAge * 3;
        $agePercentage = 0;
        if ($this->getGameStateValue(self::VALUE_CURRENT_AGE) > 0) {
            $agePercentage = ((($this->getGameStateValue(self::VALUE_CURRENT_AGE) - 1) * $cardsPerAge) + ($cardsPerAge - Draftpool::countCardsInCurrentAge())) / $totalCards;
        }

        return (int)($wonderPercentage * 8 + $agePercentage * 92);
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in sevenwondersduel.action.php)
    */

    /*

    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' );

        $player_id = self::getActivePlayerId();

        // Add your game logic to play a card there
        ...

        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );

    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    /*

    Example for game state "MyGameState":

    function enterStateMyGameState()
    {
        // Do some stuff ...

        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
        switch($state['name']) {
            case self::STATE_SELECT_WONDER_NAME:
                $wonderSelectionRound = $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
                $cards = Wonders::getDeckCardsSorted("selection{$wonderSelectionRound}");
                $card = array_shift($cards);
                $wonderId = $card['id'];

                $this->performActionSelectWonder(Player::get($active_player), $wonderId);
                break;
            case self::STATE_SELECT_START_PLAYER_NAME:
                // Set the opponent of the zombie as start player of the next age.
                $this->performActionSelectStartPlayer(Player::opponent($active_player));
                break;
            case self::STATE_PLAYER_TURN_NAME:
            case self::STATE_CHOOSE_PROGRESS_TOKEN_NAME:
            case self::STATE_CHOOSE_OPPONENT_BUILDING_NAME:
            case self::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME:
            case self::STATE_CHOOSE_DISCARDED_BUILDING_NAME:
                $this->gamestate->nextState( self::ZOMBIE_PASS );
                break;
            default:
                throw new feException( "Zombie mode not supported at this game state: ".$state['name'] );
        }
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }

    /* Below we make some protected functions public, so the other SWD classes can use them. */

    /**
     * Get the "current_player". The current player is the one from which the action originated (the one who send the request).
     * Be careful: It is not always the active player.
     * In general, you shouldn't use this method, unless you are in "multiplayer" state.
     * @return int
     */
    public function getCurrentPlayerId($bReturnNullIfNotLogged = false) {
        return parent::getCurrentPlayerId($bReturnNullIfNotLogged);
    }

    // Make _() public so we can call it from Base
    public function _($string)
    {
        return parent::_($string);
    }

}
