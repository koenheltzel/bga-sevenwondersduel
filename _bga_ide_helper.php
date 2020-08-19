<?php

/**
 * This an IDE helper for developing games on Board Game Arena Studio.
 * Feel free to use it in your preferred IDE to get out these ugly undefined methods errors and to have a direct
 * documentation to classes / methods.
 *
 * @author Teddy Gandon
 * @see https://gist.github.com/TeddyGandon/9e3183475252203b1ede192688b463cd
 */

define('APP_GAMEMODULE_PATH', '');

/**
 * Class Table
 *
 * @property GameState gamestate
 * @property Deck cards
 */
class Table {

    /**
     * Not documented
     */
    public function reloadPlayersBasicInfos()
    {
    }

    /**
     * Not documented
     * @param $arr
     * @param $arr
     */
    public function reattributeColorsBasedOnPreferences($arr, $arr2)
    {
    }

    /**
     * Table constructor.
     */
    public function __construct()
    {
    }

    /**
     * Returns the number of players playing at the table
     * Note: doesn't work in setupNewGame so use count($players) instead
     */
    protected function getPlayersNumber()
    {
    }

    /**
     * Get the "active_player", whatever what is the current state type.
     * Note: it does NOT mean that this player is active right now, because state type could be "game" or "multiplayer"
     * Note: avoid using this method in a "multiplayer" state because it does not mean anything.
     */
    public function getActivePlayerId()
    {
    }

    /**
     * Get the "active_player" name
     * Note: avoid using this method in a "multiplayer" state because it does not mean anything.
     * @return string
     */
    function getActivePlayerName()
    {
    }

    /**
     * Get an associative array with generic data about players (ie: not game specific data).
     * The key of the associative array is the player id.
     * The content of each value is:
     * player_name
     * player_color (ex: ff0000)
     * @return array
     */
    function loadPlayersBasicInfos()
    {
    }

    /**
     * Get the "current_player". The current player is the one from which the action originated (the one who send the request).
     * Be careful: It is not always the active player.
     * In general, you shouldn't use this method, unless you are in "multiplayer" state.
     * @return int
     */
    protected function getCurrentPlayerId()
    {
        return 0;
    }

    /**
     * Get the "current_player" name
     * Be careful using this method (see above).
     * @return string
     */
    protected function getCurrentPlayerName()
    {
    }

    /**
     * Get the "current_player" color
     * Be careful using this method (see above).
     */
    protected function getCurrentPlayerColor()
    {
    }

    /**
     * Check the "current_player" zombie status. If true, player is zombie, i.e. left or was kicked out of the game.
     */
    protected function isCurrentPlayerZombie()
    {
    }

    /**
     * This is the generic method to access the database.
     * It can execute any type of SELECT/UPDATE/DELETE/REPLACE query on the database.
     * You should use it for UPDATE/DELETE/REPLACE query. For SELECT queries, the specialized methods below are much better.
     *
     * @param $sql
     * @return mysqli_result
     */
    function DbQuery($sql)
    {
    }

    /**
     * Returns a unique value from DB or null if no value is found.
     * $sql must be a SELECT query.
     * Raise an exception if more than 1 row is returned.
     *
     * @param $sql
     * @param $low_priority_select
     */
    protected static function getUniqueValueFromDB($sql, $low_priority_select = false)
    {
    }

    /**
     * Returns an associative array of rows for a sql SELECT query.
     * The key of the resulting associative array is the first field specified in the SELECT query.
     * The value of the resulting associative array if an associative array with all the field specified in the SELECT query and associated values.
     * First column must be a primary or alternate key.
     * The resulting collection can be empty.
     * If you specified $bSingleValue=true and if your SQL query request 2 fields A and B, the method returns an associative array "A=>B"
     * Example 1:
     * self::getCollectionFromDB( "SELECT player_id id, player_name name, player_score score FROM player" );
     *
     * Result:
     * array(
     * 1234 => array( 'id'=>1234, 'name'=>'myuser0', 'score'=>1 ),
     * 1235 => array( 'id'=>1235, 'name'=>'myuser1', 'score'=>0 )
     * )
     * Example 2:
     * self::getCollectionFromDB( "SELECT player_id id, player_name name FROM player", true );
     *
     * Result:
     * array(
     * 1234 => 'myuser0',
     * 1235 => 'myuser1'
     * )
     *
     * @param      $sql
     * @param bool $bSingleValue
     * @return array
     */
    protected function getCollectionFromDB($sql, $bSingleValue = FALSE)
    {
        return array();
    }

