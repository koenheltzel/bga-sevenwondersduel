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
<div id="debugPlayArea" style="position: absolute; width: 20px; height: 20px;
    box-shadow: rgba(0, 0, 0, 1) 0px 0px 0px 10px inset;
    background-color: red;
    z-index: 50; opacity: 0.5; display: none"></div>
<div id="swd" data-state="" data-agora="{AGORA}">
    <div id="swd_wrap" class="square" data-wonder-columns="1">
        <div id="player_wonders_mobile_container_{PLAYER_OPPONENT_ID}">
            <div></div>
        </div>
        <div id="layout_flexbox">
            <div id="wonder_column">
                <!-- BEGIN player_wonders -->
                <div id="player_wonders_container_{PLAYER_ID}">
                    <div id="player_wonders_{PLAYER_ID}" class="whiteblock player_wonders player{PLAYER_ID} {PLAYER_ALIAS}">
                        <div class="card_outline"></div>
                        <div class="card_outline"></div>
                        <div class="card_outline"></div>
                        <div class="card_outline"></div>
                    </div>
                </div>
                <!-- END player_wonders -->
            </div>
            <div id="middle_column">
                <!-- BEGIN middle_column_block -->
                    <!-- BEGIN draftpool -->
                    <div id="wonder_selection_block" class="whiteblock">
                        <h3 id="wonder_selection_block_title"></h3>
                        <div id="wonder_selection_container">
                            <div class="card_outline"></div><div class="card_outline"></div><br/>
                            <div class="card_outline"></div><div class="card_outline"></div>
                        </div>
                    </div>
                    <div id="draftpool_container">
                        <div id="draftpool" class="draftpool age">
                        </div>
                        <div id="draftpool_actions" class="whiteblock">
                            <a href="#" id="buttonConstructWonder" class="action_button bgabutton bgabutton_blue"><div class="action_wonder"></div><span>{CONSTRUCT_WONDER}</span></a>
                            <a href="#" id="buttonConstructBuilding" class="action_button bgabutton bgabutton_blue"><div class="action_construct"></div><span>{CONSTRUCT_BUILDING}</span></a>
                            <a href="#" id="buttonDiscardBuilding" class="action_button bgabutton bgabutton_blue"><div class="coin"><span>+X</span></div><span>{DISCARD_BUILDING}</span></a>
                        </div>
                        <div id="select_start_player" class="whiteblock">
                            <a href="#" id="buttonPlayerLeft" class="action_button bgabutton bgabutton_blue"><img class="emblem" /><span>Name 1</span></a>
                            <div id="select_start_player_text"></div>
                            <a href="#" id="buttonPlayerRight" class="action_button bgabutton bgabutton_blue"><img class="emblem" /><span>Name 2</span></a>
                        </div>
                        <div id="progress_token_from_box" class="whiteblock">
                            <h3>{CHOOSE_PROGRESS_TOKEN_FROM_BOX}:</h3>
                            <div id="progress_token_from_box_container">
                                <div class="progress_token_outline"></div><div class="progress_token_outline"></div><div class="progress_token_outline"></div>
                            </div>
                        </div>
                    </div>
                    <!-- END draftpool -->
                    <!-- BEGIN end_game -->
                    <div id="end_game_container" class="whiteblock" style="display: none;">
                        <table>
                            <tr>
                                <td></td>
                                <!-- BEGIN end_game_category_header -->
                                <td class="end_game_{CATEGORY}"><div class="end_game_icon end_game_card" /></td>
                                <!-- END end_game_category_header -->
                                <td></td>
                            </tr>
                            <!-- BEGIN end_game_player -->
                            <tr>
                                <td class="end_game_player_name" style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</td>
                                <!-- BEGIN end_game_category_player -->
                                <td class="end_game_{CATEGORY} player{PLAYER_ID}">xx</td>
                                <!-- END end_game_category_player -->
                                <td style="overflow: initial">
                                    <div class="end_game_total player{PLAYER_ID}">

                                    </div>
                                </td>
                            </tr>
                            <!-- END end_game_player -->
                        </table>
                    </div>
                    <!-- END end_game -->
                    <!-- BEGIN player_buildings -->
                    <div class="player_buildings whiteblock player{PLAYER_ID} {PLAYER_ALIAS}">
                        <div class="player_building_column Brown"></div>
                        <div class="player_building_column Grey"></div>
                        <div class="player_building_column Yellow"></div>
                        <div class="player_building_column Red"></div>
                        <div class="player_building_column Blue"></div>
                        <div class="player_building_column Green">
                            <div class="building_header_small_container card_outline science_progress"><span></span></div>
                        </div>
                        <div class="player_building_column Purple"></div>
                        <div class="player_building_column Agora"></div>
                    </div>
                    <!-- END player_buildings -->
                <!-- END middle_column_block -->
            </div>
            <div id="board_column">
                <!-- BEGIN board_column_block -->
                    <!-- BEGIN board -->
                    <div id="board_container">
                        <div class="board"></div>
                        <div id="conflict_pawn" class="pawn"></div>
                        <div id="board_progress_tokens">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                        <div id="military_tokens">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <!-- END board -->
                    <!-- BEGIN board_player -->
                    <div class="whiteblock player_info {PLAYER_ALIAS}">
                        <!-- BEGIN board_player_row -->
                            <!-- BEGIN board_player_row_info -->

                            <div class="player_info_sub">
                                <div class="player_area_name">
                                    <h3 style="color:#{PLAYER_COLOR}" class="">{PLAYER_NAME}</h3>
                                </div>
                                <div id="player_area_{PLAYER_ID}_coins_container" class="player_area_coins">
                                    <div class="coin">
                                        <span id="player_area_{PLAYER_ID}_coins"></span>
                                    </div>
                                </div>
                                <div class="player_area_points">
                                    <div class="point">
                                        <span id="player_area_{PLAYER_ID}_score"></span>
                                    </div>
                                </div>
                                <div class="player_area_cubes">
                                    <div class="agora_cube3d_{PLAYER_ALIAS}"></div>
                                </div>
                            </div>
                            <!-- END board_player_row_info -->
                            <!-- BEGIN board_player_row_progress_tokens -->
                            <div class="player_area_progress_tokens">
                                <div class="progress_token_outline"></div>
                                <div class="progress_token_outline"></div>
                                <div class="progress_token_outline"></div>
                                <div class="progress_token_outline"></div>
                                <div class="progress_token_outline"></div>
                                <div class="progress_token_outline"></div>
                            </div>
                            <!-- END board_player_row_progress_tokens -->
                        <!-- END board_player_row -->
                    </div>
                    <!-- END board_player -->
                <!-- END board_column_block -->
            </div>
        </div>
        <div id="player_wonders_mobile_container_{PLAYER_ME_ID}">
            <div></div>
        </div>
    </div>
    <div id="discarded_cards_whiteblock" class="whiteblock">
        <h3>{DISCARDED_BUILDINGS}</h3>
        <div id="discarded_cards_container">
            <div class="discarded_cards_cursor"></div>
        </div>
    </div>

    <div id="list_of_cards_flexbox">
        <div class="list_of_cards_whiteblock whiteblock">
            <h3>{BUILDINGS_WITH_LINKS}</h3>
            <div class="list_of_cards_header">
                <div><h3>{AGE} I</h3></div>
                <div><h3>{AGE} II</h3></div>
                <div><h3>{AGE} III</h3></div>
            </div>
            <div class="list_of_cards list_of_cards_linked">
                <!-- BEGIN list_of_cards_page1 -->
                <div class="list_card_page1" data-building-id="{ID}" style="left: calc(var(--list-of-cards-scale) * {X}px); top: calc(var(--list-of-cards-scale) * {Y}px); color: {COLOR};">
                    {TITLE}
                </div>
                <!-- END list_of_cards_page1 -->
            </div>
        </div>
        <div class="list_of_cards_whiteblock whiteblock">
            <h3>{BUILDINGS_WITHOUT_LINKS}</h3>
            <div class="list_of_cards_header">
                <div><h3>{AGE} I</h3></div>
                <div><h3>{AGE} II</h3></div>
                <div><h3>{AGE} III</h3></div>
                <div><h3>{GUILDS}</h3></div>
            </div>
            <div class="list_of_cards list_of_cards_unlinked">
                <!-- BEGIN list_of_cards_page2 -->
                <div class="{CLASS}" data-building-id="{ID}" style="left: calc(var(--list-of-cards-scale) * {X}px); top: calc(var(--list-of-cards-scale) * {Y}px); color: {COLOR};">
                    {TITLE}
                </div>
                <!-- END list_of_cards_page2 -->
            </div>
        </div>
    </div>

    <div id="settings_whiteblock" class="whiteblock">
        <h3>{SETTINGS}</h3>
        <table class="settings_table">
            <tr>
                <td>{AUTOMATIC_LAYOUT}</td>
                <td><input type="checkbox" id="setting_auto_layout" checked="checked"> {AUTOMATIC_LAYOUT_DESCRIPTION}</td>
            </tr>
            <tr>
                <td>{LAYOUT}</td>
                <td>
                    <select id="setting_layout" disabled>
                        <option value="portrait"></option>
                        <option value="square"></option>
                        <option value="landscape"></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>{AUTOMATIC_SCALING}</td>
                <td><input type="checkbox" id="setting_auto_scale" checked="checked"> {AUTOMATIC_SCALING_DESCRIPTION}</td>
            </tr>
            <tr>
                <td>{SCALE}</td>
                <td>
                    <input type="number" id="setting_scale" style="width: 50px" min="50" max="200" disabled>% <span id="setting_scale_description">{SETTING_SCALE_DESCRIPTION}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- BEGIN block_catalog -->
    <div>
        <div class="whiteblock">
            <h3>Wonders:</h3>
            <div>
                <!-- BEGIN block_catalog_wonder -->
                <div id="catalog_wonder_{id}" data-wonder-id="{id}" class="wonder wonder_small"
                     style="background-position: -{x}00% -{y}00%;"><span class="swd_title">{title}</span></div>
                <!-- END block_catalog_wonder -->
            </div>
        </div>

        <div class="whiteblock">
            <h3>Age cards:</h3>
            <div>
                <!-- BEGIN block_catalog_building -->
                <div id="catalog_building_{id}" data-building-id="{id}" class="building building_small"
                     style="background-position: -{x}00% -{y}00%;"><span class="swd_title">{title}</span></div>
                <!-- END block_catalog_building -->
            </div>
        </div>

        <div class="whiteblock">
            <h3>Progress tokens:</h3>
            <div>
                <!-- BEGIN block_catalog_progress_token -->
                <div id="catalog_progress_token_{id}" data-progress-token-id="{id}"
                     class="progress_token progress_token_small" style="background-position: -{x}00% -{y}00%;">
                    <svg viewBox="0 0 200 200">
                        <path id="curve" fill="transparent" d="M14,97.526C13.19,144.073,51.722,184,99.5,184c47.28,0,85.77-39.112,85.5-85.491C184.732,52.519,146.437,14,99.5,14,52.808,14,14.791,52.1,14,97.526Z" />
                        <text width="200">
                            <textPath xlink:href="#curve" class="swd_title" text-anchor="middle" startOffset="25%">
                                {title}
                            </textPath>
                        </text>
                    </svg>
                </div>
                <!-- END block_catalog_progress_token -->
            </div>
        </div>
    </div>
    <!-- END block_catalog -->
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
        data-building-type="${jsType}"\
        class="building building_small column${jsColumn} row${jsRow} ${jsAvailable}"\
        style="position: absolute; z-index: ${jsZindex}; background-position: -${jsX}00% -${jsY}00%;"\
    >\
        <div class="draftpool_building_cost opponent" style="display: ${jsDisplayCostOpponent}"><div class="coin"><span style="color: ${jsCostColorOpponent} !important">${jsCostOpponent}</span></div>\
            <div\
                class="linked_building_icon linked_building_icon_small"\
                style="background-position: -${jsLinkX}00% -${jsLinkY}00%;"\
            ></div>\
        </div>\
        <span class="swd_title">${jsName}</span>\
        <div class="draftpool_building_cost me" style="display: ${jsDisplayCostMe}"><div class="coin"><span style="color: ${jsCostColorMe} !important">${jsCostMe}</span></div>\
            <div\
                class="linked_building_icon linked_building_icon_small"\
                style="background-position: -${jsLinkX}00% -${jsLinkY}00%;"\
            ></div>\
        </div>\
    </div>';

