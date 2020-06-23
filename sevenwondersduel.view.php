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
 * sevenwondersduel.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in sevenwondersduel_sevenwondersduel.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

use SWD\Player;

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_sevenwondersduel_sevenwondersduel extends game_view
{
    function getGameName() {
        return "sevenwondersduel";
    }

    function build_page($viewArgs) {
        // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count($players);

        /*********** Place your code below:  ************/

        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "block_board_progresstoken_container");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "board_player");

        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "player_row_info");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "player_row_buildings");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "player_row");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "player");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "draftpool");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "middle_column_block");

        for ($i = 0; $i < 5; $i++) {
            $this->page->insert_block("block_board_progresstoken_container", ["i" => $i]);
        }

        $boardPlayerClasses = ["board_player_left", "board_player_right"];
        $index = 0;
        foreach ([Player::opponent()->id, Player::me()->id] as $playerId) {
            $this->page->reset_subblocks('draftpool');
            $this->page->reset_subblocks('player');

            if ($playerId == Player::me()->id) {
                $this->page->insert_block("draftpool");

            }

            $player = $players[$playerId];
            $blocks = ["player_row_info", "player_row_buildings"];
            if ($playerId == Player::opponent()->id) {
                $blocks = array_reverse($blocks);
            }

            $this->page->reset_subblocks("player_row");
            foreach($blocks as $block) {
                foreach($blocks as $tmpBlock) $this->page->reset_subblocks($tmpBlock);

                $this->page->insert_block($block, array(
                    "PLAYER_ID" => $playerId,
                    "PLAYER_NAME" => $player['player_name'],
                    "PLAYER_COLOR" => $player['player_color']
                ));
                $this->page->insert_block("player_row");
            }

            $this->page->insert_block("board_player", array(
                "CLASS" => $boardPlayerClasses[$index],
                "PLAYER_ID" => $playerId,
                "PLAYER_NAME" => $player['player_name'],
                "PLAYER_COLOR" => $player['player_color']
            ));

            $this->page->insert_block("player", array(
                "PLAYER_ID" => $playerId,
                "PLAYER_NAME" => $player['player_name'],
                "PLAYER_COLOR" => $player['player_color'],
                "WHICH_PLAYER" => $playerId == Player::me()->id ? 'me' : 'opponent'
            ));
            $index++;


            $this->page->insert_block("middle_column_block");
        }

        // The "catalog". For testing spritesheets / tooltips.
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "block_catalog_wonder");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "block_catalog_building");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "block_catalog_progress_token");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "block_catalog");
        if (0) {
            $this->page->reset_subblocks('block_catalog_building');
            for ($id = 1; $id <= 77; $id++) {
                $spritesheetColumns = 10;
                $x = (($id - 1) % $spritesheetColumns);
                $y = floor(($id - 1) / $spritesheetColumns);

                $this->page->insert_block("block_catalog_building", [
                    'id' => $id,
                    'x' => $x,
                    'y' => $y,
                ]);
            }

            $this->page->reset_subblocks('block_catalog_wonder');
            for ($id = 1; $id <= 13; $id++) {
                $spritesheetColumns = 5;
                $x = (($id - 1) % $spritesheetColumns);
                $y = floor(($id - 1) / $spritesheetColumns);

                $this->page->insert_block("block_catalog_wonder", [
                    'id' => $id,
                    'x' => $x,
                    'y' => $y,
                ]);
            }

            $this->page->reset_subblocks('block_catalog_progress_token');
            for ($id = 1; $id <= 11; $id++) {
                $spritesheetColumns = 4;
                $x = (($id - 1) % $spritesheetColumns);
                $y = floor(($id - 1) / $spritesheetColumns);

                $this->page->insert_block("block_catalog_progress_token", [
                    'id' => $id,
                    'x' => $x,
                    'y' => $y,
                ]);
            }
            $this->page->insert_block("block_catalog");
        }


        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */

        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "sevenwondersduel_sevenwondersduel", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */


        /*********** Do not change anything below this line  ************/
    }
}
  