    /**
     * Idem than getCollectionFromDB, but raise an exception if the collection is empty
     *
     * @param $sql
     */
    protected function getNonEmptyCollectionFromDB($sql)
    {
    }

    /**
     * Returns one row for the sql SELECT query as an associative array or null if there is no result
     * Raise an exception if the query return more than one row
     * Example:
     * self::getObjectFromDB( "SELECT player_id id, player_name name, player_score score FROM player WHERE player_id='$player_id'" );
     *
     * Result:
     * array(
     * 'id'=>1234, 'name'=>'myuser0', 'score'=>1
     * )
     *
     * @param $sql
     * @return array
     */
    protected function getObjectFromDB($sql)
    {
    }

    /**
     * Idem than previous one, but raise an exception if no row is found
     *
     * @param $sql
     */
    protected function getNonEmptyObjectFromDB($sql)
    {
    }

    /**
     * Return an array of rows for a sql SELECT query.
     * the result if the same than "getCollectionFromDB" except that the result is a simple array (and not an associative array).
     * The result can be empty.
     * If you specified $bUniqueValue=true and if your SQL query request 1 field, the method returns directly an array of values.
     * Example 1:
     * self::getObjectListFromDB( "SELECT player_id id, player_name name, player_score score FROM player" );
     *
     * Result:
     * array(
     * array( 'id'=>1234, 'name'=>'myuser0', 'score'=>1 ),
     * array( 'id'=>1235, 'name'=>'myuser1', 'score'=>0 )
     * )
     * Example 2:
     * self::getObjectListFromDB( "SELECT player_name name FROM player", true );
     *
     * Result:
     * array(
     * 'myuser0',
     * 'myuser1'
     * )
     *
     * @param      $sql
     * @param bool $bUniqueValue
     */
    protected function getObjectListFromDB($sql, $bUniqueValue = FALSE)
    {
    }

    /**
     * Return an associative array of associative array, from a SQL SELECT query.
     * First array level correspond to first column specified in SQL query.
     * Second array level correspond to second column specified in SQL query.
     * If bSingleValue = true, keep only third column on result
     *
     * @param      $sql
     * @param bool $bSingleValue
     */
    protected function getDoubleKeyCollectionFromDB($sql, $bSingleValue = FALSE)
    {
    }

    /**
     * Return the PRIMARY key of the last inserted row (see PHP mysql_insert_id function).
     */
    protected function DbGetLastId()
    {
    }

    /**
     * Return the number of row affected by the last operation
     */
    protected function DbAffectedRow()
    {
    }

    /**
     * You must use this function on every string type data in your database that contains unsafe data.
     * (unsafe = can be modified by a player).
     * This method makes sure that no SQL injection will be done through the string used.
     * Note: if you using standard types in ajax actions, like AT_alphanum it is sanitized before arrival,
     * this is only needed if you manage to get unchecked string, like in the games where user has to enter text as a response.
     *
     * @param $string
     */
    protected function escapeStringForDB($string)
    {
    }

    /**
     * This method is located at the beginning of your game logic. This is the place you defines the globals used in your game logic, by assigning them IDs.
     * You can define up to 79 globals, with IDs from 10 to 89. You must NOT use globals outside this range as globals are used by other components of the framework.
     * self::initGameStateLabels( array(
     * "my_first_global_variable" => 10,
     * "my_second_global_variable" => 11
     * ) );
     *
     * @param $array
     */
    protected function initGameStateLabels($array)
    {
    }

    /**
     * Init your global value. Must be called before any use of your global, so you should call this method from your "setupNewGame" method.
     *
     * @param $value_label
     * @param $value_value
     */
    protected function setGameStateInitialValue($value_label, $value_value)
    {
    }

    /**
     * Retrieve the current value of a global.
     *
     * @param $value_label
     */
    public function getGameStateValue($value_label, $default = NULL)
    {
    }

    /**
     * Set the current value of a global.
     *
     * @param $value_label
     * @param $value_value
     */
    public function setGameStateValue($value_label, $value_value)
    {
    }

