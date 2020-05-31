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


<div>
    <!-- BEGIN block_age -->
    <div class="whiteblock">
        <h3>Age {age}:</h3>
        <div class="draftpool" style="height: 550px;">
            <!-- BEGIN block_building -->
            <div class="building building_small column{column} row{row}" style="z-index: {zindex}; background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_building -->
        </div>
    </div>
    <!-- END block_age -->

    <div class="whiteblock">
        <h3>Wonders:</h3>
        <div>
            <!-- BEGIN block_wonder -->
            <div class="wonder" style="background-position: -{x}00% -{y}00%;"></div>
            <!-- END block_wonder -->
        </div>
    </div>
</div>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
