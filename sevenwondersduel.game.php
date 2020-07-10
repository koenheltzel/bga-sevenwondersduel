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


class SevenWondersDuel extends Table
{

    use SWD\States\ChooseDiscardedBuildingTrait;
    use SWD\States\ChooseProgressTokenFromBoxTrait;
    use SWD\States\ChooseOpponentBuildingTrait;
    use SWD\States\ChooseProgressTokenTrait;
    use SWD\States\GameSetupTrait;
    use SWD\States\NextAgeTrait;
    use SWD\States\NextPlayerTurnTrait;
    use SWD\States\PlayerTurnTrait;
    use SWD\States\SelectStartPlayerTrait;
    use SWD\States\SelectWonderTrait;
    use SWD\States\StartPlayerSelectedTrait;
    use SWD\States\WonderSelectedTrait;

    /**
     * @var SevenWondersDuel
     */
    public static $instance;

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
    
    const STATE_GAME_END_ID = 99;
    const STATE_GAME_END_NAME = "gameEnd";

    const VALUE_CURRENT_WONDER_SELECTION_ROUND = "current_wonder_selection_round";
    const VALUE_CURRENT_AGE = "current_age";
    const VALUE_CONFLICT_PAWN_POSITION = "conflict_pawn_position";
    const VALUE_MILITARY_TOKEN1 = "military_token1";
    const VALUE_MILITARY_TOKEN2 = "military_token2";
    const VALUE_MILITARY_TOKEN3 = "military_token3";
    const VALUE_MILITARY_TOKEN4 = "military_token4";


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

    public static function get() {
        // We can assume self::$instance exists since SevenWondersDuel's constructor is the entry point for SWD code.
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
                self::VALUE_CURRENT_WONDER_SELECTION_ROUND => 10,
                self::VALUE_CURRENT_AGE => 11,
                self::VALUE_CONFLICT_PAWN_POSITION => 12,
                self::VALUE_MILITARY_TOKEN1 => 13,
                self::VALUE_MILITARY_TOKEN2 => 14,
                self::VALUE_MILITARY_TOKEN3 => 15,
                self::VALUE_MILITARY_TOKEN4 => 16,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );

        $this->buildingDeck = self::getNew( "module.common.deck" );
        $this->buildingDeck->init( "building" );

        $this->wonderDeck = self::getNew( "module.common.deck" );
        $this->wonderDeck->init( "wonder" );

        $this->progressTokenDeck = self::getNew( "module.common.deck" );
        $this->progressTokenDeck->init( "progress_token" );
	}

    public function getCurrentPlayerId($bReturnNullIfNotLogged = false) {
        return parent::getCurrentPlayerId($bReturnNullIfNotLogged);
    }

    public function getStartPlayerId() {
        $players = $this->loadPlayersBasicInfos();
        return array_shift($players)['player_id'];
    }

    public function getCurrentAge() {
        return $this->getGameStateValue(self::VALUE_CURRENT_AGE);
    }

    public function getConflictPawnPosition() {
        return $this->getGameStateValue(self::VALUE_CONFLICT_PAWN_POSITION);
    }

    public function setConflictPawnPosition($value) {
        return $this->setGameStateValue(self::VALUE_CONFLICT_PAWN_POSITION, $value);
    }

    public function getWonderSelectionRound() {
        return $this->getGameStateValue(self::VALUE_CURRENT_WONDER_SELECTION_ROUND);
    }


    /**
     * @param $number 1 to 4
     * @return int token value or 0 if no token
     */
    public function getMilitaryTokenValue($number) {
        return (int)$this->getGameStateValue(constant ( "self::VALUE_MILITARY_TOKEN{$number}"));
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
        return "sevenwondersduel";
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
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN1, 5);
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN2, 2);
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN3, 2);
        self::setGameStateInitialValue( self::VALUE_MILITARY_TOKEN4, 5);

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

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
        $sql = "SELECT player_id id, player_score score FROM player ";
        $players = self::getCollectionFromDb( $sql );
        $result['players'] = $players;
        $result['startPlayerId'] = $this->getStartPlayerId();
        $result['playerIds'] = [];
        foreach($players as $player) {
            $result['playerIds'][] = $player['id'];
        }

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        // Wonder selection stuff
        $result['wondersSituation'] = Wonders::getSituation();

        $result['discardedBuildings'] = SevenWondersDuel::get()->buildingDeck->getCardsInLocation('discard');
        $result['playerBuildings'] = [
            Player::me()->id => SevenWondersDuel::get()->buildingDeck->getCardsInLocation(Player::me()->id),
            Player::opponent()->id => SevenWondersDuel::get()->buildingDeck->getCardsInLocation(Player::opponent()->id),
        ];
        $result['playersSituation'] = Players::getSituation();
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
        $result['players'] = [
            Player::me()->id => json_decode(json_encode(Player::me()), true),
            Player::opponent()->id => json_decode(json_encode(Player::opponent()), true),
        ];
//        $result['cards'] = $this->buildingDeck;

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
        $agePercentage = ((($this->getCurrentAge() - 1) * $cardsPerAge) + Draftpool::countCardsInCurrentAge()) / $totalCards;

        return (int)($wonderPercentage * 10 + $agePercentage * 90);
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
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
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
}