    /**
     * Increment the current value of a global. If increment is negative, decrement the value of the global.
     * Return the final value of the global.
     *
     * @param $value_label
     * @param $increment
     */
    public function incGameStateValue($value_label, $increment)
    {
    }

    /**
     * Check if action is valid regarding current game state (exception if fails).
     * The action is valid if it is listed as a "possibleactions" in the current game state (see game state description).
     * This method MUST be called in the first place in ALL your PHP methods that handle players action, in order to make sure a player can't do an action when the rules disallow it at this moment of the game.
     * if "bThrowException" is set to "false", the function return false in case of failure instead of throwing and exception. This is useful when several actions are possible in order to test each of them without throwing exceptions.
     *
     * @param      $actionName
     * @param bool $bThrowException
     */
    protected function checkAction($actionName, $bThrowException = TRUE)
    {
    }

    /**
     *Make the next player active in the natural player order.
     * Note: you CANT use this method in a "activeplayer" or "multipleactiveplayer" state. You must use a "game" type game state for this.
     */
    protected function activeNextPlayer()
    {
    }

    /**
     *Make the previous player active (in the natural player order).
     * Note: you CANT use this method in a "activeplayer" or "multipleactiveplayer" state. You must use a "game" type game state for this.
     */
    protected function activePrevPlayer()
    {
    }

    /**
     * Return an associative array which associate each player with the next player around the table.
     * In addition, key 0 is associated to the first player to play.
     * Example: if three player with ID 1, 2 and 3 are around the table, in this order, the method returns:
     * array(
     * 1 => 2,
     * 2 => 3,
     * 3 => 1,
     * 0 => 1
     * );
     */
    protected function getNextPlayerTable()
    {
    }

    /**
     * Same as getNextPlayerTable, but the associative array associate the previous player around the table.
     */
    protected function getPrevPlayerTable()
    {
    }

    /**
     * Get player playing after given player in natural playing order.
     *
     * @param $player_id
     */
    protected function getPlayerAfter($player_id)
    {
    }

    /**
     * Get player playing before given player in natural playing order.
     * Note: There is no API to modify this order, if you have custom player order you have to maintain it in your database and have custom function to access it.
     *
     * @param $player_id
     */
    protected function getPlayerBefore($player_id)
    {
    }

    /**
     * Send a notification to all players of the game.
     *
     * @param string $notification_type A string that defines the type of your notification. Your game interface Javascript logic will use this to know what is the type of the received notification (and to trigger the corresponding method).
     * @param string $notification_log  A string that defines what is to be displayed in the game log. You can use an empty string here (""). In this case, nothing is displayed in the game log. If you define a real string here, you should use "clienttranslate" method to make sure it can be translate. You can use arguments in your notification_log strings, that refers to values defines in the "notification_args" argument (see below). NB: Make sure you only use single quotes ('), otherwise PHP will try to interpolate the variable and will ignore the values in the args array. Note: you CAN use some HTML inside your notification log, and it is working. However: _ pay attention to keep the log clear. _ try to not include some HTML tags inside the "clienttranslate" method, otherwise it will make the translators work more difficult. You can use a notification argument instead, and provide your HTML through this argument.
     * @param array  $notification_args The arguments of your notifications, as an associative array. This array will be transmitted to the game interface logic, in order the game interface can be updated.
     */
    public function notifyAllPlayers($notification_type, $notification_log, $notification_args)
    {
    }

    /**
     * Same as above notifyAllPlayers, except that the notification is sent to one player only.
     * This method must be used each time some private information must be transmitted to a player.
     * Important: the variable for player name must be ${player_name} in order to be highlighted with the player color in the game log
     *
     * @param $player_id
     * @param $notification_type
     * @param $notification_log
     * @param $notification_args
     */
    public function notifyPlayer($player_id, $notification_type, $notification_log, $notification_args)
    {
    }

    /**
     * Create a statistic entry with a default value. This method must be called for each statistics of your game, in your setupNewGame method.
     * '$table_or_player' must be set to "table" if this is a table statistics, or "player" if this is a player statistics.
     * '$name' is the name of your statistics, as it has been defined in your stats.inc.php file.
     * '$value' is the initial value of the statistics. If this is a player statistics and if the player is not specified by "$player_id" argument, the value is set for ALL players.
     *
     * @param      $table_or_player
     * @param      $name
     * @param      $value
     * @param null $player_id
     */
    protected function initStat($table_or_player, $name, $value, $player_id = NULL)
    {
    }

