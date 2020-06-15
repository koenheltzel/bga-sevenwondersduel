{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    sevenwondersduel_sevenwondersduel.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->


<div id="swd_wrap">
    <div class="whiteblock" style="display: inline-block">
        <h3>Age 1:</h3>
        <div id="draftpool" class="draftpool age">

        </div>
    </div>

    <!-- BEGIN player -->
    <div id="player_board_wrap_{PLAYER_ID}" class="player_board_wrap whiteblock">
        <h3 style="color:#{PLAYER_COLOR}">
            <div id="player_board_name_{PLAYER_ID}" class="player_board_name">{PLAYER_NAME}</div>
        </h3>
        <div class="sw_coins">
            <div id="ttcoin{PLAYER_ID}" class="sicon sicon_coin imgtext tcoin"></div> <span id="coin_{PLAYER_ID}" class="tcoin">0</span>
        </div>
        <div id="player_board_content_{PLAYER_ID}" class="player_board_content">
            <div class="player_board_building_column Brown"></div>
            <div class="player_board_building_column Grey"></div>
            <div class="player_board_building_column Yellow"></div>
            <div class="player_board_building_column Blue"></div>
            <div class="player_board_building_column Red"></div>
            <div class="player_board_building_column Green"></div>
            <div class="player_board_building_column Purple"></div>
            <div class="player_board_wonders"></div>
        </div>
    </div>
    <!-- END player -->

    <div class="whiteblock">
        <h3>Wonders:</h3>
        <div>
            <!-- BEGIN block_wonder -->
            <div id="catalog_wonder_{id}" data-wonder-id="{id}" class="wonder wonder_small" style="background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_wonder -->
        </div>
    </div>

    <div class="whiteblock">
        <h3>Age cards:</h3>
        <div>
            <!-- BEGIN block_building -->
            <div id="catalog_building_{id}" data-building-id="{id}" class="building building_small" style="background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_building -->
        </div>
    </div>

    <div class="whiteblock">
        <h3>Progress tokens:</h3>
        <div>
            <!-- BEGIN block_progress_token -->
            <div id="catalog_progress_token_{id}" data-progress-token-id="{id}" class="progress_token progress_token_small" style="background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_progress_token -->
        </div>
    </div>
</div>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

var jstpl_draftpool_building = '\
    <div id="${jsRow}_${jsColumn}"\
        ${jsData}\
        class="building building_small column${jsColumn} row${jsRow}"\
        style="position: absolute; z-index: ${jsZindex}; background-position: -${jsX}00% -${jsY}00%;"\
    >\
    </div>';

var jstpl_player_building = '\
    <div id="player_building_${jsId}"\
        ${jsData}\
        class="building building_header_small"\
        style="position: inline-block; background-position: -${jsX}00% calc(-5px + -${jsY} * 321px / 2);"\
    >\
    </div>';

var jstpl_player_wonder = '\
    <div id="player_wonder_${jsId}" style="display: inline-block;">\
        <div\
            ${jsData}\
            class="wonder wonder_small"\
            style="background-position: -${jsX}00% -${jsY}00%; "\
        >\
        </div>\
        <div class="age_card_container" style="">\
        </div>\
    </div>';

var jstpl_player_wonder_age_card = '\
    <div ${jsData}\
        class="building building_small"\
        style="background-position: -${jsX}00% -${jsY}00%;"\
    >\
    </div>';


var jstpl_building_tooltip = '\
    <div class="cardtooltip">\
        <div class="cardinfos">\
            <h3>${name}</h3>\
        </div>\
        <div class="building" style="background-position: -${backx}00% -${backy}00%;">\
        </div>\
        <div class="clear"></div>\
    </div>';

var jstpl_wonder_tooltip = '\
    <div class="cardtooltip">\
        <div class="cardinfos">\
            <h3>${name}</h3>\
        </div>\
        <div class="wonder" style="background-position: -${backx}00% -${backy}00%;">\
        </div>\
        <div class="clear"></div>\
    </div>';

var jstpl_progress_token_tooltip = '\
    <div class="cardtooltip">\
        <div class="cardinfos">\
            <h3>${name}</h3>\
        </div>\
        <div class="progress_token" style="background-position: -${backx}00% -${backy}00%;">\
        </div>\
        <div class="clear"></div>\
    </div>';

</script>  

{OVERALL_GAME_FOOTER}