// Keep title attribute empty. This overrides the title attribute of the player_building_column div.
var jstpl_player_building = '\
    <div id="player_building_container_${jsId}" class="building_header_small_container" style="order: ${jsOrder}">\
        <div id="player_building_${jsId}"\
            data-building-id="${jsId}"\
            title=""\
            class="building building_header_small"\
            style="background-position: -${jsX}00% calc((-10px + -${jsY} * var(--building-height)) * var(--building-small-scale));"\
        >\
        </div>\
    </div>';

var jstpl_wonder = '\
    <div id="wonder_${jsId}_container" class="wonder_container">\
        <div id="wonder_${jsId}"\
            data-wonder-id="${jsId}"\
            class="wonder wonder_small"\
            style="background-position: -${jsX}00% -${jsY}00%; "\
        >\
            <div class="player_wonder_cost" style="display: ${jsDisplayCost}"><div class="coin"><span style="color: ${jsCostColor} !important">${jsCost}</span></div></div>\
            <span class="swd_title">${jsName}</span>\
        </div>\
        <div class="age_card_container"></div>\
        <div class="card_outline"></div>\
    </div>';

var jstpl_wonder_age_card = '\
    <div\
        class="building building_small"\
        style="background-position: -${jsX}00% -${jsY}00%;"\
    >\
    </div>';