    /**
     * Set a statistic $name to $value.
     * If "$player_id" is not specified, setStat consider it is a TABLE statistic.
     * If "$player_id" is specified, setStat consider it is a PLAYER statistic.
     *
     * @param      $value
     * @param      $name
     * @param null $player_id
     */
    public function setStat($value, $name, $player_id = NULL)
    {
    }

    /**
     * Increment (or decrement) specified statistic value by $delta value. Same behavior as setStat function.
     *
     * @param      $delta
     * @param      $name
     * @param null $player_id
     */
    public function incStat($delta, $name, $player_id = NULL)
    {
    }

    /**
     * Return the value of statistic specified by $name. Useful when creating derivative statistics such as average.
     *
     * @param      $name
     * @param null $player_id
     */
    public function getStat($name, $player_id = NULL)
    {
    }

    /**
     * Give standard extra time to this player.
     * Standard extra time depends on the speed of the game (small with "slow" game option, bigger with other options).
     * You can also specify an exact time to add, in seconds, with the "specified_time" argument (rarely used).
     *
     * @param      $player_id
     * @param null $specific_time
     */
    protected function giveExtraTime($player_id, $specific_time = NULL)
    {
    }

    /**
     * @param $module
     * @return mixed
     */
    protected function getNew($module)
    {
    }
}

class APP_Object {

}
class APP_DbObject {

}
/**
 * Class GameState
 */
class GameState {

    /**
     * Not documented
     * @param $str
     */
    public function updateMultiactiveOrNextState($str)
    {
    }

    /**
     * You can call this method to make any player active.
     * Note: you CANT use this method in a "activeplayer" or "multipleactiveplayer" state. You must use a "game" type game state for this.
     *
     * @param $player_id
     */
    public function changeActivePlayer($player_id)
    {
    }

    /**
     * With this method you can retrieve the list of the active player at any time.
     * During a "game" type gamestate, it will return a void array.
     * During a "activeplayer" type gamestate, it will return an array with one value (the active player id).
     * during a "multipleactiveplayer" type gamestate, it will return an array of the active players id.
     * Note: you should only use this method is the latter case.
     */
    public function getActivePlayerList()
    {
    }

    /**
     * With this method, all playing players are made active.
     * Usually, you use this method at the beginning (ex: "st" action method) of a multiplayer game state when all players have to do some action.
     */
    public function setAllPlayersMultiactive()
    {
    }

    /**
     * Make a specific list of players active during a multiactive gamestate.
     * Bare in mind it doesn't deactivate other previously active players.
     * "players" is the array of player id that should be made active.
     * In case "players" is empty, the method trigger the "next_state" transition to go to the next game state.
     *
     * @param $players
     * @param $next_state
     */
    public function setPlayersMultiactive($players, $next_state)
    {

    }

    /**
     * During a multiactive game state, make the specified player inactive.
     * Usually, you call this method during a multiactive game state after a player did his action.
     * If this player was the last active player, the method trigger the "next_state" transition to go to the next game state.
     *
     * @param $player_id
     * @param $next_state
     */
    public function setPlayerNonMultiactive($player_id, $next_state)
    {
    }

    /**
     * (rarely used)
     * This works exactly like "checkAction", except that it do NOT check if current player is active.
     * This is used specifically in certain game states when you want to authorize some additional actions for players that are not active at the moment.
     * Example: in Libertalia game, you want to authorize players to change their mind about card played. They are of course not active at the time they change their mind, so you cannot use "checkAction" and use "checkPossibleAction" instead.
     *
     * @param $action
     */
    public function checkPossibleAction($action)
    {
    }

    /**
     * Change current state to a new state. Important: parameter $transition is the name of the transition, and NOT the name of the target game state, see Your game state machine: states.inc.php for more information about states.
     *
     * @param $transition
     */
    public function nextState($transition)
    {
    }

