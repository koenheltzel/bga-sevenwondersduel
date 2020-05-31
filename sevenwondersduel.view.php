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

use \SWD\Building;
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_sevenwondersduel_sevenwondersduel extends game_view
  {
    function getGameName() {
        return "7 Wonders Duel";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

        $this->page->begin_block( "sevenwondersduel_sevenwondersduel", "block_age_building" );
        $this->page->begin_block( "sevenwondersduel_sevenwondersduel", "block_age" );

        $this->page->begin_block( "sevenwondersduel_sevenwondersduel", "block_wonder" );
        $this->page->begin_block( "sevenwondersduel_sevenwondersduel", "block_building" );

        $ages = [
            1 => [
                [5,7],
                [4,6,8],
                [3,5,7,9],
                [2,4,6,8,10],
                [1,3,5,7,9,11],
            ],
            2 => [
                [1,3,5,7,9,11],
                [2,4,6,8,10],
                [3,5,7,9],
                [4,6,8],
                [5,7],
            ],
            3 => [
                [5,7],
                [4,6,8],
                [3,5,7,9],
                [4,8],
                [3,5,7,9],
                [4,6,8],
                [5,7],
            ]
        ];
        $this->page->reset_subblocks( 'age' );
        foreach($ages as $age => $rows) {
            $this->page->reset_subblocks( 'block_age_building' );
            foreach($rows as $rowIndex => $columns) {
                $row = $rowIndex + 1;
                foreach($columns as $column) {
                    $id = random_int(1, 73);
                    if ($row % 2 == 0) {
                        $x = 2 + $age;
                        $y = 7;
                    }
                    else {
                        $spritesheetColumns = 10;
                        $x = (($id - 1) % $spritesheetColumns);
                        $y = floor(($id - 1) / $spritesheetColumns);
                    };

                    $this->page->insert_block("block_age_building", [
                        'id' => $id,
                        'zindex' => $row * 10,
                        'column' => $column,
                        'row' => $row,
                        'x' => $x,
                        'y' => $y,
                    ]);
                }
            }
            $this->page->insert_block("block_age", [
                'age' => $age
            ]);
        }


        $this->page->reset_subblocks( 'block_building' );
        for($id = 1; $id <= 77; $id++) {
            $spritesheetColumns = 10;
            $x = (($id - 1) % $spritesheetColumns);
            $y = floor(($id - 1) / $spritesheetColumns);

            $this->page->insert_block("block_building", [
                'id' => $id,
                'x' => $x,
                'y' => $y,
            ]);
        }

        $this->page->reset_subblocks( 'block_wonder' );
        for($id = 1; $id <= 13; $id++) {
            $spritesheetColumns = 5;
            $x = (($id - 1) % $spritesheetColumns);
            $y = floor(($id - 1) / $spritesheetColumns);

            $this->page->insert_block("block_wonder", [
                'id' => $id,
                'x' => $x,
                'y' => $y,
            ]);
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
  