var jstpl_progress_token = '\
    <div id="progress_token_${jsId}"\
        ${jsData}\
        class="progress_token progress_token_small"\
        style="background-position: -${jsX}00% -${jsY}00%;"\
    >\
        <svg viewBox="0 0 200 200">\
            <path id="curve" fill="transparent" d="M14,97.526C13.19,144.073,51.722,184,99.5,184c47.28,0,85.77-39.112,85.5-85.491C184.732,52.519,146.437,14,99.5,14,52.808,14,14.791,52.1,14,97.526Z" />\
            <text width="200">\
                <textPath xlink:href="#curve" class="swd_title" text-anchor="middle" startOffset="25%">\
                    ${jsName}\
                </textPath>\
            </text>\
        </svg>\
    </div>';

var jstpl_military_token = '<div class="military_token military_token_${jsValue}"></div>';

var jstpl_coin_animated = '<div class="coin animated"></div>';

var jstpl_tooltip_cost_me = '\
    <hr\>\
    <strong class="me">${translateCurrentCost}:</strong>\
    <div class="payment_plan">\
        ${jsPayment}\
    </div>\
    <strong>${translateTotal}:</strong> ${jsCoinHtml}\
    ';

var jstpl_tooltip_cost_opponent = '\
    <hr\>\
    <strong class="opponent">${translateCurrentCost}:</strong>\
    <div class="payment_plan">\
        ${jsPayment}\
    </div>\
    <strong>${translateTotal}:</strong> ${jsCoinHtml}\
    ';