    /**
     *Get an associative array of current game state attributes, see Your game state machine: states.inc.php for state attributes.
     * $state=$this->gamestate->state(); if( $state['name'] == 'myGameState' ) {...}
     * @return array
     */
    public function state()
    {
        return [];
    }
}

/**
 * Class APP_GameAction
 * @property array viewArgs
 * @property Table game
 * @property string view
 */
class APP_GameAction
{

    /**
     * @param string $arg
     * @return bool
     */
    function isArg($arg)
    {
        return true;
    }

    /**
     * @param string $message
     */
    function trace($message)
    {

    }

    /**
     * @param string $arg
     * @param string $type
     * @param bool $required
     * @return mixed
     */
    function getArg($arg, $type, $required)
    {
        return '';
    }

    /**
     *
     */
    function setAjaxMode()
    {

    }

    /**
     *
     */
    function ajaxResponse()
    {

    }

}

/**
 * Class Deck
 */
class Deck
{

    /**
     * To enable auto-reshuffle you must do "$this->cards->autoreshuffle = true" during the setup of the component.
     * Every time a card must be retrieved from the "deck" location, if it is empty the "discard" location will be automatically reshuffled into the "deck" location.
     * If you need to notify players when the deck is shuffled, you can setup a callback method using this feature: $this->cards->autoreshuffle_trigger = array('obj' => $this, 'method' => 'deckAutoReshuffle');
     * If you need to use other locations than "deck" and "discard" for auto-reshuffle feature, you can configure it this way: $this->cards->autoreshuffle_custom = array('deck' => 'discard'); (replace 'deck' and 'discard' with your custom locations).
     * @var bool
     */
    public $autoreshuffle = false;

    /**
     * Must be called before any other Deck method.
     * @param string $table_name name of the DB table used by this Deck component
     */
    public function init(string $table_name ) {

    }

    /**
     * Create card items in your deck component. Usually, all card items are created once, during the setup phase of the game.
     * Note: During the "createCards" process, Deck generates unique IDs for all card items.
     * Note: createCards is optimized to create a lot of cards at once. Do not use it to create cards one by one.
     * If "location" and "location_arg" arguments are not set, newly created cards are placed in the "deck" location.
     * If "location" (and optionally location_arg) is specified, cards are created for this specific location.
     * @param array $cards describe all cards that need to be created. "cards" is an array with the following format:
     * @param string $location
     * @param int $location_arg
     */
    public function createCards(array $cards, string $location='deck', int $location_arg=null ) {

    }

    /**
     * Pick a card from a "pile" location (ex: "deck") and place it in the "hand" of specified player.
     * Return the card picked or "null" if there are no more card in given location.
     * This method supports auto-reshuffle (see "auto-reshuffle" below).
     * @param $location
     * @param $player_id
     * @return array|null
     */
    public function pickCard(string $location, int $player_id ) : ?array {

    }

    /**
     * Pick "$nbr" cards from a "pile" location (ex: "deck") and place them in the "hand" of specified player.
     * Return an array with the cards picked, or "null" if there are no more card in given location.
     * Note that the number of cards picked can be less than "$nbr" in case there are not enough cards in the pile location.
     * This method supports auto-reshuffle (see "auto-reshuffle" below). In case there are not enough cards in the pile, all remaining cards are picked first, then the auto-reshuffle is triggered, then the other cards are picked.
     * @param $nbr
     * @param $location
     * @param $player_id
     * @return array|null
     */
    public function pickCards(int $nbr, string $location, int $player_id ) : ?array {

    }

    /**
     * This method is similar to 'pickCard', except that you can pick a card for any sort of location and not only the "hand" location.
     * This method supports auto-reshuffle (see "auto-reshuffle" below).
     * @param string $from_location is the "pile" style location from where you are picking a card
     * @param string $to_location is the location where you will place the card picked
     * @param int $location_arg if "location_arg" is specified, the card picked will be set with this "location_arg"
     */
    public function pickCardForLocation(string $from_location, string $to_location, int $location_arg=0 ) {

    }

