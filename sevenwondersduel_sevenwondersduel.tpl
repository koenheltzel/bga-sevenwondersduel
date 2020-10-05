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
<div id="swd" data-state="" data-client-state="" data-age="" data-quality="" data-show-opponent-cost="" data-agora="{AGORA}">
    <div id="swd_wrap" class="square" data-wonder-columns="1">
        <div id="player_wonders_mobile_container_{PLAYER_OPPONENT_ID}" class="player_wonders_mobile opponent whiteblock"></div>
        <div id="layout_flexbox">
            <div id="wonder_column">
                <!-- BEGIN player_wonders -->
                <div id="player_wonders_container_{PLAYER_ID}" class="player_wonders_container {PLAYER_ALIAS} whiteblock">
                    <div id="player_wonders_{PLAYER_ID}" class="player_wonders player{PLAYER_ID} {PLAYER_ALIAS}">
                        <div class="card_outline"></div>
                        <div class="card_outline"></div>
                        <div class="card_outline"></div>
                        <div class="card_outline"></div>
                    </div>
                    <div id="player_conspiracies_{PLAYER_ID}" class="player_conspiracies player{PLAYER_ID} {PLAYER_ALIAS}">
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
                            <a href="#" id="buttonPrepareConspiracy" class="action_button bgabutton bgabutton_blue agora"><div class="action_conspiracy"></div><span>{PREPARE_CONSPIRACY}</span></a>
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
                    <div id="choose_conspirator_action" class="whiteblock">
                        <a href="#" id="buttonPlaceInfluence" class="action_button bgabutton bgabutton_blue"><div class="action_place_influence"></div><span>{PLACE_INFLUENCE}</span></a>
                        <a href="#" id="buttonConspire" class="action_button bgabutton bgabutton_blue"><div class="action_conspiracy"></div><span>{CONSPIRE}</span></a>
                    </div>
                    <div id="conspire" class="whiteblock">
                        <div></div>
                        <div></div>
                        <div id="choose_conspire_remnant_position">
                            <h3>{CHOOSE_CONSPIRE_REMNANT_POSITION}</h3>
                            <a href="#" id="buttonConspiracyRemnantTop" class="action_button bgabutton bgabutton_blue"><span>{TOP}</span></a>
                            <a href="#" id="buttonConspiracyRemnantBottom" class="action_button bgabutton bgabutton_blue"><span>{BOTTOM}</span></a>
                        </div>
                    </div>
                    <div id="senate_actions" class="whiteblock">
                        <a href="#" id="buttonSenateActionsPlaceInfluence" class="action_button bgabutton bgabutton_blue"><div class="action_place_influence"></div><span>{PLACE_INFLUENCE}</span></a>
                        <a href="#" id="buttonSenateActionsMoveInfluence" class="action_button bgabutton bgabutton_blue"><div class="action_move_influence"></div><span>{MOVE_INFLUENCE}</span></a>
                        <a href="#" id="buttonSenateActionsSkip" class="action_button bgabutton bgabutton_blue"><span>{SKIP_MOVE_INFLUENCE}</span></a>
                    </div>
                    <div id="move_influence" class="whiteblock">
                        <a href="#" id="buttonMoveInfluenceSkip" class="action_button bgabutton bgabutton_blue"><span>{SKIP_MOVE_INFLUENCE}</span></a>
                    </div>
                    <div id="trigger_unprepared_conspiracy" class="whiteblock">
                        <a href="#" id="buttonTriggerUnpreparedConspiracySkip" class="action_button bgabutton bgabutton_blue"><span>{SKIP_TRIGGER_UNPREPARED_CONSPIRACY}</span></a>
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
                        <div class="player_building_column Senator agora">
                            <div class="building_header_small_container expansion_icon_container expansion_icon_container_agora">
                                <div class="agora_icon"></div>
                            </div>
                        </div>
                    </div>
                    <!-- END player_buildings -->
                <!-- END middle_column_block -->
            </div>
            <div id="board_column">
                <!-- BEGIN board_column_block -->
                    <!-- BEGIN board -->
                    <div id="board_middle_container">
                        <div id="senate_container" class="agora">
                            <div class="senate"></div>
                            <div class="influence_containers">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                            <div class="decree_containers">
                                <div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                            <svg id="senate_chambers" viewBox="0 0 220 949">
                                <path id="chamber1" data-chamber="1" d="m205.49691,65.41452l-0.50356,8.83306c24.00311,12.83298 1.42032,18.99925 -3.3332,22.74921l0.08332,114.24636c23.58642,16.58292 2.92027,18.33258 -4.08322,23.33253l0.58976,20.66605l-99.25322,-87.24718c0.00356,0 29.33594,-39.66538 29.33238,-39.66538c0.00356,0 9.66991,9.99968 9.66635,9.99968l6.58313,-7.16645l22.99925,0.16667l0.41665,-22.99925l7.16645,-6.66645l-10.16635,-10.083c14.75342,-12.99987 36.0023,-26.41585 40.50226,-26.16585z" />
                                <path id="chamber2" data-chamber="2" d="m95.00158,174.31504c0,0 97.98972,85.65767 96.97827,86.32427l9.66565,12.99863c0.01156,0 -1.65493,38.66262 0,35.99622c-16.32006,21.6644 -16.32006,42.99549 -16.33162,42.99549c0.01156,0 -2.32153,5.99937 -2.33309,5.99937c0.01156,0 -25.98571,-7.66586 -25.99727,-7.66586c0.01156,0 -80.31334,-25.99727 -80.3249,-25.99727c0.01156,0 -4.65462,0.6666 -4.66618,0.66659l-41.99559,-14.99842c8.34401,-25.99727 16.34318,-45.32857 16.33162,-45.32858c0.01156,0.00001 11.0104,5.99938 10.99885,5.99937c0.01155,0.00001 5.01103,-8.99905 4.99947,-8.99905c0.01156,0 20.34276,-6.99926 20.3312,-6.99927c0.01156,0.00001 -5.98781,-21.99769 -5.99937,-21.99769c0.01156,0 4.01114,-8.33245 3.99958,-8.33246c0.01156,0.00001 -12.32048,-6.33266 -12.33204,-6.33267c0.01156,0.00001 14.01009,-26.33056 26.67542,-44.32867z" />
                                <path id="chamber3" data-chamber="3" d="M25.3,321.3c0,0,66.7,25,66.7,24.7c0,0.3,3-0.7,3-1c0,0.3,34.3,13.3,34.3,13c0,0.3,3.3-0.3,3.3-0.7l44.3,17.3c-22.3,36.7-18.7,77.7-18.7,98.3L1,472.3c0,0.3,3-51.7,3-52c0,0.3,15,2.3,15,2c0,0.3,1-8.7,1-9c0,0.3,17.7-13,17.7-13.3c0,0.3-12.3-18-12.3-18.3l2-9.3l-15-2.7C17.7,346.6,25.3,321.3,25.3,321.3L25.3,321.3z" />
                                <path id="chamber4" data-chamber="4" d="M59.5,614.7L92,603.5c0-0.3,3,0.7,3,1c0-0.3,34.3-13.1,34.3-12.8c0-0.3,3.3,0.3,3.3,0.7l44.3-17.1c-22.3-36.1-18.7-76.5-18.7-96.8L1,479.1c0-0.3,0.7,28,4.9,53.3l13.3-1.5l0.9,9.1l17.7,14.2L26,571.5c0.7,3.1,0.5,8.5,1.2,11.5l-13.3,1.3c2.4,14.4,6.3,28.8,12.1,43.2L59.5,614.7z" />
                                <path id="chamber5" data-chamber="5" d="M92.2,777.2c-12.7-18-24.8-41.8-24.9-41.8c0,0,12.3-6.3,12.3-6.3c0,0-4-8.3-4-8.3c0,0,6-22,6-22c0,0-20.3-7-20.3-7c0,0-5-9-5-9c0,0-11,6-11,6c0,0-10.4-25.5-17.2-48.5L71,624.4c0,0,4.7,0.7,4.7,0.7c0,0,80.3-26,80.3-26c0,0,26-7.7,26-7.7c0,0,2.3,6,2.3,6c0,0,0,21.3,16.3,43c-1.7-2.7,0,36,0,36l-9.7,13c1,0.7-97.7,87.2-97.7,87.2L92.2,777.2z" />
                                <path id="chamber6" data-chamber="6" d="M204.8,889c-4.5,0.3-27.1-13.5-41.8-26.5l10.2-10.1l-7.2-6.7l-0.4-23l-23,0.2l-6.6-7.2c0,0-9.7,10-9.7,10c0,0-30.2-41.7-29.4-43L197.4,693l-0.8,23.4c15.5,6.9,21.6,11.1,4.1,22.3L200.7,853c4.8,3.7,27.3,9.9,3.3,22.7l0.5,8.8L204.8,889z" />
                            </svg>
                        </div>
                        <div id="board_container">
                            <div class="board"></div>
                            <div id="capital_opponent" class="capital"></div>
                            <div id="capital_me" class="capital"></div>
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
                    </div>
                    <!-- END board -->
                    <!-- BEGIN board_player -->
                    <div class="whiteblock player_info {PLAYER_ALIAS} player{PLAYER_ID}">
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
                                <div class="player_area_cubes agora">
                                    <div class="agora_cube3d_{PLAYER_ALIAS}">
                                        <span id="player_area_{PLAYER_ID}_cubes"></span>
                                    </div>
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
        <div id="player_wonders_mobile_container_{PLAYER_ME_ID}" class="player_wonders_mobile me whiteblock"></div>
    </div>
    <div id="lower_divs_container">
        <div id="discarded_cards_whiteblock" class="whiteblock">
            <h3>{DISCARDED_BUILDINGS}</h3>
            <div id="discarded_cards_container">
                <div class="discarded_cards_cursor"></div>
            </div>
        </div>
        <div id="conspiracy_deck_container" class="whiteblock agora">
            <h3>Conspiracy deck</h3>
            <div class="conspiracy conspiracy_small" style="background-position: -400% -200%;">
                <span id="conspiracy_deck_count"></span>
            </div>
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
                <td></td>
                <td>{MANUAL}</td>
                <td>{AUTOMATIC}</td>
            </tr>
            <tr>
                <td><strong>{LAYOUT}</strong></td>
                <td>
                    <select id="setting_layout" disabled>
                        <option value="portrait"></option>
                        <option value="square"></option>
                        <option value="landscape"></option>
                    </select>
                </td>
                <td><input type="checkbox" id="setting_auto_layout" checked="checked"> {AUTOMATIC_LAYOUT_DESCRIPTION}</td>
            </tr>
            <tr>
                <td><strong>{SCALE}</strong></td>
                <td>
                    <input type="number" id="setting_scale" style="width: 50px" min="50" max="200" disabled>% <span id="setting_scale_description">{SETTING_SCALE_DESCRIPTION}</span>
                </td>
                <td><input type="checkbox" id="setting_auto_scale" checked="checked"> {AUTOMATIC_SCALING_DESCRIPTION}</td>
            </tr>
            <tr>
                <td><strong>{QUALITY}</strong></td>
                <td>
                    <select id="setting_quality" disabled>
                        <option value="1x"></option>
                        <option value="2x"></option>
                    </select>
                </td>
                <td><input type="checkbox" id="setting_auto_quality" checked="checked"> {AUTOMATIC_QUALITY_DESCRIPTION}</td>
            </tr>
            <tr>
                <td><strong>{OPPONENT_COST}</strong></td>
                <td>
                    <select id="setting_opponent_cost">
                        <option value="1"></option>
                        <option value="0"></option>
                    </select>
                </td>
                <td>{OPPONENT_COST_DESCRIPTION}</td>
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
            <h3>Conspiracies:</h3>
            <div>
                <!-- BEGIN block_catalog_conspiracy -->
                <div id="catalog_conspiracy_{id}" data-conspiracy-id="{id}" class="conspiracy conspiracy_small"
                     style="background-position: -{x}00% -{y}00%;"><span class="swd_title">{title}</span></div>
                <!-- END block_catalog_conspiracy -->
            </div>
            <h3>Conspiracies compact:</h3>
            <div>
                <!-- BEGIN block_catalog_conspiracy_compact -->
                <div id="catalog_conspiracy_{id}" data-conspiracy-id="{id}" class="conspiracy conspiracy_small conspiracy_compact"
                     style="background-position: -{x}00% calc(-{y}.62 * var(--conspiracy-height) * var(--conspiracy-small-scale));"><span class="swd_title">{title}</span></div>
                <!-- END block_catalog_conspiracy_compact -->
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
        <div class="draftpool_building_cost opponent" style="display: ${jsDisplayCostOpponent}"><div class="coin ${jsCostOpponentClass}"><span style="color: ${jsCostColorOpponent} !important">${jsCostOpponent}</span></div>\
            <div\
                class="linked_building_icon linked_building_icon_small"\
                style="background-position: -${jsLinkX}00% -${jsLinkY}00%;"\
            ></div>\
        </div>\
        <span class="swd_title">${jsName}</span>\
        <div class="draftpool_building_cost me" style="display: ${jsDisplayCostMe}"><div class="coin ${jsCostMeClass}"><span style="color: ${jsCostColorMe} !important">${jsCostMe}</span></div>\
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
            <div class="player_wonder_cost" style="display: ${jsDisplayCost}"><div class="coin ${jsCostClass}"><span style="color: ${jsCostColor} !important">${jsCost}</span></div></div>\
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