var jstpl_building_tooltip = '\
    <div class="swd_tooltip">\
        <div class="cardinfos">\
            <strong>${cardType} “${jsName}”</strong><strong style="float:right; color: ${jsBuildingTypeColor}">${jsBuildingTypeDescription}</strong>\
            <hr\>\
            <p>${jsText}</p>\
            ${jsCostOpponent}\
            ${jsCostMe}\
        </div>\
        <div>\
            <div data-building-type="${jsType}" class="building tooltipWiggle" style="float:right; background-position: -${jsX}00% -${jsY}00%;">\
                <span class="swd_title">${jsName}</span>\
            </div>\
        </div>\
        <div class="clear"></div>\
    </div>';

var jstpl_wonder_tooltip = '\
    <div class="swd_tooltip">\
        <div class="cardinfos">\
            <strong>${translateWonder} “${jsName}”</strong>\
            <hr\>\
            <p>${jsText}</p>\
            ${jsCost}\
        </div>\
        <div>\
            <div class="wonder tooltipWiggle" style="float:right; background-position: -${jsBackX}00% -${jsBackY}00%;">\
                <span class="swd_title">${jsName}</span>\
            </div>\
        </div>\
        <div class="clear"></div>\
    </div>';

var jstpl_progress_token_tooltip = '\
    <div class="swd_tooltip progress_token_tooltip">\
        <div class="cardinfos">\
            <strong>${translateProgressToken} “${jsName}”</strong>\
            <hr\>\
            <p>${jsText}</p>\
        </div>\
        <div>\
            <div class="progress_token tooltipWiggle" style="float:right; background-position: -${jsBackX}00% -${jsBackY}00%;">\
                <svg viewBox="0 0 200 200">\
                    <path id="curve" fill="transparent" d="M14,97.526C13.19,144.073,51.722,184,99.5,184c47.28,0,85.77-39.112,85.5-85.491C184.732,52.519,146.437,14,99.5,14,52.808,14,14.791,52.1,14,97.526Z" />\
                    <text width="200">\
                        <textPath xlink:href="#curve" class="swd_title" text-anchor="middle" startOffset="25%">\
                            ${jsName}\
                        </textPath>\
                    </text>\
                </svg>\
            </div>\
        </div>\
        <div class="clear"></div>\
    </div>';

</script>

{OVERALL_GAME_FOOTER}