    /**
     * This method is similar to 'pickCards', except that you can pick cards for any sort of location and not only the "hand" location.
     * This method supports auto-reshuffle (see "auto-reshuffle" below).
     * @param int $nbr
     * @param string $from_location is the "pile" style location from where you are picking some cards.
     * @param string $to_location is the location where you will place the cards picked.
     * @param int $location_arg if "location_arg" is specified, the cards picked will be set with this "location_arg".
     * @param bool $no_deck_reform if "no_deck_reform" is set to "true", the auto-reshuffle feature is disabled during this method call.
     */
    public function pickCardsForLocation(int $nbr, string $from_location, string $to_location, int $location_arg=0, bool $no_deck_reform=false ) {

    }

    /**
     * Move the specific card to given location.
     * @param int $card_id ID of the card to move.
     * @param string $location location where to move the card.
     * @param int $location_arg if specified, location_arg where to move the card. If not specified "location_arg" will be set to 0.
     */
    public function moveCard(int $card_id, string $location, int $location_arg=0 ) {

    }

    /**
     * Move the specific cards to given location.
     * @param array $cards an array of IDs of cards to move.
     * @param string $location location where to move the cards.
     * @param int $location_arg if specified, location_arg where to move the cards. If not specified "location_arg" will be set to 0.
     */
    public function moveCards(array $cards, string $location, int $location_arg ) {

    }

    /**
     * Move a card to a specific "pile" location where card are ordered.
     * If location_arg place is already taken, increment all cards after location_arg in order to insert new card at this precise location.
     * (note: insertCardOnExtremePosition method below is more useful in most of the case)
     * @param $card_id
     * @param $location
     * @param $location_arg
     */
    public function insertCard(int $card_id, string $location, int $location_arg ) {

    }

    /**
     * Move a card on top or at bottom of given "pile" type location.
     * @param $card_id
     * @param $location
     * @param $bOnTop
     */
    public function insertCardOnExtremePosition(int $card_id, string $location, bool $bOnTop ) {

    }

    /**
     * Move all cards in specified "from" location to given location.
     * @param string $from_location where to take the cards
     * @param string $to_location where to put the cards
     * @param int $from_location_arg if specified, only cards with given "location_arg" are moved.
     * @param int $to_location_arg if specified, cards moved "location_arg" is set to given value. Otherwise location_arg is set to zero.
     */
    public function moveAllCardsInLocation(string $from_location, string $to_location, int $from_location_arg=null, int $to_location_arg=0 ) {

    }

    /**
     * Move all cards in specified "from" location to given "to" location. This method does not modify the "location_arg" of cards.
     * @param $from_location
     * @param $to_location
     */
    public function moveAllCardsInLocationKeepOrder(string $from_location, string $to_location ) {

    }

    /**
     * Move specified card at the top of the "discard" location.
     * Note: this is an alias for: insertCardOnExtremePosition( $card_id, "discard", true )
     * @param $card_id
     */
    public function playCard(int $card_id ) {

    }

    /**
     * Get specific card information.
     * Return null if this card is not found.
     * @param $card_id
     * @return array|null
     */
    public function getCard(int $card_id ) : ?array {

    }

    /**
     * Get specific cards information.
     * If some cards are not found or if some cards IDs are specified multiple times, the method throws an (unexpected) Exception.
     * @param array $cards_array an array of cards ID.
     */
    public function getCards(array $cards_array ) {

    }

    /**
     * Get all cards in specific location, as an array. Return an empty array if the location is empty.
     * @param $location the location where to get the cards.
     * @param null $location_arg if specified, return only cards with the specified "location_arg".
     * @param null $order_by if specified, returned cards are ordered by the given database field. Example: "card_id" or "card_type".
     * @return array
     */
    public function getCardsInLocation(string $location, int $location_arg = null, string $order_by = null ) : array {

    }

    /**
     * Return the number of cards in specified location.
     * @param string $location the location where to count the cards.
     * @param int|null $location_arg if specified, count only cards with the specified "location_arg".
     * @return int
     */
    public function countCardInLocation(string $location, int $location_arg=null ) : int {

    }

    /**
     * Return the number of cards in each location of the game.
     * The method returns an associative array with the format "location" => "number of cards".
     * Example:
     *     array(
     *         'deck' => 12,
     *         'hand' => 21,
     *         'discard' => 54,
     *         'ontable' => 3
     *     );
     * @return array
     */
    public function countCardsInLocations() : array {

    }

