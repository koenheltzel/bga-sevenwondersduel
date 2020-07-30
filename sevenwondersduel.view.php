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


        $this->tpl['CONSTRUCT_WONDER'] = self::_("Construct a wonder");
        $this->tpl['CONSTRUCT_BUILDING'] = self::_("Construct building");
        $this->tpl['DISCARD_BUILDING'] = self::_("Discard for coins");
        $this->tpl['CHOOSE_PROGRESS_TOKEN_FROM_BOX'] = self::_("Choose a progress token from the box");
        $this->tpl['DISCARDED_BUILDINGS'] = self::_("Discarded buildings");

        // Tooltips
        $this->tpl['CURRENT_COST_YOU'] = self::_("Current construction cost for you");
        $this->tpl['CURRENT_COST_OPPONENT'] = self::_("Current construction cost for opponent");
        $this->tpl['WONDER'] = self::_("Wonder");
        $this->tpl['PROGRESS_TOKEN'] = self::_("Progress token");
        $this->tpl['TOTAL'] = self::_("Total");

        /*********** Place your code below:  ************/

        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "board_player_row_info");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "board_player_row_progress_tokens");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "board_player_row");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "board_player");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "board");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "board_column_block");

        $this->page->begin_block("sevenwondersduel_sevenwondersduel", 'player_buildings');
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "draftpool");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "end_game_category_header");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "end_game_category_player");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "end_game_player");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "end_game");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "middle_column_block");
        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "player_wonders");


        $boardPlayerClasses = ["board_player_left", "board_player_right"];
        $index = 0;
        foreach ([Player::opponent()->id, Player::me()->id] as $playerId) {
            $player = $players[$playerId];

            // Board column
            $this->page->reset_subblocks('board');
            $this->page->reset_subblocks('board_player');
            $this->page->reset_subblocks('board_player_row');

            if ($playerId == Player::me()->id) {
                $this->page->insert_block("board");
            }

            $playerInfoBlocks = ["board_player_row_info", "board_player_row_progress_tokens"];
            if ($playerId == Player::opponent()->id) {
                $playerInfoBlocks = array_reverse($playerInfoBlocks);
            }
            foreach($playerInfoBlocks as $block) {
                foreach($playerInfoBlocks as $tmpBlock) $this->page->reset_subblocks($tmpBlock);

                $this->page->insert_block($block, array(
                    "PLAYER_ID" => $playerId,
                    "PLAYER_NAME" => $player['player_name'],
                    "PLAYER_COLOR" => $player['player_color']
                ));
                $this->page->insert_block("board_player_row");
            }

            $this->page->insert_block("board_player", array(
                "CLASS" => $boardPlayerClasses[$index],
                "PLAYER_ID" => $playerId,
                "PLAYER_NAME" => $player['player_name'],
                "PLAYER_COLOR" => $player['player_color'],
                "PLAYER_ALIAS" => Player::get($playerId)->getAlias()
            ));

            $this->page->insert_block("board_column_block");

            // Middle column
            $this->page->reset_subblocks('draftpool');
            $this->page->reset_subblocks('end_game_category_player');
            $this->page->reset_subblocks('end_game_player');
            $this->page->reset_subblocks('end_game_category_header');
            $this->page->reset_subblocks('end_game');
            $this->page->reset_subblocks('player_buildings');

            if ($playerId == Player::me()->id) {
                $this->page->insert_block("draftpool");

                // END GAME TABLE
                if (1) {
                    $categories = ['blue', 'green', 'yellow', 'purple', 'wonders', 'progresstokens', 'coins', 'military'];
                    foreach ($categories as $category) {
                        $this->page->insert_block('end_game_category_header', array(
                            "CATEGORY" => $category,
                        ));
                    }

                    foreach ([Player::opponent()->id, Player::me()->id] as $tmpPlayerId) {
                        $tmpPlayer = $players[$tmpPlayerId];
                        $this->page->reset_subblocks('end_game_category_player');
                        foreach ($categories as $category) {
                            $this->page->insert_block('end_game_category_player', array(
                                "CATEGORY" => $category,
                                "PLAYER_ID" => $tmpPlayerId,
                            ));
                        }

                        $this->page->insert_block('end_game_player', array(
                            "PLAYER_ID" => $tmpPlayerId,
                            "PLAYER_NAME" => $tmpPlayer['player_name'],
                            "PLAYER_COLOR" => $tmpPlayer['player_color'],
                        ));
                    }

//                    foreach ($categories as $category) {
//                        $this->page->reset_subblocks('end_game_category_player');
//                        foreach ([Player::me()->id, Player::opponent()->id] as $tmpPlayerId) {
//                            $this->page->insert_block('end_game_category_player', array(
//                                "PLAYER_ID" => $tmpPlayerId,
//                            ));
//                        }
//                        $this->page->insert_block('end_game_category', array(
//                            "CATEGORY" => $category,
//                        ));
//                    }
                    $this->page->insert_block("end_game");
                }
            }

            $this->page->insert_block('player_buildings', array(
                "PLAYER_ID" => $playerId,
                "PLAYER_NAME" => $player['player_name'],
                "PLAYER_COLOR" => $player['player_color'],
                "PLAYER_ALIAS" => Player::get($playerId)->getAlias()
            ));
            $this->page->insert_block("middle_column_block");

            // Wonder column

            $this->page->insert_block("player_wonders", array(
                "CLASS" => $boardPlayerClasses[$index],
                "PLAYER_ID" => $playerId,
                "PLAYER_NAME" => $player['player_name'],
                "PLAYER_COLOR" => $player['player_color'],
                "PLAYER_ALIAS" => Player::get($playerId)->getAlias()
            ));

            $index++;


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

        $this->page->begin_block("sevenwondersduel_sevenwondersduel", "swd");
        $this->page->insert_block("swd", [
            "PLAYER_ME_ID" => Player::me()->id,
            "PLAYER_OPPONENT_ID" => Player::opponent()->id,
        ]);

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
  

