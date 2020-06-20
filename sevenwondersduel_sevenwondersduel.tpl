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
    <div id="wonder_selection_container"></div>
    <div id="board_container">
        <div class="board"></div>
        <!-- BEGIN block_board_progresstoken_container -->
        <div id="board_progress_token_container{i}"></div>
        <!-- END block_board_progresstoken_container -->
        <!-- BEGIN board_player -->
        <div class="whiteblock {CLASS}">
            <h3 style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</h3>
        </div>
        <!-- END board_player -->
    </div>
    <div id="draftpool_container" class="whiteblock" style="display: inline-block">
        <h3>Age 1:</h3>
        <div id="draftpool" class="draftpool age">

        </div>
        <div class="draftpool_actions">
            <a href="#" id="building" class="action_button bgabutton bgabutton_blue"><div class="action_construct"></div><span>Construct building</span></a>
            <a href="#" id="building" class="action_button bgabutton bgabutton_blue"><div class="action_discard"></div><span>Discard to obtain coins</span></a>
            <a href="#" id="building" class="action_button bgabutton bgabutton_blue"><div class="action_wonder"></div><span>Construct a wonder</span></a>
        </div>
    </div>

    <!-- BEGIN player -->
    <div id="player_area_wrap_{PLAYER_ID}" class="player_area_wrap whiteblock">
        <h3 style="color:#{PLAYER_COLOR}" class="player_area_name">{PLAYER_NAME}</h3>
        <div id="player_area_{PLAYER_ID}_coins" class="player_area_coins coin">
            <span>24</span>
        </div>
        <div id="player_area_content_{PLAYER_ID}" class="player_area_content">
            <div class="player_area_building_column_container">
                <div class="player_area_building_column Brown" title="Raw materials"></div>
                <div class="player_area_building_column Grey" title="Manufactured goods"></div>
                <div class="player_area_building_column Yellow" title="Commercial Buildings"></div>
                <div class="player_area_building_column Red" title="Military Buildings"></div>
                <div class="player_area_building_column Blue" title="Civilian Buildings"></div>
                <div class="player_area_building_column Green" title="Scientific Buildings"></div>
                <div class="player_area_building_column Purple" title="Guilds"></div>
            </div>
            <div style="display: inline-block">
                <div class="player_area_wonders">
                    <div class="player_area_wonders_row">
                        <div class="player_area_wonder_container0"></div>
                        <div class="player_area_wonder_container1"></div>
                    </div>
                    <div class="player_area_wonders_row">
                        <div class="player_area_wonder_container2"></div>
                        <div class="player_area_wonder_container3"></div>
                    </div>
                </div>
            </div>
            <div class="player_area_progress_tokens">
            </div>
        </div>
    </div>
    <!-- END player -->

    <!-- BEGIN block_catalog -->
    <div>
        <div class="whiteblock">
            <h3>Wonders:</h3>
            <div>
                <!-- BEGIN block_catalog_wonder -->
                <div id="catalog_wonder_{id}" data-wonder-id="{id}" class="wonder wonder_small"
                     style="background-position: -{x}00% -{y}00%;"></div>
                <!-- END block_catalog_wonder -->
            </div>
        </div>

        <div class="whiteblock">
            <h3>Age cards:</h3>
            <div>
                <!-- BEGIN block_catalog_building -->
                <div id="catalog_building_{id}" data-building-id="{id}" class="building building_small"
                     style="background-position: -{x}00% -{y}00%;"></div>
                <!-- END block_catalog_building -->
            </div>
        </div>

        <div class="whiteblock">
            <h3>Progress tokens:</h3>
            <div>
                <!-- BEGIN block_catalog_progress_token -->
                <div id="catalog_progress_token_{id}" data-progress-token-id="{id}"
                     class="progress_token progress_token_small" style="background-position: -{x}00% -{y}00%;"></div>
                <!-- END block_catalog_progress_token -->
            </div>
        </div>
    </div>
    <!-- END block_catalog -->
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
    <div class="drafpool_building_cost"><div class="coin" style="zoom: 0.75; display: ${jsDisplayCost}"><span>${jsCost}</span></div></div>\
    </div>';

// Keep title attribute empty. This overrides the title attribute of the player_area_building_column div.
var jstpl_player_building = '\
    <div id="player_building_${jsId}"\
        ${jsData}\
        title=""\
        class="building building_header_small"\
        style="position: inline-block; background-position: -${jsX}00% calc(-5px + -${jsY} * 321px / 2);"\
    >\
    </div>';

var jstpl_wonder_selection = '\
    <div id="wonder_selection_${jsId}"\
            ${jsData}\
            class="wonder wonder_small"\
            style="background-position: -${jsX}00% -${jsY}00%; "\
        >\
        </div>\
    </div>';

var jstpl_player_wonder = '\
    <div id="player_wonder_${jsId}_container" style="display: inline-block;">\
        <div id="player_wonder_${jsId}"\
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

var jstpl_player_progress_token = '\
    <div id="player_progress_token_${jsId}"\
        ${jsData}\
        class="progress_token progress_token_small"\
        style="position: inline-block; background-position: -${jsX}00% -${jsY}00%;"\
    >\
    </div>';


var jstpl_board_progress_token = '\
    <div id="board_progress_token_${jsId}"\
        ${jsData}\
        class="progress_token progress_token_small"\
        style="position: inline-block; background-position: -${jsX}00% -${jsY}00%;"\
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