var jstpl_conspiracy = '\
    <div class="conspiracy_container">\
        <div\
            data-conspiracy-id="${jsId}"\
            data-conspiracy-position="${jsPosition}"\
            data-conspiracy-prepared="${jsPrepared}"\
            data-conspiracy-triggered="${jsTriggered}"\
            class="conspiracy conspiracy_small conspiracy_compact"\
            style="background-position: -${jsX}00% calc(-${jsY}.62 * var(--conspiracy-height) * var(--conspiracy-small-scale));"\
        >\
            <span class="swd_title">${jsName}</span>\
        </div>\
        <div class="age_card_container"></div>\
        <div class="card_outline"></div>\
    </div>';

var jstpl_conspiracy_full = '\
    <div id="conspiracy_${jsId}"\
        data-conspiracy-id="${jsId}"\
        class="conspiracy conspiracy_small"\
        style="background-position: -${jsX}00% -${jsY}00%;"\
    >\
        <span class="swd_title">${jsName}</span>\
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

var jstpl_decree = '<div data-decree-id="${jsId}" class="decree decree_small" style="background-position: -${jsX}00% -${jsY}00%;"></div>';

var jstpl_cube = '<div class="agora_cube agora_cube_${jsPlayerAlias} ${jsControllerClass}"><span>${jsCount}</span></div>';

