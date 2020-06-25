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


<!-- BEGIN swd -->
<div id="playarea" style="position: absolute; width: 20px; height: 20px;
    box-shadow: rgba(0, 0, 0, 1) 0px 0px 0px 10px inset;
    background-color: red;
    z-index: 50; opacity: 0.5; display: none"></div>
<div id="swd_wrap" class="square" data-wonder-columns="1">
    <div id="player_wonders_mobile_container_{PLAYER_OPPONENT_ID}">
        <div></div>
    </div>
    <div id="layout_flexbox">
        <div id="wonder_column">
            <!-- BEGIN player_wonders -->
            <div id="player_wonders_container_{PLAYER_ID}">
                <div id="player_wonders_{PLAYER_ID}" class="whiteblock player_area_wonders player_area_wrap {WHICH_PLAYER}">
                    <div id="player_area_content_wonder_position_1_{PLAYER_ID}" class="player_area_wonder_position_1 card_outline"></div>
                    <div id="player_area_content_wonder_position_2_{PLAYER_ID}" class="player_area_wonder_position_2 card_outline"></div>
                    <div id="player_area_content_wonder_position_3_{PLAYER_ID}" class="player_area_wonder_position_3 card_outline"></div>
                    <div id="player_area_content_wonder_position_4_{PLAYER_ID}" class="player_area_wonder_position_4 card_outline"></div>
                </div>
            </div>
            <!-- END player_wonders -->
        </div>
        <div id="middle_column">
            <!-- BEGIN middle_column_block -->
                <!-- BEGIN draftpool -->
                <div id="wonder_selection_block" class="whiteblock">
                    <h3>Wonders selection:</h3>
                    <div id="wonder_selection_container">
                        <div id="wonder_selection_position_0" class="wonder_selection_position card_outline"></div><div id="wonder_selection_position_1" class="wonder_selection_position card_outline"></div><br/>
                        <div id="wonder_selection_position_2" class="wonder_selection_position card_outline"></div><div id="wonder_selection_position_3" class="wonder_selection_position card_outline"></div>
                    </div>
                </div>
                <div id="draftpool_container" style="display: inline-block">
                    <div id="draftpool" class="draftpool age">

                    </div>
                    <div class="draftpool_actions">
                        <a href="#" id="building" class="action_button bgabutton bgabutton_blue"><div class="action_construct"></div><span>Construct building</span></a>
                        <a href="#" id="building" class="action_button bgabutton bgabutton_blue"><div class="action_discard"></div><span>Discard to obtain coins</span></a>
                        <a href="#" id="building" class="action_button bgabutton bgabutton_blue"><div class="action_wonder"></div><span>Construct a wonder</span></a>
                    </div>
                </div>
                <!-- END draftpool -->
                <!-- BEGIN player_buildings -->
                <div class="player_buildings whiteblock player{PLAYER_ID} {WHICH_PLAYER}">
                    <div class="player_building_column Brown" title="Raw materials"></div>
                    <div class="player_building_column Grey" title="Manufactured goods"></div>
                    <div class="player_building_column Yellow" title="Commercial Buildings"></div>
                    <div class="player_building_column Red" title="Military Buildings"></div>
                    <div class="player_building_column Blue" title="Civilian Buildings"></div>
                    <div class="player_building_column Green" title="Scientific Buildings"></div>
                    <div class="player_building_column Purple" title="Guilds"></div>
                </div>
                <!-- END player_buildings -->
            <!-- END middle_column_block -->
        </div>
        <div id="board_column">
            <!-- BEGIN board_column_block -->
                <!-- BEGIN board -->
                <div id="board_container">
                    <div class="board"></div>
                    <!-- BEGIN block_board_progresstoken_container -->
                    <div id="board_progress_token_container{i}"></div>
                    <!-- END block_board_progresstoken_container -->
                </div>
                <!-- END board -->
                <!-- BEGIN board_player -->
                <div id="player_area_wrap_{PLAYER_ID}" class="player_area_wrap whiteblock {WHICH_PLAYER}">
                    <!-- BEGIN board_player_row -->
                        <!-- BEGIN board_player_row_info -->
                        <h3 style="color:#{PLAYER_COLOR}" class="player_area_name">{PLAYER_NAME}</h3>
                        <div class="player_area_coins">
                        <div class="coin">
                            <span id="player_area_{PLAYER_ID}_coins">24</span>
                        </div>
                        </div>
                        <!-- END board_player_row_info -->
                        <!-- BEGIN board_player_row_progress_tokens -->
                        <div class="player_area_progress_tokens">
                        </div>
                        <!-- END board_player_row_progress_tokens -->
                    <!-- END board_player_row -->
                </div>
                <!-- END board_player -->
            <!-- END board_column_block -->
        </div>

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
    <div id="player_wonders_mobile_container_{PLAYER_ME_ID}">
        <div></div>
    </div>
</div>
<!-- END swd -->

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

var jstpl_draftpool_building = '\
    <div id="${jsRow}_${jsColumn}"\
        data-building-id="${jsId}"\
        data-card-id="${jsCardId}"\
        class="building building_small column${jsColumn} row${jsRow} ${jsAvailable}"\
        style="position: absolute; z-index: ${jsZindex}; background-position: -${jsX}00% -${jsY}00%;"\
    >\
    <div class="drafpool_building_cost"><div class="coin" style="display: ${jsDisplayCost}"><span>${jsCost}</span></div></div>\
    </div>';

// Keep title attribute empty. This overrides the title attribute of the player_building_column div.
var jstpl_player_building = '\
    <div id="player_building_${jsId}"\
        data-building-id="${jsId}"\
        data-card-id="${jsCardId}"\
        title=""\
        class="building building_header_small"\
        style="position: inline-block; background-position: -${jsX}00% calc(-5px + -${jsY} * var(--building-height) * var(--building-small-scale));"\
    >\
    </div>';

var jstpl_wonder = '\
    <div id="wonder_${jsId}_container" class="wonder_container" style="display: inline-block;">\
        <div id="wonder_${jsId}"\
            data-wonder-id="${jsId}"\
            data-card-id="${jsCardId}"\
            class="wonder wonder_small"\
            style="background-position: -${jsX}00% -${jsY}00%; "\
        >\
        </div>\
        <div class="age_card_container" style="">\
        </div>\
    </div>';

var jstpl_wonder_age_card = '\
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
