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
    <!-- BEGIN block_age -->
    <div class="whiteblock" style="display: inline-block">
        <h3>Age {age}:</h3>
        <div class="draftpool age">
            <!-- BEGIN block_age_building -->
            <div id="building{id}" class="building building_small column{column} row{row}" data-building-id="{id}" style="position: absolute; z-index: {zindex}; background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_age_building -->
        </div>
    </div>
    <!-- END block_age -->

    <div class="whiteblock">
        <h3>Wonders:</h3>
        <div>
            <!-- BEGIN block_wonder -->
            <div id="catalog_wonder_{id}" class="wonder wonder_small"  data-wonder-id="{id}" style="background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_wonder -->
        </div>
    </div>

    <div class="whiteblock">
        <h3>Age cards:</h3>
        <div>
            <!-- BEGIN block_building -->
            <div id="catalog_building_{id}" class="building building_small" data-building-id="{id}" style="background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_building -->
        </div>
    </div>

    <div class="whiteblock">
        <h3>Progress tokens:</h3>
        <div>
            <!-- BEGIN block_progress_token -->
            <div class="progress_token progress_token_small" style="background-position: -{x}00% -{y}00%;"></div>
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

var jstpl_building_tooltip = '<div class="cardtooltip">\
                            <div class="cardinfos">\
                                <h3>${name}</h3>\
                            </div>\
                            <div class="building" style="background-position: -${backx}00% -${backy}00%;">\
                            </div>\
                            <div class="clear"></div>\
                          </div>';

var jstpl_wonder_tooltip = '<div class="cardtooltip">\
                            <div class="cardinfos">\
                                <h3>${name}</h3>\
                            </div>\
                            <div class="wonder" style="background-position: -${backx}00% -${backy}00%;">\
                            </div>\
                            <div class="clear"></div>\
                          </div>';

</script>  

{OVERALL_GAME_FOOTER}