var jstpl_military_token = '<div class="military_token military_token_${jsValue}"></div>';

var jstpl_coin_animated = '<div class="coin animated"></div>';

var jstpl_tooltip_cost_me = '\
    <hr\>\
    <strong class="me">${translateCurrentCost}:</strong>\
    <div class="payment_plan me">\
        ${jsPayment}\
    </div>\
    <strong>${translateTotal}:</strong> ${jsCoinHtml}\
    ';

var jstpl_tooltip_cost_opponent = '\
    <hr\>\
    <strong class="opponent">${translateCurrentCost}:</strong>\
    <div class="payment_plan opponent">\
        ${jsPayment}\
    </div>\
    <div class="opponent"><strong>${translateTotal}:</strong> ${jsCoinHtml}</div>\
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

var jstpl_decree_tooltip = '\
    <div class="swd_tooltip decree_tooltip">\
        <div class="cardinfos">\
            <strong>${translateDecree}</strong>\
            <hr\>\
            <p>${jsText}</p>\
        </div>\
        <div>\
            <div class="decree tooltipWiggle" style="float:right; background-position: -${jsBackX}00% -${jsBackY}00%;"></div>\
        </div>\
        <div class="clear"></div>\
    </div>';

var jstpl_conspiracy_tooltip = '\
    <div class="swd_tooltip">\
        <div class="cardinfos">\
            <strong>${translateConspiracy} “${jsName}”</strong>\
            <hr\>\
            <p>${jsText}</p>\
        </div>\
        <div>\
            <div class="conspiracy tooltipWiggle" style="float:right; background-position: -${jsBackX}00% -${jsBackY}00%;">\
                <span class="swd_title">${jsName}</span>\
            </div>\
        </div>\
        <div class="clear"></div>\
    </div>';

</script>

{OVERALL_GAME_FOOTER}