    /**
     * Return the number of cards in each "location_arg" for the given location.
     * The method returns an associative array with the format "location_arg" => "number of cards".
     * Example: count the number of cards in each player's hand:
     *     countCardsByLocationArgs( 'hand' );
     *     // Result:
     *     array(
     *         122345 => 5,    // player 122345 has 5 cards in hand
     *         123456 => 4     // and player 123456 has 4 cards in hand
     *     );
     * @param string $location
     * @return int
     */
    public function countCardsByLocationArgs(string $location ) : int {

    }

    /**
     * Get all cards in given player hand.
     * Note: This is an alias for: getCardsInLocation( "hand", $player_id )
     * @param int $player_id
     * @return array
     */
    public function getPlayerHand(int $player_id ) : array {

    }

    /**
     * Get the card on top of the given ("pile" style) location, or null if the location is empty.
     * Note that the card pile won't be "auto-reshuffled" if there is no more card available.
     * @param string $location
     * @return array
     */
    public function getCardOnTop(string $location ) : array {

    }

    /**
     * Get the "$nbr" cards on top of the given ("pile" style) location.
     * The method return an array with at most "$nbr" elements (or a void array if there is no card in this location).
     * Note that the card pile won't be "auto-reshuffled" if there is not enough cards available.
     * @param int $nbr
     * @param string $location
     * @return array
     */
    public function getCardsOnTop(int $nbr, string $location ) : array {

    }

    /**
     * (rarely used)
     * Get the position of cards at the top of the given location / at the bottom of the given location.
     * Of course this method works only on location in "pile" where you are using "location_arg" to specify the position of each card (example: "deck" location).
     * If bGetMax=true, return the location of the top card of the pile.
     * If bGetMax=false, return the location of the bottom card of the pile.
     * @param bool $bGetMax
     * @param string $location
     * @return int
     */
    public function getExtremePosition(bool $bGetMax, string $location ) : int {

    }

    /**
     * Get all cards of a specific type (rarely used).
     * Return an array of cards, or an empty array if there is no cards of the specified type.
     * type: the type of cards
     * type_arg: if specified, return only cards with the specified "type_arg".
     * @param string $type the type of cards
     * @param int|null $type_arg if specified, return only cards with the specified "type_arg".
     * @return array
     */
    public function getCardsOfType(string $type, int $type_arg=null) : array {

    }

    /**
     * Get all cards of a specific type in a specific location (rarely used).
     * Return an array of cards, or an empty array if there is no cards of the specified type.
     * @param string $type the type of cards
     * @param int|null $type_arg if specified, return only cards with the specified "type_arg".
     * @param string $location the location where to get the cards.
     * @param int|null $location_arg if specified, return only cards with the specified "location_arg".
     * @return array
     */
    public function getCardsOfTypeInLocation(string $type, int $type_arg=null, string $location, int $location_arg = null ) : array {

    }

    /**
     * Shuffle all cards in specific location.
     * Shuffle only works on locations where cards are on a "pile" (ex: "deck").
     * Please note that all "location_arg" will be reset to reflect the new order of the cards in the pile.
     * @param string $location
     */
    public function shuffle(string $location ) {

    }
}

/**
 * Class feException
 */
class feException extends Exception
{

}

/**
 * Global
 */

/**
 * @param string $translation
 * @return string
 */
function totranslate($translation)
{
    return $translation;
}

/**
 * @param string $translation
 * @return string
 */
function clienttranslate($translation)
{
    return $translation;
}

/**
 * An argument type.
 * 'AT_alphanum' for a string with 0-9a-zA-Z_ and space
 */
define('AT_alphanum', '');

/**
 * An argument type.
 * 'AT_numberlist' for a list of several numbers separated with "," or ";" (ex: exemple: 1,4;2,3;-1,2).
 */
define('AT_numberlist', '');

/**
 * An argument type.
 * 'AT_posint' for a positive integer
 */
define('AT_posint', '');

/**
 * An argument type.
 * 'AT_float' for a float
 */
define('AT_float', '');

/**
 * An argument type.
 * 'AT_bool' for 1/0/true/false
 */
define('AT_bool', '');

/**
 * An argument type.
 * 'AT_enum' for an enumeration (argTypeDetails list the possible values as an array)
 */
define('AT_enum', '');

/**
 * An argument type.
 * 'AT_int' for an integer
 */
define('AT_int', '');
