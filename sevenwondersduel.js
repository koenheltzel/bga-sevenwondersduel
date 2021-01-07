/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuelPantheon implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * sevenwondersduelpantheon.js
 *
 * SevenWondersDuelPantheon user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
        "dojo",
        "dojo/_base/declare",
        "dojo/query",
        "dojo/NodeList-traverse",
        "dojo/on",
        "dojo/dom",
        "dojo/cookie",
        "ebg/core/gamegui",
        "ebg/counter",
        g_gamethemeurl + "modules/js/CoinAnimator.js",
        g_gamethemeurl + "modules/js/CubeAnimator.js",
        g_gamethemeurl + "modules/js/MilitaryTrackAnimator.js",
    ],
    function (dojo, declare, on, dom, cookie) {
        return declare("bgagame.sevenwondersduelpantheon", ebg.core.gamegui, {

            instance: null,

            LAYOUT_AUTO: 'auto',
            LAYOUT_LANDSCAPE: 'landscape',
            LAYOUT_SQUARE: 'square',
            LAYOUT_PORTRAIT: 'portrait',

            // Show console.log messages
            debug: 1,
            pantheon: 0,
            agora: 0,
            expansion: 0,

            // Settings
            autoScale: 1,
            scale: 1,
            autoLayout: 1,
            layout: "",
            quality: "1x",
            showOpponentCost: 1,
            cookieExpireDays: 60,

            // Freezes layout during disabled card selection (remembering old scroll position)
            freezeLayout: 0,
            rememberScrollX: 0,
            rememberScrollY: 0,

            // Tooltip settings
            toolTipDelay: 200,

            // Game logic properties
            playerTurnBuildingId: null,
            playerTurnNode: null,
            currentAge: 0,
            myConspiracies: [],
            // Pantheon
            activateDivinityNode: null,
            activateDivinityId: 0,
            // Agora
            senateActionsSection: 0,
            moveInfluenceFrom: 0,
            moveDecreeFrom: 0,
            swapMeBuildingId: 0,
            swapOpponentBuildingId: 0,

            // General properties
            customTooltips: [],
            windowResizeTimeoutId: null,
            autoUpdateScaleTimeoutId: null,
            previousAvailableDimensions: null,
            playerTurnDescription: null,
            playerTurnDescriptionMyTurn: null,

            // Animation durations
            constructBuildingAnimationDuration: 1000,
            discardBuildingAnimationDuration: 400,
            constructWonderAnimationDuration: 1600,
            prepareConspiracyAnimationDuration: 1600,
            selectWonderAnimationDuration: 300,
            progressTokenDuration: 1000,
            twistCoinDuration: 250,
            turnAroundCardDuration: 500,
            putDraftpoolCard: 250,
            coin_slide_duration: 500,
            coin_slide_delay: 100,
            notification_safe_margin: 100,
            victorypoints_slide_duration: 1000,
            conspire_duration: 1000,
            construct_conspiracy_duration: 1600,
            choose_conspire_remnant_position_duration: 1600,
            place_divinity_duration: 1200,
            activate_divinity_duration: 1000,

            constructor: function () {
                bgagame.sevenwondersduelpantheon.instance = this;

                // Tooltip settings
                // dijit.Tooltip.defaultPosition = ["above-centered", "below-centered"];
            },

            /*
                setup:

                This method must set up the game user interface according to current game situation specified
                in parameters.

                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)

                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                if (this.debug) console.log("setup(gamedatas)", gamedatas);

                dojo.destroy('debug_output'); // TODO: Remove? See http://en.doc.boardgamearena.com/Tools_and_tips_of_BGA_Studio#Speed_up_game_re-loading_by_disabling_Input.2FOutput_debug_section

                this.gamedatas = gamedatas;

                this.agora = this.gamedatas.agora;
                this.pantheon = this.gamedatas.pantheon;
                this.expansion = this.agora || this.pantheon;

                // Because of spectators we can't assume everywhere that this.player_id is one of the two players.
                this.me_id = parseInt(this.gamedatas.me_id); // me = alias for the player on the bottom
                this.opponent_id = parseInt(this.gamedatas.opponent_id); // opponent = alias for the player on the top

                if (this.isCookieSetAndValid('swd_layout_v2')) {
                    this.layout = dojo.cookie('swd_layout_v2');
                    this.setLayout(this.layout);
                }
                else {
                    this.layout = this.LAYOUT_AUTO;
                }
                $('setting_layout').value = this.layout;

                if (this.isCookieSetAndValid('swd_autoScale_v2', true)) {
                    this.autoScale = parseInt(dojo.cookie('swd_autoScale_v2'));
                }
                if (!this.autoScale) {
                    if (this.isCookieSetAndValid('swd_scale', true)) {
                        this.scale = parseFloat(dojo.cookie('swd_scale'));
                        this.setScale(this.scale);
                    }
                    else {
                        this.autoScale = 1;
                    }
                }
                if (this.isCookieSetAndValid('swd_quality_v2')) {
                    this.quality = dojo.cookie('swd_quality_v2');
                }
                if (this.isCookieSetAndValid('swd_opponent_cost', true)) {
                    this.showOpponentCost = parseInt(dojo.cookie('swd_opponent_cost'));
                    $('setting_opponent_cost').value = this.showOpponentCost;
                }
                this.updateSettings();
                this.updateQuality(); // To update the data-quality attribute (it's important to have called updateSettings() first, so $('setting_quality').value is set correctly).

                if (this.quality == '2x') {
                    this.dontPreloadImage('board.png');
                    this.dontPreloadImage('buildings_v3.jpg');
                    this.dontPreloadImage('linked-building-icons.png');
                    this.dontPreloadImage('progress_tokens_v3.jpg');
                    this.dontPreloadImage('sprites.png');
                    this.dontPreloadImage('wonders_v3.jpg');
                }
                if (!this.agora) {
                    this.dontPreloadImage('agora_senate_tooltips.png');
                }
                if (this.quality == '2x' || !this.agora) {
                    this.dontPreloadImage('agora_conspiracies.jpg');
                    this.dontPreloadImage('agora_decrees.png');
                    this.dontPreloadImage('agora_senate.png');
                    this.dontPreloadImage('agora_sprites.png');
                }
                if (this.quality == '2x' || !this.pantheon) {
                    this.dontPreloadImage('pantheon_sprites.png');
                    this.dontPreloadImage('pantheon_board.png');
                    this.dontPreloadImage('pantheon_divinities.png');
                }

                if (this.quality == '1x') {
                    this.dontPreloadImage('board@2X.png');
                    this.dontPreloadImage('buildings_v3@2X.jpg');
                    this.dontPreloadImage('linked-building-icons@2X.png');
                    this.dontPreloadImage('progress_tokens_v3@2X.jpg');
                    this.dontPreloadImage('sprites@2X.png');
                    this.dontPreloadImage('wonders_v3@2X.jpg');
                }
                if (this.quality == '1x' || !this.agora) {
                    this.dontPreloadImage('agora_conspiracies@2X.jpg');
                    this.dontPreloadImage('agora_decrees@2X.png');
                    this.dontPreloadImage('agora_senate@2X.png');
                    this.dontPreloadImage('agora_sprites@2X.png');
                }
                if (this.quality == '1x' || !this.pantheon) {
                    this.dontPreloadImage('pantheon_sprites@2X.png');
                    this.dontPreloadImage('pantheon_board@2X.png');
                    this.dontPreloadImage('pantheon_divinities@2X.png');
                }

                // Setup game situation.
                this.updateWondersSituation(this.gamedatas.wondersSituation);
                this.updateDraftpool(this.gamedatas.draftpool, true);
                this.updateProgressTokensSituation(this.gamedatas.progressTokensSituation);
                this.updateMilitaryTrack(this.gamedatas.militaryTrack);
                this.updateDiscardedBuildings(this.gamedatas.discardedBuildings);
                if (this.pantheon) {
                    this.updateMythologyTokensSituation(this.gamedatas.mythologyTokensSituation);
                    this.updateOfferingTokensSituation(this.gamedatas.offeringTokensSituation);
                    this.updateDivinitiesSituation(this.gamedatas.divinitiesSituation);
                }
                if (this.agora) {
                    this.updateDecreesSituation(this.gamedatas.decreesSituation);
                    this.updateConspiracyDeckCount(this.gamedatas.conspiraciesSituation['deckCount']);
                    this.myConspiracies = this.gamedatas.myConspiracies;
                    this.updateConspiraciesSituation(this.gamedatas.conspiraciesSituation);
                    this.updateSenateSituation(this.gamedatas.senateSituation);
                }

                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];

                    var buttonNode;
                    if (player_id == this.me_id) {
                        buttonNode = $('buttonPlayerLeft');
                    } else {
                        buttonNode = $('buttonPlayerRight');
                    }
                    dojo.attr(buttonNode, 'data-player-id', player_id);
                    dojo.query('span', buttonNode)[0].innerHTML = player.name;

                    // Try (!) to get the player avatar. If we can't find the avater due to future BGA changes, remove it from the button.
                    var buttonImg = dojo.query('img', buttonNode)[0];
                    var avatar = dojo.query('#avatar_' + player_id)[0];
                    if (avatar) {
                        var avatarSrc = dojo.attr(avatar, 'src');
                        if (avatarSrc) {
                            dojo.attr(buttonImg, 'src', avatarSrc);
                        }
                    } else {
                        dojo.destroy(buttonImg);
                    }

                    this.updatePlayerWonders(player_id, this.gamedatas.wondersSituation[player_id]);
                    this.updatePlayerBuildings(player_id, this.gamedatas.playerBuildings[player_id]);

                    if (this.pantheon) {
                        // For Pantheon, only show 1 instead of 3 Progress Token outline placeholders (because we have placeholders for Mythology & Offering tokens too), so delete the last 2.
                        let outlineNodes = dojo.query('.player_info.' + this.getPlayerAlias(player_id) + ' .player_area_progress_tokens .progress_token_outline:empty');
                        dojo.destroy(outlineNodes[2]);
                        dojo.destroy(outlineNodes[1]);
                    }
                    this.updatePlayerProgressTokens(player_id, this.gamedatas.progressTokensSituation[player_id]);
                }
                // After updatePlayerProgressTokens so we can highlight the Law progress token for the endgameanimation.
                this.updatePlayersSituation(this.gamedatas.playersSituation);

                // Set setting dropdown values (translations don't work yet in the constructor, so we do it here).
                dojo.query('#setting_layout option[value="' + this.LAYOUT_AUTO + '"]')[0].innerText = _('Auto');
                dojo.query('#setting_layout option[value="' + this.LAYOUT_PORTRAIT + '"]')[0].innerText = _('Portrait');
                dojo.query('#setting_layout option[value="' + this.LAYOUT_SQUARE + '"]')[0].innerText = _('Square');
                dojo.query('#setting_layout option[value="' + this.LAYOUT_LANDSCAPE + '"]')[0].innerText = _('Landscape');

                dojo.query('#setting_auto_scale option[value="1"]')[0].innerText = _('Fit to screen (no scrolling)');
                dojo.query('#setting_auto_scale option[value="2"]')[0].innerText = _('Use full width (scroll vertically)');
                dojo.query('#setting_auto_scale option[value="0"]')[0].innerText = _('Manual');

                dojo.query('#setting_quality option[value="1x"]')[0].innerText = _('Normal');
                dojo.query('#setting_quality option[value="2x"]')[0].innerText = _('High definition');

                dojo.query('#setting_opponent_cost option[value="1"]')[0].innerText = _('Yes');
                dojo.query('#setting_opponent_cost option[value="0"]')[0].innerText = _('No');

                // Click handlers using event delegation:
                dojo.query('body')
                    .on("#swd *:click",
                        dojo.hitch(this, "closeTooltips")
                    );
                dojo.query('body')
                    .on("#swd[data-state=selectWonder] #wonder_selection_container .wonder:click",
                        dojo.hitch(this, "onWonderSelectionClick")
                    );
                dojo.query('body')
                    .on("#swd[data-state=playerTurn] #draftpool .building.available:click",
                        dojo.hitch(this, "onPlayerTurnDraftpoolClick")
                    );
                dojo.query('body')
                    .on("#swd[data-client-state=client_useAgeCard] #player_wonders_" + this.me_id + " .wonder_small:click",
                        dojo.hitch(this, "onPlayerTurnConstructWonderSelectedClick")
                    );
                dojo.query('body')
                    .on("#swd[data-state=chooseProgressToken] #board_progress_tokens .progress_token_small:click",
                        dojo.hitch(this, "onProgressTokenClick")
                    );
                dojo.query('body')
                    .on("#swd[data-state=selectStartPlayer] #select_start_player .action_button:click",
                        dojo.hitch(this, "onStartPlayerClick")
                    );
                dojo.query('body')
                    .on("#swd[data-state=chooseOpponentBuilding] .player_building_column.red_border .building_header_small:click",
                        dojo.hitch(this, "onOpponentBuildingClick")
                    );
                dojo.query('body')
                    .on("#swd[data-state=chooseDiscardedBuilding] #discarded_cards_container .building_small:click",
                        dojo.hitch(this, "onDiscardedBuildingClick")
                    );
                dojo.query('body')
                    .on("#swd[data-state=chooseProgressTokenFromBox] #progress_token_from_box_container .progress_token :click",
                        dojo.hitch(this, "onProgressTokenFromBoxClick")
                    );
                dojo.query('body')
                    .on("#adminControls a.admin_function :click",
                        dojo.hitch(this, "onAdminFunctionClick")
                    );

                // Click handlers without event delegation:
                dojo.query("#buttonConstructBuilding").on("click", dojo.hitch(this, "onPlayerTurnConstructBuildingClick"));
                dojo.query("#buttonDiscardBuilding").on("click", dojo.hitch(this, "onPlayerTurnDiscardBuildingClick"));
                dojo.query("#buttonConstructWonder").on("click", dojo.hitch(this, "onPlayerTurnConstructWonderClick"));

                dojo.query("#setting_auto_scale").on("change", dojo.hitch(this, "onSettingAutoScaleChange"));
                dojo.query("#setting_scale").on("change", dojo.hitch(this, "onSettingScaleChange"));
                dojo.query("#setting_layout").on("change", dojo.hitch(this, "onSettingLayoutChange"));
                dojo.query("#setting_quality").on("change", dojo.hitch(this, "onSettingQualityChange"));
                dojo.query("#setting_opponent_cost").on("change", dojo.hitch(this, "onSettingOpponentCostChange"));

                if (this.pantheon) {
                    // Pantheon click handlers using event delegation:
                    dojo.query('body')
                        .on("#swd[data-state=chooseAndPlaceDivinity] #choose_and_place_divinity .divinity_small :click",
                            dojo.hitch(this, "onChooseDivinityClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=chooseAndPlaceDivinity] .pantheon_space_containers .red_border :click",
                            dojo.hitch(this, "onPlaceDivinityClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=playerTurn] .pantheon_space_containers .divinity.divinity_border :click",
                            dojo.hitch(this, "onActivateDivinityClick")
                        );

                    // Pantheon click handlers without event delegation:
                    dojo.query("#activate_divinity_confirm").on("click", dojo.hitch(this, "onActivateDivinityConfirmClick"));

                }
                // Click handlers shared between Agora and Pantheon:
                if (this.agora || this.pantheon) {
                    dojo.query('body')
                        .on("#swd[data-state=takeBuilding] .player_building_column.red_border .building_header_small:click",
                            dojo.hitch(this, "onTakeBuildingClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=takeUnconstructedWonder] .wonder.red_border:click",
                            dojo.hitch(this, "onTakeUnconstructedWonderClick")
                        );
                }
                if (this.agora) {
                    // Agora click handlers using event delegation:
                    dojo.query('body')
                        .on("#swd[data-state=chooseConspiratorAction] #choose_conspirator_action .bgabutton_blue :click",
                            dojo.hitch(this, "onChooseConspiratorActionClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=conspire] .conspiracy_small :click",
                            dojo.hitch(this, "onChooseConspiracyClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=chooseConspireRemnantPosition] #choose_conspire_remnant_position .action_button :click",
                            dojo.hitch(this, "onChooseConspireRemnantPositionClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-client-state=client_useAgeCard] #player_conspiracies_" + this.me_id + " .conspiracy_small[data-conspiracy-prepared=\"0\"][data-conspiracy-triggered=\"0\"]:click",
                            dojo.hitch(this, "onPlayerTurnPrepareConspiracySelectedClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=playerTurn] #player_conspiracies_" + this.me_id + " .conspiracy_small[data-conspiracy-prepared=\"1\"][data-conspiracy-triggered=\"0\"].green_border:click," +
                            "#swd[data-state=triggerUnpreparedConspiracy] #player_conspiracies_" + this.me_id + " .conspiracy_small[data-conspiracy-prepared=\"0\"][data-conspiracy-triggered=\"0\"].green_border:click",
                            dojo.hitch(this, "onPlayerTurnTriggerConspiracyClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=triggerUnpreparedConspiracy] #buttonSimpleSkip:click",
                            dojo.hitch(this, "onPlayerTurnSkipTriggerConspiracyClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=moveInfluence] #buttonSimpleSkip:click",
                            dojo.hitch(this, "onSenateActionsSkipButtonClick")
                        );

                    dojo.query('body')
                        .on("#swd[data-client-state=client_placeInfluence] #senate_chambers .red_stroke:click," +
                            "#swd[data-state=placeInfluence] #senate_chambers .red_stroke:click",
                            dojo.hitch(this, "onPlaceInfluenceClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=removeInfluence] #senate_chambers .red_stroke:click",
                            dojo.hitch(this, "onRemoveInfluenceClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-client-state=client_moveInfluenceFrom] #senate_chambers .red_stroke:click",
                            dojo.hitch(this, "onMoveInfluenceFromClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-client-state=client_moveInfluenceTo] #senate_chambers .red_stroke:click",
                            dojo.hitch(this, "onMoveInfluenceToClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-client-state=client_moveInfluenceTo] #senate_chambers .gray_stroke:click",
                            dojo.hitch(this, "onMoveInfluenceCancelClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=constructLastRowBuilding] #draftpool .row1.red_border:click",
                            dojo.hitch(this, "onConstructLastRowBuildingClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=destroyConstructedWonder] .wonder.red_border:click",
                            dojo.hitch(this, "onDestroyConstructedWonderClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=discardAvailableCard] #draftpool .available:click",
                            dojo.hitch(this, "onDiscardAvailableCardClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=discardAvailableCard] #buttonSimpleSkip:click",
                            dojo.hitch(this, "onDiscardAvailableCardSkipButtonClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-client-state=client_moveDecreeFrom] .decree_containers .red_border:click",
                            dojo.hitch(this, "onMoveDecreeFromClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-client-state=client_moveDecreeTo] #senate_chambers .red_stroke:click",
                            dojo.hitch(this, "onMoveDecreeToClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-client-state=client_moveDecreeTo] .decree_containers .gray_border:click",
                            dojo.hitch(this, "onMoveDecreeCancelClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=swapBuilding] .player_building_column.red_border .building_header_small:click",
                            dojo.hitch(this, "onSwapBuildingClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=constructBuildingFromBox] #construct_building_from_box .building_small:click",
                            dojo.hitch(this, "onConstructBuildingFromBoxClick")
                        );
                    dojo.query('body')
                        .on("#swd[data-state=lockProgressToken] .progress_token_small :click",
                            dojo.hitch(this, "onLockProgressTokenClick")
                        );

                    // Agora click handlers without event delegation:
                    dojo.query("#buttonPrepareConspiracy").on("click", dojo.hitch(this, "onPlayerTurnPrepareConspiracyClick"));
                    dojo.query('body')
                        .on("#swd #senate_actions #buttonSenateActionsPlaceInfluence.bgabutton_blue :click",
                            dojo.hitch(this, "onSenateActionsPlaceInfluenceButtonClick")
                        );
                    dojo.query('body')
                        .on("#swd #senate_actions #buttonSenateActionsMoveInfluence.bgabutton_blue :click",
                            dojo.hitch(this, "onSenateActionsMoveInfluenceButtonClick")
                        );
                    dojo.query("#buttonSenateActionsSkip").on("click", dojo.hitch(this, "onSenateActionsSkipButtonClick"));
                }

                // Resize/scroll handler to determine layout and scale factor
                window.addEventListener('resize', dojo.hitch(this, "onWindowUpdate"));
                window.addEventListener('scroll', dojo.hitch(this, "onWindowUpdate"));

                // Tool tips using event delegation:
                this.setupTooltips();

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                // Debug tooltip content by placing a tooltip at the top of the screen.
                // dojo.place( this.getWonderTooltip(11, this.opponent_id, '<div class="coin"><span style="color: red !important">9</span></div>'), 'swd_wrap', 'first' );
                // dojo.place( this.getBuildingTooltip(21, false, '', '', '', ''), 'swd_wrap', 'first');

                // At the beginning swdPosition's y position is 265 (when it's not visible), so retry after loading to update the layout.
                this.callFunctionAfterLoading(dojo.hitch(this, "updateLayout"));
            },

            testDivinities: function (playerId) {
                for (let place = 1; place <= 6; place++) {
                    if (playerId == 2310958 && place != 4) {
                        let divinityType = Math.ceil(Math.random() * 5);
                        let node = dojo.query('.pantheon_space_containers > div:nth-of-type(' + place + ')');
                        node.empty();
                        dojo.place( this.getDivinityDivHtml(0, divinityType, false), node[0], 'first');
                    }

                    if (place % 3 == 0) {
                        let divinity = Math.ceil(Math.random() * 16);
                        let divinityType = this.gamedatas.divinities[divinity].type;
                        dojo.place( this.getDivinityDivHtml(divinity, divinityType, false), 'player_conspiracies_' + playerId, 'first');
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:

                In this method, you associate each of your game notifications with your local method to handle it.

                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your sevenwondersduelpantheon.game.php file.

            */
            setupNotifications: function () {
                if (this.debug) console.log('notifications subscriptions setup');

                // Example 1: standard notification handling
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

                // Example 2: standard notification handling + tell the user interface to wait
                //            during 3 seconds after calling the method in order to var the players
                //            see what is happening in the game.
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
                // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
                //

                dojo.subscribe('wonderSelected', this, "notif_wonderSelected");
                this.notifqueue.setSynchronous('wonderSelected');

                dojo.subscribe('nextAge', this, "notif_nextAge");

                dojo.subscribe('constructBuilding', this, "notif_constructBuilding");
                this.notifqueue.setSynchronous('constructBuilding');

                dojo.subscribe('discardBuilding', this, "notif_discardBuilding");
                this.notifqueue.setSynchronous('discardBuilding');

                dojo.subscribe('constructWonder', this, "notif_constructWonder");
                this.notifqueue.setSynchronous('constructWonder');

                dojo.subscribe('progressTokenChosen', this, "notif_progressTokenChosen");
                this.notifqueue.setSynchronous('progressTokenChosen');

                dojo.subscribe('opponentDiscardBuilding', this, "notif_opponentDiscardBuilding");
                this.notifqueue.setSynchronous('opponentDiscardBuilding');

                dojo.subscribe('nextAgeDraftpoolReveal', this, "notif_nextAgeDraftpoolReveal");
                this.notifqueue.setSynchronous('nextAgeDraftpoolReveal');

                dojo.subscribe('nextPlayerTurnScientificSupremacy', this, "notif_nextPlayerTurnScientificSupremacy");
                this.notifqueue.setSynchronous('nextPlayerTurnScientificSupremacy');

                dojo.subscribe('nextPlayerTurnMilitarySupremacy', this, "notif_nextPlayerTurnMilitarySupremacy");
                this.notifqueue.setSynchronous('nextPlayerTurnMilitarySupremacy');

                dojo.subscribe('nextPlayerTurnPoliticalSupremacy', this, "notif_nextPlayerTurnPoliticalSupremacy");
                this.notifqueue.setSynchronous('nextPlayerTurnPoliticalSupremacy');

                dojo.subscribe('nextPlayerTurnEndGameScoring', this, "notif_nextPlayerTurnEndGameScoring");
                this.notifqueue.setSynchronous('nextPlayerTurnEndGameScoring');

                dojo.subscribe('endGameCategoryUpdate', this, "notif_endGameCategoryUpdate");
                this.notifqueue.setSynchronous('endGameCategoryUpdate');

                // Pantheon

                dojo.subscribe('takeToken', this, "notif_takeToken");
                this.notifqueue.setSynchronous('takeToken');

                dojo.subscribe('placeDivinity', this, "notif_placeDivinity");
                this.notifqueue.setSynchronous('placeDivinity');

                dojo.subscribe('activateDivinity', this, "notif_activateDivinity");
                this.notifqueue.setSynchronous('activateDivinity');

                // Agora

                dojo.subscribe('constructConspiracy', this, "notif_constructConspiracy");
                this.notifqueue.setSynchronous('constructConspiracy');

                dojo.subscribe('chooseConspireRemnantPosition', this, "notif_chooseConspireRemnantPosition");
                this.notifqueue.setSynchronous('chooseConspireRemnantPosition');

                dojo.subscribe('conspireKeepBoth', this, "notif_conspireKeepBoth");
                this.notifqueue.setSynchronous('conspireKeepBoth');

                dojo.subscribe('prepareConspiracy', this, "notif_prepareConspiracy");
                this.notifqueue.setSynchronous('prepareConspiracy');

                dojo.subscribe('triggerConspiracy', this, "notif_triggerConspiracy");
                this.notifqueue.setSynchronous('triggerConspiracy');

                dojo.subscribe('placeInfluence', this, "notif_placeInfluence");
                this.notifqueue.setSynchronous('placeInfluence');

                dojo.subscribe('moveInfluence', this, "notif_moveInfluence");
                this.notifqueue.setSynchronous('moveInfluence');

                dojo.subscribe('removeInfluence', this, "notif_removeInfluence");
                this.notifqueue.setSynchronous('removeInfluence');

                dojo.subscribe('decreeControlChanged', this, "notif_decreeControlChanged");
                this.notifqueue.setSynchronous('decreeControlChanged');

                dojo.subscribe('destroyConstructedWonder', this, "notif_destroyConstructedWonder");
                this.notifqueue.setSynchronous('destroyConstructedWonder');

                dojo.subscribe('lockProgressToken', this, "notif_lockProgressToken");
                this.notifqueue.setSynchronous('lockProgressToken');

                dojo.subscribe('moveDecree', this, "notif_moveDecree");
                this.notifqueue.setSynchronous('moveDecree');

                dojo.subscribe('swapBuilding', this, "notif_swapBuilding");
                this.notifqueue.setSynchronous('swapBuilding');

                dojo.subscribe('takeBuilding', this, "notif_takeBuilding");
                this.notifqueue.setSynchronous('takeBuilding');

                dojo.subscribe('takeUnconstructedWonder', this, "notif_takeUnconstructedWonder");
                this.notifqueue.setSynchronous('takeUnconstructedWonder');

            },

            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                if (this.debug) console.log('Entering state: ' + stateName, args);

                // Reset the client state
                dojo.attr($('swd'), 'data-client-state', '');

                if (args.args && stateName.substring(0, 7) != "client_") {
                    dojo.attr($('swd'), 'data-state', stateName);

                    // Update player coins / scores
                    if (args.args.playersSituation) {
                        this.updatePlayersSituation(args.args.playersSituation);
                    }

                    if (args.args.divinitiesSituation) this.updateDivinitiesSituation(args.args.divinitiesSituation);
                    if (args.args.wondersSituation) this.updateWondersSituation(args.args.wondersSituation);
                    if (args.args._private && args.args._private.myConspiracies) this.myConspiracies = args.args._private.myConspiracies;
                    if (args.args.conspiraciesSituation) this.updateConspiraciesSituation(args.args.conspiraciesSituation);
                    if (args.args.mythologyTokensSituation) this.updateMythologyTokensSituation(args.args.mythologyTokensSituation);
                    if (args.args.offeringTokensSituation) this.updateOfferingTokensSituation(args.args.offeringTokensSituation);
                    if (args.args.senateSituation) this.updateSenateSituation(args.args.senateSituation);
                    if (args.args.wonderSelectionRound) {
                        $('wonder_selection_block_title').innerText = dojo.string.substitute(_("Wonders selection - round ${round} of 2"), {
                            round: args.args.wonderSelectionRound
                        });
                    }

                    if (args.args.draftpool) {
                        // Wait for loading screen (for Agora, when Age I draftpool gets revealed at the start of the game).
                        this.callFunctionAfterLoading(dojo.hitch(this, "updateDraftpool"), [args.args.draftpool]);
                    }

                    if (0 && this.pantheon) {
                        this.testDivinities(2310957);
                        this.testDivinities(2310958);
                    }

                    this.updateLayout(); // Because of added height of action button divs being auto shown/hidden because of the state change, it's a good idea to update the layout here.

                    // We chose to group all of the states' functions together, so we create a seperate "onEnter{StateName}" function and call it here if it exists.
                    var functionName = 'onEnter' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
                    if (typeof this[functionName] === 'function') {
                        this[functionName](args.args);
                    }
                }
                else {
                    dojo.attr($('swd'), 'data-client-state', stateName);
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                if (this.debug) console.log('Leaving state: ' + stateName);

                switch (stateName) {

                    /* Example:

                    case 'myGameState':

                        // Hide the HTML block we are displaying only during this game state
                        dojo.style( 'my_html_block_id', 'display', 'none' );

                        break;
                   */


                    case 'discardAvailableCard':
                        this.clearRedBorder(); // Clear Insider Influence borders if the second step is skipped.
                        break;
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //
            onUpdateActionButtons: function (stateName, args) {
                if (this.debug) console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        /*
                                         Example:

                                         case 'myGameState':

                                            // Add 3 action buttons in the action status bar:

                                            this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                                            this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                                            this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                                            break;
                        */
                    }
                }
            },

            //  __        __              _
            //  \ \      / /__  _ __   __| | ___ _ __ ___
            //   \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__/ __|
            //    \ V  V / (_) | | | | (_| |  __/ |  \__ \
            //     \_/\_/ \___/|_| |_|\__,_|\___|_|  |___/

            getWonderCardData: function (playerId, wonderId) {
                for (var i = 0; i < this.gamedatas.wondersSituation[playerId].length; i++) {
                    var cardData = this.gamedatas.wondersSituation[playerId][i];
                    if (cardData.wonder == wonderId) {
                        return cardData;
                    }
                }
                return null;
            },

            getWonderDivHtml: function (wonderId, displayCost, cost, playerCoins) {
                var wonder = this.gamedatas.wonders[wonderId];
                var data = {
                    jsId: wonderId,
                    jsName: _(wonder.name),
                    jsConstructed: displayCost ? 0 : 1,
                    jsDisplayCost: displayCost ? 'inline-block' : 'none',
                    jsCost: this.getCostValue(cost),
                    jsCostColor: this.getCostColor(cost, playerCoins),
                };
                data.jsCostClass = isNaN(data.jsCost) ? 'cost_free' : '';
                var spritesheetColumns = 5;
                data.jsX = (wonderId - 1) % spritesheetColumns;
                data.jsY = Math.floor((wonderId - 1) / spritesheetColumns);
                return this.format_block('jstpl_wonder', data);
            },

            updateWondersSituation: function (situation) {
                this.gamedatas.wondersSituation = situation;
                if (situation.selection.length < 4) { // else
                    this.updateWonderSelection(situation.selection);
                }
                for (var player_id in this.gamedatas.players) {
                    this.updatePlayerWonders(player_id, situation[player_id]);
                }
            },

            /*
            Transition (3D flip) from old node to new node. Destroy old node afterwards.
             */
            twistAnimation: function (oldNode, newNode, autoPlay=true, delay=0, duration=-1) {
                if (duration == -1) duration = this.twistCoinDuration;
                if (!oldNode || dojo.style(oldNode, 'display') == 'none' || oldNode.outerHTML != newNode.outerHTML) {
                    var displayValue = dojo.style(newNode, 'display'); // probably inline-block or block.
                    dojo.style(newNode, 'display', 'none');

                    var anims = [];
                    if (oldNode) {
                        dojo.place(oldNode, newNode.parentElement);
                        var oldNodeAnim = dojo.animateProperty({
                            node: oldNode,
                            delay: delay,
                            duration: duration / 2,
                            easing: dojo.fx.easing.linear,
                            properties: {
                                propertyTransform: {start: 0, end: 90}
                            },
                            onAnimate: function (values) {
                                dojo.style(oldNode, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                            },
                            onEnd: dojo.hitch(this, function (node) {
                                dojo.destroy(oldNode);
                            })
                        });
                        anims.push(oldNodeAnim);
                    }

                    var newNodeAnim = dojo.animateProperty({
                        node: newNode,
                        duration: duration / 2,
                        easing: dojo.fx.easing.linear,
                        properties: {
                            propertyTransform: {start: -90, end: 0}
                        },
                        onPlay: function (values) {
                            dojo.destroy(oldNode);
                            dojo.style(newNode, 'display', displayValue);
                            dojo.style(newNode, 'transform', 'perspective(40em) rotateY(-90deg)'); // When delay > 0 this is necesarry to hide the new node.
                        },
                        onAnimate: function (values) {
                            dojo.style(newNode, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                        }
                    });
                    anims.push(newNodeAnim);

                    var anim = dojo.fx.chain(anims);
                    if (autoPlay) anim.play();
                    return anim;
                } else {
                    dojo.destroy(oldNode);
                }
            },

            updatePlayerNumberOfWondersStyling: function (playerId, count) {
                let playerWonderContainer = dojo.query('.player_wonders.player' + playerId)[0];
                let wondersCount = Math.max(4, count);
                this.setCssVariable('--number-of-wonders', wondersCount , playerWonderContainer);
                this.setCssVariable('--number-of-wonder-rows-landscape', Math.ceil(wondersCount / 2) , playerWonderContainer);
                if (wondersCount > 4) {
                    dojo.style(dojo.query('.player_wonders.player' + playerId + ' > div:nth-of-type(5)')[0], 'display', wondersCount > 4 ? 'inline-block' : 'none');
                }
                if (wondersCount > 5) {
                    dojo.style(dojo.query('.player_wonders.player' + playerId + ' > div:nth-of-type(6)')[0], 'display', wondersCount > 5 ? 'inline-block' : 'none');
                }
                this.autoUpdateScale();
            },

            updatePlayerWonders: function (playerId, rows) {
                if (this.debug) console.log('updatePlayerWonders', playerId, rows);

                this.updatePlayerNumberOfWondersStyling(playerId, Math.max(4, rows.length > 0 ? parseInt(rows[rows.length - 1].position) : 0));

                var i = 1;
                Object.keys(rows).forEach(dojo.hitch(this, function (index) {
                    var row = rows[index];
                    var container = dojo.query('.player_wonders.player' + playerId + '>div:nth-of-type(' + row.position + ')')[0];

                    var oldNode = dojo.query('.wonder_container', container)[0];

                    var wonderDivHtml = this.getWonderDivHtml(row.wonder, row.constructed == 0, row.cost, this.gamedatas.playersSituation[playerId].coins);
                    var newNode = dojo.place(wonderDivHtml, container);
                    if (row.constructed > 0) {
                        var data = {
                            jsX: row.ageCardSpriteXY[0],
                            jsY: row.ageCardSpriteXY[1]
                        };
                        dojo.place(this.format_block('jstpl_wonder_age_card', data), dojo.query('.age_card_container', newNode)[0]);
                    }
                    this.twistAnimation(
                        dojo.query('.player_wonder_cost', oldNode)[0],
                        dojo.query('.player_wonder_cost', newNode)[0],
                    );
                    if (oldNode) dojo.destroy(oldNode);
                    i++;
                }));
            },

            //   ____        _ _     _ _                           ____             __ _                     _
            //  | __ ) _   _(_) | __| (_)_ __   __ _ ___          |  _ \ _ __ __ _ / _| |_ _ __   ___   ___ | |
            //  |  _ \| | | | | |/ _` | | '_ \ / _` / __|  _____  | | | | '__/ _` | |_| __| '_ \ / _ \ / _ \| |
            //  | |_) | |_| | | | (_| | | | | | (_| \__ \ |_____| | |_| | | | (_| |  _| |_| |_) | (_) | (_) | |
            //  |____/ \__,_|_|_|\__,_|_|_| |_|\__, |___/         |____/|_|  \__,_|_|  \__| .__/ \___/ \___/|_|
            //                                 |___/                                      |_|

            getBuildingDivHtml: function (id, building) {
                var data = {
                    jsId: id,
                    jsX: building.spriteXY[0],
                    jsY: building.spriteXY[1],
                    jsOrder: 0
                };
                if (building.type == "Green") {
                    data.jsOrder = building.scientificSymbol.toString() + building.age.toString();
                }

                return this.format_block('jstpl_player_building', data);
            },

            updatePlayerBuildings: function (playerId, cards) {
                if (this.debug) console.log('updatePlayerBuildings: ', playerId, cards);

                var i = 1;
                Object.keys(cards).forEach(dojo.hitch(this, function (index) {
                    var building = this.gamedatas.buildings[cards[index].id];
                    var container = dojo.query('.player_buildings.player' + playerId + ' .' + building.type)[0];
                    dojo.place(this.getBuildingDivHtml(building.id, building), container);
                    i++;
                }));
            },

            getDraftpoolCardData: function (buildingId) {
                for (var i = 0; i < this.gamedatas.draftpool.cards.length; i++) {
                    var position = this.gamedatas.draftpool.cards[i];
                    if (typeof position.building != 'undefined' && position.building == buildingId) {
                        return position;
                    }
                }
                return null;
            },

            /**
             * @param draftpool
             * @param setupGame
             * @returns {number} Duration of the full animation.
             */
            updateDraftpool: function (draftpool, setupGame = false, forceAnimation = false) {
                if (this.debug) console.log('updateDraftpool: ', draftpool, setupGame, 'age: ', draftpool.age);

                let currentState = dojo.attr($('swd'), 'data-state');
                if (!setupGame && currentState != "playerTurn" && !forceAnimation) {
                    setupGame = true; // Only animate in the draftpool when in playerTurn (to prevent Agora wonder selection actions getting a draftpool setup animation on every F5).
                }

                let realAgeOrExpansion = (draftpool.age > 0 || this.expansion);

                dojo.style('draftpool_container', 'display', realAgeOrExpansion ? 'flex' : 'none');

                dojo.attr($('swd'), 'data-age', draftpool.age == undefined ? 0 : draftpool.age);

                // New age. Animate the build up
                if (realAgeOrExpansion && draftpool.age >= this.currentAge) {
                    this.currentAge = draftpool.age; // This currentAge business is a bit of dirty check to prevent older notifications (due to animations finishing) arriving after newer notifications. Especially when a new age has arrived.
                    this.gamedatas.draftpool = draftpool;

                    var animationDelay = 100; // Have some initial delay, so this function can finish updating the DOM.
                    for (var i = 0; i < draftpool.cards.length; i++) {
                        var position = draftpool.cards[i];

                        var oldNodes = dojo.query('#draftpool .row' + position.row + '.column' + position.column);
                        if (oldNodes.length > 1) {
                            // This card is already being updated, probably by a previous updateDraftpool. We skip it.
                            continue;
                        }
                        var oldNode = oldNodes.length == 1 ? oldNodes[0] : null;

                        var linkedBuildingId = 0;
                        var data = {
                            jsId: '',
                            jsName: '',
                            jsType: '',
                            jsRow: position.row,
                            jsColumn: position.column,
                            jsX: position.spriteXY[0],
                            jsY: position.spriteXY[1],
                            jsZindex: position.row,
                            jsAvailable: position.available ? 'available' : '',
                            jsDisplayCostMe: 'none',
                            jsCostColorMe: 'black',
                            jsCostMe: -1,
                            jsCostMeClass: '',
                            jsDisplayCostOpponent: 'none',
                            jsCostColorOpponent: 'black',
                            jsCostOpponent: -1,
                            jsCostOpponentClass: '',
                            jsLinkX: 0,
                            jsLinkY: 0,
                        };
                        if (typeof position.building != 'undefined') {
                            var buildingData = this.gamedatas.buildings[position.building];
                            data.jsId = position.building;
                            data.jsName = _(buildingData.name);
                            data.jsType = buildingData.type;
                            if (position.available) {
                                data.jsDisplayCostMe = position.available ? 'block' : 'none',
                                data.jsCostColorMe = this.getCostColor(position.cost[this.me_id], this.gamedatas.playersSituation[this.me_id].coins),
                                data.jsCostMe = this.getCostValue(position.cost[this.me_id]);
                                data.jsCostMeClass = isNaN(data.jsCostMe) ? 'cost_free' : '';

                                data.jsDisplayCostOpponent = position.available ? 'block' : 'none',
                                data.jsCostColorOpponent = this.getCostColor(position.cost[this.opponent_id], this.gamedatas.playersSituation[this.opponent_id].coins),
                                data.jsCostOpponent = this.getCostValue(position.cost[this.opponent_id]);
                                data.jsCostOpponentClass = isNaN(data.jsCostOpponent) ? 'cost_free' : '';
                            }

                            // Linked building symbol
                            linkedBuildingId = buildingData.linkedBuilding;
                            if (linkedBuildingId != 0) {
                                var spritesheetColumns = 9;
                                var linkedBuildingSpriteId = 0;
                                if (linkedBuildingId < 0) {
                                    linkedBuildingSpriteId = 18 + Math.abs(linkedBuildingId);
                                }
                                else {
                                    linkedBuildingSpriteId = this.gamedatas.buildingIdsToLinkIconId[linkedBuildingId];
                                }
                                data.jsLinkX = (linkedBuildingSpriteId - 1) % spritesheetColumns;
                                data.jsLinkY = Math.floor((linkedBuildingSpriteId - 1) / spritesheetColumns);
                            }
                        }

                        var newNode = dojo.place(this.format_block('jstpl_draftpool_building', data), 'draftpool');

                        // Remove linked symbols dom elements that aren't needed.
                        Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                            if (linkedBuildingId == 0 || (!position.hasLinkedBuilding || !position.hasLinkedBuilding[playerId])) {
                                dojo.destroy(dojo.query('.' + this.getPlayerAlias(playerId) + ' .linked_building_icon', newNode)[0]);
                            }
                        }));

                        if (oldNode) {
                            // Turn around age cards.
                            if (dojo.attr(oldNode, "data-building-id") == "" && typeof position.building != 'undefined') {
                                dojo.style(newNode, 'transform', 'perspective(40em) rotateY(-180deg)'); // When delay > 0 this is necesarry to hide the new node.
                                var anim = dojo.fx.chain([
                                    dojo.fx.combine([
                                        dojo.animateProperty({
                                            node: oldNode,
                                            delay: animationDelay,
                                            duration: this.turnAroundCardDuration,
                                            easing: dojo.fx.easing.linear,
                                            properties: {
                                                propertyTransform: {start: 0, end: 180}
                                            },
                                            onAnimate: function (values) {
                                                dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                            },
                                            onEnd: dojo.hitch(this, function (node) {
                                                dojo.destroy(node);
                                            })
                                        }),
                                        dojo.animateProperty({
                                            node: newNode,
                                            delay: animationDelay,
                                            duration: this.turnAroundCardDuration,
                                            easing: dojo.fx.easing.linear,
                                            properties: {
                                                propertyTransform: {start: -180, end: 0}
                                            },
                                            onAnimate: function (values) {
                                                dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                            }
                                        }),
                                    ]),
                                ]);

                                anim.play();

                                animationDelay += this.turnAroundCardDuration * 0.75;
                            }
                            // Animate the updating of the card cost?
                            else {
                                this.twistAnimation(
                                    dojo.query('.draftpool_building_cost.me', oldNode)[0],
                                    dojo.query('.draftpool_building_cost.me', newNode)[0],
                                );
                                this.twistAnimation(
                                    dojo.query('.draftpool_building_cost.opponent', oldNode)[0],
                                    dojo.query('.draftpool_building_cost.opponent', newNode)[0],
                                );
                                dojo.destroy(oldNode);
                            }
                        }
                        // Start of age, fade in / sleightly 3D turn in cards.
                        else if (!setupGame) {
                            dojo.style(newNode, 'opacity', 0);
                            dojo.animateProperty({
                                node: newNode,
                                delay: animationDelay,
                                duration: this.putDraftpoolCard,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    opacity: {start: 0.0, end: 1.0},
                                    propertyScale: {start: 1.15, end: 1},
                                    propertyTransform: {start: -40, end: 0},
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg) scale(' + parseFloat(values.propertyScale.replace("px", "")) + ')');
                                }
                            }).play();
                            animationDelay += this.putDraftpoolCard * 0.75;
                        }
                    }

                    // Mythology tokens
                    if (this.pantheon) {
                        if (draftpool.mythologyTokens) {
                            for (let i = 0; i < draftpool.mythologyTokens.length; i++) {
                                let card = draftpool.mythologyTokens[i];
                                let row = card.rowCol[0];
                                let col = card.rowCol[1];
                                let node = dojo.query('#draftpool #' + row + '_' + col)[0];
                                let tokenNode = dojo.query('.mythology_token', node);
                                if (!tokenNode[0]) {
                                    dojo.place( this.getMythologyTokenHtml(card.id, card.type, false), node);
                                }
                            }
                        }
                        if (draftpool.offeringTokens) {
                            for (let i = 0; i < draftpool.offeringTokens.length; i++) {
                                let card = draftpool.offeringTokens[i];
                                let row = card.rowCol[0];
                                let col = card.rowCol[1];
                                let node = dojo.query('#draftpool #' + row + '_' + col)[0];
                                let tokenNode = dojo.query('.offering_token', node);
                                if (!tokenNode[0]) {
                                    dojo.place( this.getOfferingTokenHtml(card.id, card.type, false), node);
                                }
                            }
                        }
                    }

                    // Adjust the height of the age divs based on the age cards absolutely positioned within.
                    var rows = draftpool.age == 3 ? 7 : 5;
                    dojo.query('.draftpool').style("height", "calc(var(--building-height) * var(--building-small-scale) + " + (rows - 1) + ".35 * var(--draftpool-row-height))");

                    this.updateLayout();

                    return animationDelay + this.putDraftpoolCard;
                }
                return 0;
            },

            createDiscardedBuildingNode: function (buildingId) {
                // Set up a wrapper div so we can move the building to pos 0,0 of that wrapper
                var discardedCardsContainer = $('discarded_cards_container');
                var wrapperDiv = dojo.clone(dojo.query('.discarded_cards_cursor', discardedCardsContainer)[0]);
                dojo.removeClass(wrapperDiv, 'discarded_cards_cursor');
                dojo.place(wrapperDiv, discardedCardsContainer, discardedCardsContainer.children.length - 1);

                var newNode = this.getDiscardedBuildingNode(buildingId)
                dojo.place(newNode, wrapperDiv);
                return newNode;
            },

            getDiscardedBuildingNode: function (buildingId) {
                var building = this.gamedatas.buildings[buildingId];
                var spriteId = buildingId;
                var linkedBuildingId = 0;
                var data = {
                    jsId: buildingId,
                    jsName: _(building.name),
                    jsType: building.type,
                    jsRow: '',
                    jsColumn: '',
                    jsX: building.spriteXY[0],
                    jsY: building.spriteXY[1],
                    jsZindex: 1,
                    jsAvailable: '',
                    jsDisplayCostMe: 'none',
                    jsCostColorMe: 'black',
                    jsCostMe: -1,
                    jsCostMeClass: '',
                    jsDisplayCostOpponent: 'none',
                    jsCostColorOpponent: 'black',
                    jsCostOpponent: -1,
                    jsCostOpponentClass: '',
                    jsLinkX: 0,
                    jsLinkY: 0,
                };

                var newNode = dojo.place(this.format_block('jstpl_draftpool_building', data), 'draftpool');
                dojo.attr(newNode, 'id', ''); // Remove the draftpool specific id
                return newNode;
            },

            updateDiscardedBuildings: function (discardedBuildings) {
                this.gamedatas.discardedBuildings = discardedBuildings;

                Object.keys(this.gamedatas.discardedBuildings).forEach(dojo.hitch(this, function (index) {
                    var building = this.gamedatas.discardedBuildings[index];
                    this.createDiscardedBuildingNode(building.id);
                }));
            },

            //  ____                                      _____     _
            // |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __  ___
            // | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \/ __|
            // |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | \__ \
            // |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|___/
            //                  |___/

            updateProgressTokensSituation: function (progressTokensSituation) {
                if (this.debug) console.log('updateProgressTokensSituation: ', progressTokensSituation);
                this.gamedatas.progressTokensSituation = progressTokensSituation;

                dojo.query("#board_progress_tokens>div").forEach(dojo.empty);

                for (var i = 0; i < progressTokensSituation.board.length; i++) {
                    var location = progressTokensSituation.board[i];
                    var progressToken = this.gamedatas.progressTokens[location.id];
                    var position = parseInt(progressTokensSituation.board[i].location_arg);
                    var container = dojo.query('#board_progress_tokens>div:nth-of-type(' + (position + 1) + ')')[0];
                    dojo.place(this.getProgressTokenDivHtml(progressToken.id), container);
                }
            },

            getNewProgressTokenContainer: function(playerId) {
                let tokensContainer = dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens')[0];
                let outlineNodes = dojo.query('.progress_token_outline:empty', tokensContainer);
                if (outlineNodes[0]) {
                    return outlineNodes[0];
                }
                else {
                    return dojo.place(this.format_block('jstpl_progress_token_outline', {}), tokensContainer);
                }
            },

            getProgressTokenDivHtml: function (progressTokenId) {
                var progressToken = this.gamedatas.progressTokens[progressTokenId];
                var data = {
                    jsId: progressTokenId,
                    jsName: _(progressToken.name),
                    jsData: 'data-progress-token-id="' + progressTokenId + '"',
                };
                var spritesheetColumns = 4;
                data.jsX = (progressTokenId - 1) % spritesheetColumns;
                data.jsY = Math.floor((progressTokenId - 1) / spritesheetColumns);
                return this.format_block('jstpl_progress_token', data);
            },

            updatePlayerProgressTokens: function (playerId, deckCards) {
                if (this.debug) console.log('updatePlayerProgressTokens', playerId, deckCards);

                let tokensContainer = dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens')[0];
                Object.keys(deckCards).forEach(dojo.hitch(this, function (index) {
                    var deckCard = deckCards[index];
                    let node = dojo.query('div[data-progress-token-id=' + deckCard.id + ']', tokensContainer)[0];
                    if (!node) {
                        let result = dojo.place(this.getProgressTokenDivHtml(deckCard.id), this.getNewProgressTokenContainer(playerId));
                    }
                }));
            },

            //  ____  _       _       _ _   _
            // |  _ \(_)_   _(_)_ __ (_) |_(_) ___  ___
            // | | | | \ \ / / | '_ \| | __| |/ _ \/ __|
            // | |_| | |\ V /| | | | | | |_| |  __/\__ \
            // |____/|_| \_/ |_|_| |_|_|\__|_|\___||___/

            updateDivinitiesSituation: function (divinitiesSituation) {
                if (this.debug) console.log('updateDivinitiesSituation: ', divinitiesSituation);
                this.gamedatas.divinitiesSituation = divinitiesSituation;

                // Pantheon spaces
                let tokensContainer = dojo.query('.pantheon_space_containers')[0];
                let costContainer = dojo.query('.pantheon_cost_containers')[0];
                Object.keys(divinitiesSituation.spaces).forEach(dojo.hitch(this, function (space) {
                    let spaceData = divinitiesSituation.spaces[space];
                    let emptySpace = dojo.query('div[data-space=' + space + ']:empty', tokensContainer)[0];
                    if (emptySpace) {
                        let result = dojo.place(this.getDivinityDivHtml(spaceData.id, spaceData.type, false), emptySpace);
                    }
                    let divinityNode = dojo.query('div[data-space=' + space + '] .divinity', tokensContainer)[0];

                    for (var playerId in this.gamedatas.players) {
                        if (playerId == this.player_id) {
                            dojo.attr(divinityNode, 'data-activatable', spaceData.activatable[playerId]);
                        }
                        if (spaceData.payment) {
                            let alias = this.getPlayerAlias(playerId);
                            let container = dojo.query('div[data-space=' + space + '] .' + alias + ' .cost', costContainer)[0];
                            let oldNode = dojo.query('.coin', container)[0];
                            let playerCoins = this.gamedatas.playersSituation[playerId].coins;
                            let newNode = dojo.place( this.getCostDivHtml(spaceData.cost[playerId], playerCoins), container, 'first');
                            if (oldNode && newNode) {
                                this.twistAnimation(oldNode, newNode);
                            }
                            else {
                                // This fadein doesn't work.. why?
                                dojo.style(newNode, 'opacity', 0);
                                let anim = dojo.fadeIn({node: newNode, duration: 0.5 });
                                anim.play();
                            }
                        }
                    }
                }));

                // Deck counts
                Object.keys(divinitiesSituation.deckCounts).forEach(dojo.hitch(this, function (type) {
                    let span = dojo.query('#mythology_decks_container #mythology' + type + ' .divinity_deck_count')[0];
                    span.innerHTML = divinitiesSituation.deckCounts[type];
                }));

                // Player divinities
                for (var playerId in this.gamedatas.players) {
                    for (let i = 0; i < divinitiesSituation[playerId].length; i++) {
                        let divinityData = divinitiesSituation[playerId][i];
                        console.log('divinityData', divinityData);
                        let container = $('player_conspiracies_' + playerId);
                        console.log('container', container);
                        let divinityNode = dojo.query('div[data-divinity-id="' + divinityData.id + '"]', container)[0];
                        console.log('divinityNode', divinityNode);
                        if (!divinityNode) {
                            let divinityType = this.gamedatas.divinities[divinityData.id].type;
                            console.log('divinityType', divinityType);
                            let newNode = dojo.place( this.getDivinityDivHtml(divinityData.id, divinityType, false), container, 'first');
                            console.log('newNode', newNode);
                        }
                    }
                }
            },

            divinitiesRevealAnimation: function(doorSpace, divinitiesSituation) {
                let movementDuration = 650;
                let delay = 0;
                for (let space = 1; space <= 6; space++) {
                    if (space != doorSpace) {
                        let spaceNode = dojo.query('.pantheon_space_containers div[data-space=' + space + ']')[0];
                        let oldNode = dojo.query('.divinity_container', spaceNode)[0];
                        let divinityData = divinitiesSituation.spaces[space];
                        let newNode = dojo.place(this.getDivinityDivHtml(divinityData.id, divinityData.type, false), spaceNode);
                        this.twistAnimation(oldNode, newNode, true, delay, movementDuration);
                        delay += movementDuration * 0.75;
                    }
                }
                // delay += this.turnAroundCardDuration;

                let spaceNode = dojo.query('.pantheon_space_containers div[data-space=' + doorSpace + ']')[0];
                let gateContainerNode = dojo.place(this.getDivinityDivHtml(16, 6, false), spaceNode);
                let gateNode = dojo.query('.divinity', gateContainerNode)[0];
                dojo.style(gateNode, 'opacity', 0);

                let gateAnim = dojo.animateProperty({ // Standard fadeOut started of at opacity 0 (?!?)
                    node: gateNode,
                    duration: movementDuration,
                    delay: delay,
                    // easing: dojo.fx.easing.linear,
                    properties: {
                        opacity: {
                            start: 0,
                            end: 1
                        },
                        top: {
                            start: -50,
                            end: 0
                        }
                    },
                    onAnimate: function (values) {
                        dojo.style(gateNode, 'top', 'calc(var(--scale) * ' + values.top + ')');
                    },
                });
                gateAnim.play();
                delay += movementDuration;

                return delay;
            },

            getDivinityDivHtml: function (divinityId, divinityType, full=false) {
                let hidden = divinityId == 0;
                let spriteId = hidden ? (16 + divinityType) : divinityId;
                var divinity = this.gamedatas.divinities[divinityId];
                var data = {
                    jsId: divinityId,
                    jsType: divinityType,
                    jsName: hidden ? '' : _(divinity.name),
                    jsDivinityBack: hidden ? 'divinity_back' : '',
                    jsYOffset: hidden ? '.6562' : '.7012',
                };
                var spritesheetColumns = 6;
                data.jsX = (spriteId - 1) % spritesheetColumns;
                data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);
                return this.format_block(full ? 'jstpl_divinity_full' : 'jstpl_divinity', data);
            },

            getCostDivHtml: function (cost, playerCoins) {
                var data = {
                    jsCost: this.getCostValue(cost),
                    jsCostColor: this.getCostColor(cost, playerCoins),
                };
                data.jsCostClass = isNaN(data.jsCost) ? 'cost_free' : '';
                return this.format_block('jstpl_cost', data);
            },

            //  __  __       _   _           _                     _____     _
            // |  \/  |_   _| |_| |__   ___ | | ___   __ _ _   _  |_   _|__ | | _____ _ __  ___
            // | |\/| | | | | __| '_ \ / _ \| |/ _ \ / _` | | | |   | |/ _ \| |/ / _ \ '_ \/ __|
            // | |  | | |_| | |_| | | | (_) | | (_) | (_| | |_| |   | | (_) |   <  __/ | | \__ \
            // |_|  |_|\__, |\__|_| |_|\___/|_|\___/ \__, |\__, |   |_|\___/|_|\_\___|_| |_|___/
            //         |___/                         |___/ |___/

            getMythologyTokenHtml: function (id, type, full=false) {
                var data = {
                    jsId: id,
                    jsType: type,
                };
                return this.format_block('jstpl_mythology_token', data);
            },

            updateMythologyTokensSituation: function (situation) {
                if (this.debug) console.log('updateMythologyTokensSituation', situation);
                this.gamedatas.mythologyTokensSituation = situation;

                for (var player_id in this.gamedatas.players) {
                    this.updatePlayerMythologyTokens(player_id, situation[player_id]);
                }
            },

            updatePlayerMythologyTokens: function (playerId, deckCards) {
                if (this.debug) console.log('updatePlayerMythologyTokens', playerId, deckCards);

                let tokensContainer = dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens')[0];
                Object.keys(deckCards).forEach(dojo.hitch(this, function (index) {
                    let deckCard = deckCards[index];
                    let nodeExists = dojo.query('.mythology_token[data-mythology-token-id=' + deckCard.id + ']', tokensContainer)[0];
                    if (!nodeExists) {
                        // Destroy first outline node if it exists
                        dojo.destroy(dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens .mythology_token_outline:empty')[0]);

                        dojo.place(this.getMythologyTokenHtml(deckCard.id, deckCard.type), tokensContainer);
                    }
                }));
            },

            //   ___   __  __           _               _____     _
            //  / _ \ / _|/ _| ___ _ __(_)_ __   __ _  |_   _|__ | | _____ _ __  ___
            // | | | | |_| |_ / _ \ '__| | '_ \ / _` |   | |/ _ \| |/ / _ \ '_ \/ __|
            // | |_| |  _|  _|  __/ |  | | | | | (_| |   | | (_) |   <  __/ | | \__ \
            //  \___/|_| |_|  \___|_|  |_|_| |_|\__, |   |_|\___/|_|\_\___|_| |_|___/
            //                                  |___/

            getOfferingTokenHtml: function (id, full=false) {
                var data = {
                    jsId: id
                };
                return this.format_block('jstpl_offering_token', data);
            },

            updateOfferingTokensSituation: function (situation) {
                if (this.debug) console.log('updateOfferingTokensSituation', situation);
                this.gamedatas.offeringTokensSituation = situation;

                for (var player_id in this.gamedatas.players) {
                    this.updatePlayerOfferingTokens(player_id, situation[player_id]);
                }
            },

            updatePlayerOfferingTokens: function (playerId, deckCards) {
                if (this.debug) console.log('updatePlayerOfferingTokens', playerId, deckCards);

                let tokensContainer = dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens')[0];
                Object.keys(deckCards).forEach(dojo.hitch(this, function (index) {
                    let deckCard = deckCards[index];
                    let nodeExists = dojo.query('.offering_token[data-offering-token-id=' + deckCard.id + ']', tokensContainer)[0];
                    if (!nodeExists) {
                        // Destroy first outline node if it exists
                        dojo.destroy(dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens .offering_token_outline:empty')[0]);

                        dojo.place(this.getOfferingTokenHtml(deckCard.id, deckCard.type), tokensContainer);
                    }
                }));
            },

            //  ____                   _
            // / ___|  ___ _ __   __ _| |_ ___
            // \___ \ / _ \ '_ \ / _` | __/ _ \
            //  ___) |  __/ | | | (_| | ||  __/
            // |____/ \___|_| |_|\__,_|\__\___|
            //

            updateSenateSituation: function (senateSituation) {
                if (this.debug) console.log('updateSenateSituation: ', senateSituation);
                this.gamedatas.senateSituation = senateSituation;

                Object.keys(senateSituation.chambers).forEach(dojo.hitch(this, function (chamber) {
                    let chamberData = senateSituation.chambers[chamber];

                    this.updateSenateChamber(chamber, chamberData)
                }));
            },

            getCubeDivHtml: function (count, playerId, controllerId) {
                let conrollerClass = '';
                var data = {
                    jsCount: count,
                    jsPlayerAlias: (playerId == this.me_id ? 'me' : 'opponent'),
                    jsControllerClass: (playerId == controllerId ? 'agora_control' : ''),
                };
                return this.format_block('jstpl_cube', data);
            },

            //  ____
            // |  _ \  ___  ___ _ __ ___  ___  ___
            // | | | |/ _ \/ __| '__/ _ \/ _ \/ __|
            // | |_| |  __/ (__| | |  __/  __/\__ \
            // |____/ \___|\___|_|  \___|\___||___/

            updateDecreesSituation: function (decreesSituation) {
                if (this.debug) console.log('updateDecreesSituation: ', decreesSituation);
                this.gamedatas.decreesSituation = decreesSituation;

                dojo.query(".decree_containers>div>div").forEach(dojo.empty);

                for (var i = 0; i < decreesSituation.length; i++) {
                    var location = decreesSituation[i];
                    this.placeDecree(location.id, decreesSituation[i].location_arg);
                }
            },

            getDecreeNode: function(combinedLocation) {
                var position = parseInt(combinedLocation.charAt(0));
                var stackPosition = parseInt(combinedLocation.charAt(1));
                return dojo.query('.decree_containers>div:nth-of-type(' + position + ')>div:nth-of-type(' + stackPosition + ') .decree')[0];
            },

            placeDecree: function(id, combinedLocation) {
                var position = parseInt(combinedLocation.charAt(0));
                var stackPosition = parseInt(combinedLocation.charAt(1));
                var container = dojo.query('.decree_containers>div:nth-of-type(' + position + ')>div:nth-of-type(' + stackPosition + ')')[0];
                return dojo.place(this.getDecreeDivHtml(id), container);
            },

            getDecreeDivHtml: function (decreeId) {
                var decree = this.gamedatas.decrees[decreeId];
                var data = {
                    jsId: decreeId,
                    jsName: decree ? _(decree.name) : '',
                };
                var spritesheetColumns = 6;
                data.jsX = (decreeId - 1) % spritesheetColumns;
                data.jsY = Math.floor((decreeId - 1) / spritesheetColumns);
                return this.format_block('jstpl_decree', data);
            },

            notif_decreeControlChanged: function (notif) {
                if (this.debug) console.log('notif_decreeControlChanged', notif);

                if (notif.args.payment) {
                    // Military Track animation (pawn movement, token handling)
                    let anim = bgagame.MilitaryTrackAnimator.get().getAnimation(notif.args.playerId, notif.args.payment, "agora");

                    // Wait for animation before handling the next notification (= state change).
                    this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                    anim.play();
                }
                else {
                    this.notifqueue.setSynchronousDuration(0);
                }
            },


            //   ____                      _                _
            //  / ___|___  _ __  ___ _ __ (_)_ __ __ _  ___(_) ___  ___
            // | |   / _ \| '_ \/ __| '_ \| | '__/ _` |/ __| |/ _ \/ __|
            // | |__| (_) | | | \__ \ |_) | | | | (_| | (__| |  __/\__ \
            //  \____\___/|_| |_|___/ .__/|_|_|  \__,_|\___|_|\___||___/
            //                      |_|

            getConspiracyDivHtml: function (conspiracyId, spriteId, full=false, position=-1, prepared=0, triggered=0, useful=0, progressToken=0) {
                var conspiracy = this.gamedatas.conspiracies[conspiracyId];
                var data = {
                    jsId: conspiracyId,
                    jsName: spriteId <= 16 ? _(conspiracy.name) : '',
                    jsPosition: position,
                    jsPrepared: prepared ? 1 : 0, // Make sure this is 0/1, not the age of the age card used to prepare
                    jsTriggered: triggered,
                    jsUseful: useful,
                };
                var spritesheetColumns = 6;
                data.jsX = (spriteId - 1) % spritesheetColumns;
                data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);
                data.jsPeekX = (conspiracyId - 1) % spritesheetColumns;
                data.jsPeekY = Math.floor((conspiracyId - 1) / spritesheetColumns);
                data.jsPeekDisplay = conspiracyId <= 16 && spriteId > 16 ? 'inline-block' : 'none';
                data.jsTrigger = _('Trigger');
                data.jsPrepare = _('Prepare');
                data.jsProgressToken = progressToken;
                return this.format_block(full ? 'jstpl_conspiracy_full' : 'jstpl_conspiracy', data);
            },

            updateConspiracyDeckCount: function (count) {
                $('conspiracy_deck_count').innerHTML = count;
            },

            updateConspiraciesSituation: function (situation) {
                this.gamedatas.conspiraciesSituation = situation;
                this.updateConspiracyDeckCount(situation.deckCount);
                for (var player_id in this.gamedatas.players) {
                    this.updatePlayerConspiracies(player_id, situation[player_id]);
                }
            },

            updatePlayerConspiracies: function (playerId, rows) {
                if (this.debug) console.log('updatePlayerConspiracies', playerId, rows);

                let container = $('player_conspiracies_' + playerId);
                dojo.query(".conspiracy_container", container).forEach(dojo.destroy);

                Object.keys(rows).forEach(dojo.hitch(this, function (index) {
                    var row = rows[index];
                    let id = row.conspiracy;
                    if (!row.triggered && playerId == this.me_id && this.myConspiracies[row.position]) {
                        id = this.myConspiracies[row.position].id;
                    }
                    let newNode = dojo.place(this.getConspiracyDivHtml(id, row.triggered ? row.conspiracy : 18, false, row.position, row.prepared, row.triggered, row.useful, row.progressToken), container);

                    if (row.prepared > 0) {
                        var data = {
                            jsX: row.ageCardSpriteXY[0],
                            jsY: row.ageCardSpriteXY[1]
                        };
                        dojo.place(this.format_block('jstpl_wonder_age_card', data), dojo.query('.age_card_container', newNode)[0]);
                    }
                }));
            },

            //   __  __ _ _ _ _                     _____               _
            //  |  \/  (_) (_) |_ __ _ _ __ _   _  |_   _| __ __ _  ___| | __
            //  | |\/| | | | | __/ _` | '__| | | |   | || '__/ _` |/ __| |/ /
            //  | |  | | | | | || (_| | |  | |_| |   | || | | (_| | (__|   <
            //  |_|  |_|_|_|_|\__\__,_|_|   \__, |   |_||_|  \__,_|\___|_|\_\
            //                              |___/

            invertMilitaryTrack: function () {
                return this.me_id == this.gamedatas.startPlayerId;
            },

            updateMilitaryTrack: function (militaryTrack) {
                this.gamedatas.militaryTrack = militaryTrack;

                for (var i = 1; i <= 4; i++) {
                    var value = militaryTrack.tokens[i];
                    var frontEndNumber = this.invertMilitaryTrack() ? (5 - i) : i;
                    var tokenContainer = dojo.query('#military_tokens>div:nth-of-type(' + frontEndNumber + ')')[0];
                    if (tokenContainer.children.length == 0 && value > 0) {
                        var newToken = dojo.place(this.format_block('jstpl_military_token', {jsValue: value}), tokenContainer);
                    }
                }

                this.setCssVariable('--conflict-pawn-position', this.me_id == this.gamedatas.startPlayerId ? -militaryTrack.conflictPawn : militaryTrack.conflictPawn);
            },

            //   _____           _ _   _
            //  |_   _|__   ___ | | |_(_)_ __  ___
            //    | |/ _ \ / _ \| | __| | '_ \/ __|
            //    | | (_) | (_) | | |_| | |_) \__ \
            //    |_|\___/ \___/|_|\__|_| .__/|___/
            //                          |_|

            setupTooltips: function () {
                // Simple tooltips

                if (this.agora) {
                    this.addTooltipToClass('military_token_2',
                        _('Military token') + ': ' + _('place 1 of your Influence cubes in a Chamber of your choice'), '', this.toolTipDelay);
                    this.addTooltipToClass('military_token_5',
                        _('Military token') + ': ' + '<ul><li>' + _('Remove 1 of your opponent\'s Influence cubes of your choice from the Senate') + '</li><li style="list-style: none">' + _('and') + '</li><li>' + _('You can move 1 of your Influence cubes to an adjacent Chamber') + '</li></ul>', '', this.toolTipDelay);
                }
                else {
                    let militaryTokenText = _('Military token') + ': ' + _('the opponent of the active player discards ${x} coins');
                    this.addTooltipToClass('military_token_2',
                        dojo.string.substitute(
                            militaryTokenText, {
                                x: 2
                            }), '', this.toolTipDelay);
                    this.addTooltipToClass('military_token_5',
                        dojo.string.substitute(
                            militaryTokenText, {
                                x: 5
                            }), '', this.toolTipDelay);
                }

                this.addTooltip('conflict_pawn',
                    _('Conflict pawn: when it enters a zone, active player applies the effect of the corresponding token, then returns it to the box'), ''
                );

                this.addTooltip('capital_me',
                    _('Your capital: if the Conflict pawn reaches this space your opponent immediately wins the game (Military Supremacy)'), ''
                );
                this.addTooltip('capital_opponent',
                    _('Opponent\'s capital: if the Conflict pawn reaches this space you immediately win the game (Military Supremacy)'), ''
                );

                this.addTooltipToClass('expansion_icon_container_agora',
                    _('Column for Senators (Agora expansion)'), '', this.toolTipDelay);

                this.addTooltipToClass('expansion_icon_container_pantheon',
                    _('Column for Grand Temples (Pantheon expansion)'), '', this.toolTipDelay);

                this.addTooltipToClass('science_progress',
                    _('If you gather 6 different scientific symbols, you immediately win the game (Scientific Supremacy)'), '', this.toolTipDelay);

                this.addTooltip('buttonPrepareConspiracy',
                    _('After preparing a Conspiracy with an Age card, it can be triggered at the start of a following turn, before playing an Age card.'), ''
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '#senate_actions_tooltip',
                        showDelay: this.toolTipDelay,
                        label: this.getTextHtml([
                                [_('When you recruit a Politician (white cards):'), false],
                            ].concat(this.gamedatas.buildings[75].text)
                        )
                    })
                );

                // this.addTooltipToClass( 'draftpool_building_cost.me', _('Current cost for you to construct the building'), '', this.toolTipDelay );
                // this.addTooltipToClass( 'draftpool_building_cost.opponent', _('Current cost for your opponent to construct the building'), '', this.toolTipDelay );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '.player_info .point',
                        showDelay: this.toolTipDelay,
                        label: _('Victory points')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '.player_info .coin',
                        showDelay: this.toolTipDelay,
                        label: _('Coins')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '.player_info .player_area_cubes>div',
                        showDelay: this.toolTipDelay,
                        label: _('Influence cubes')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '#swd[data-client-state="client_moveInfluenceTo"] #senate_chambers .gray_stroke',
                        showDelay: this.toolTipDelay,
                        label: _('Cancel selection')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '#swd[data-client-state="client_moveInfluenceFrom"] #senate_chambers .red_stroke',
                        showDelay: this.toolTipDelay,
                        label: _('From this Chamber')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '#swd[data-client-state="client_moveInfluenceTo"] #senate_chambers .red_stroke',
                        showDelay: this.toolTipDelay,
                        label: _('To this Chamber')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '#swd[data-client-state="client_moveDecreeTo"] .decree_containers .gray_border',
                        showDelay: this.toolTipDelay,
                        label: _('Cancel selection')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '#swd[data-client-state="client_moveDecreeTo"] #senate_chambers .red_stroke',
                        showDelay: this.toolTipDelay,
                        label: _('Move Decree token to this Chamber')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '.player_wonders.me .coin',
                        showDelay: this.toolTipDelay,
                        label: _('Current construction cost for you')
                    })
                );

                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '.player_wonders.opponent .coin',
                        showDelay: this.toolTipDelay,
                        label: _('Current construction cost for your opponent')
                    })
                );

                // Add tooltips to building cost.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '.draftpool_building_cost.me .coin',
                        showDelay: this.toolTipDelay,
                        label: _('Current construction cost for you')
                    })
                );
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "swd",
                        selector: '.draftpool_building_cost.opponent .coin',
                        showDelay: this.toolTipDelay,
                        label: _('Current construction cost for your opponent')
                    })
                );

                // Add tooltips to buildings everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '#swd:not(.nextAge) div[data-building-id]', // Not during nextAge animation
                        position: ["above-centered", "below-centered", "after-centered", "before-centered"],
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-building-id");
                            var draftpoolBuilding = dojo.query(node).closest("#draftpool")[0];
                            var meCoinHtml;
                            var opponentCoinHtml;
                            var meIconHtml;
                            var opponentIconHtml;
                            var linkedBuilding;
                            if (draftpoolBuilding) {
                                meCoinHtml = dojo.query('.me .coin', node)[0].outerHTML;
                                linkedBuilding = dojo.query('.me .linked_building_icon', node)[0];
                                if (linkedBuilding) {
                                    meIconHtml = linkedBuilding.outerHTML;
                                }

                                opponentCoinHtml = dojo.query('.opponent .coin', node)[0].outerHTML;
                                linkedBuilding = dojo.query('.opponent .linked_building_icon', node)[0];
                                if (linkedBuilding) {
                                    opponentIconHtml = linkedBuilding.outerHTML;
                                }
                            }
                            return this.getBuildingTooltip(id, draftpoolBuilding, meCoinHtml, opponentCoinHtml, meIconHtml, opponentIconHtml);
                        })
                    })
                );

                // Add tooltips to wonders everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '.wonder_small',
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-wonder-id");

                            var playerId = undefined;
                            var coinHtml = undefined;
                            var playerWondersNode = dojo.query(node).closest(".player_wonders")[0];
                            if (playerWondersNode) {
                                playerId = dojo.hasClass(playerWondersNode, 'me') ? this.me_id : this.opponent_id;
                                coinHtml = dojo.query('.coin', node)[0].outerHTML;
                            }
                            return this.getWonderTooltip(id, playerId, coinHtml);
                        })
                    })
                );

                // Add tooltips to progress tokens everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '.progress_token_small',
                        position: ['before', "above-centered", "below-centered", "after-centered", "before-centered"],
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-progress-token-id");
                            return this.getProgressTokenTooltip(id);
                        })
                    })
                );

                // Add tooltips to conspiracies everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: 'div[data-divinity-id]',
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-divinity-id");
                            var type = dojo.attr(node, "data-divinity-type");
                            return this.getDivinityTooltip(id, type, node);
                        })
                    })
                );

                // Add tooltips to decrees everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '.decree_small',
                        position: ['before'],
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-decree-id");
                            return this.getDecreeTooltip(id);
                        })
                    })
                );

                // Add tooltips to conspiracies everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: 'div[data-conspiracy-id]',
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-conspiracy-id");
                            return this.getConspiracyTooltip(id, node);
                        })
                    })
                );

                // Add tooltips to Mythology tokens everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: 'div[data-mythology-token-id]',
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-mythology-token-id");
                            return this.getMythologyTokenTooltip(id, node);
                        })
                    })
                );

                // Add tooltips to Offering tokens everywhere.
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: 'div[data-offering-token-id]',
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-offering-token-id");
                            return this.getOfferingTokenTooltip(id, node);
                        })
                    })
                );

                // Add tooltips to Senate Chambers
                this.customTooltips.push(
                    new dijit.Tooltip({
                        connectId: "game_play_area",
                        selector: '#senate_chambers>path[data-stroke=""]',
                        position: ['before'],
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var chamber = parseInt(dojo.attr(node, "data-chamber"));
                            return this.getSenateChamberTooltip(chamber);
                        })
                    })
                );



                // Mimick BGA's default behavior of closing the tooltip over mouseover and click.
                dojo.query('body').on("#dijit__MasterTooltip_0:mouseover", dojo.hitch(this, "closeTooltips"));
                dojo.query('body').on("#dijit__MasterTooltip_0:click", dojo.hitch(this, "closeTooltips"));
            },

            closeTooltips: function () {
                Object.keys(this.customTooltips).forEach(dojo.hitch(this, function (index) {
                    this.customTooltips[index].close();
                }));
            },

            getBuildingTooltip: function (id, draftpoolBuilding, meCoinHtml, opponentCoinHtml, meIconHtml, opponentIconHtml) {
                if (typeof this.gamedatas.buildings[id] != 'undefined') {
                    var building = this.gamedatas.buildings[id];

                    var spritesheetColumns = 12;

                    var data = {};
                    if (building.age <= 3) {
                        data.cardType = dojo.string.substitute(_("Age ${ageRoman} card"), {
                            ageRoman: this.ageRoman(building.age)
                        });
                    } else if(building.age == 4) {
                        data.cardType = _("Guild card");
                    } else if(building.age == 5) {
                        data.cardType = ""; // Senator, but that's already the typeDescription, so leave empty.
                    }

                    if (data.cardType != "") {
                        data.jsName = '“' + _(building.name) + '”';
                    }
                    else {
                        data.jsName = _(building.name);
                    }
                    data.jsNameOnCard = _(building.name);

                    data.jsType = _(building.type);
                    data.jsBuildingTypeColor = building.typeColor;
                    data.jsBuildingTypeDescription = _(building.typeDescription);
                    data.jsText = this.getTextHtml(building.text);
                    data.jsX = building.spriteXY[0];
                    data.jsY = building.spriteXY[1];
                    data.jsCostMe = '';
                    data.jsCostOpponent = '';
                    if (draftpoolBuilding) {
                        var position = this.getDraftpoolCardData(id);
                        if (position.payment) {
                            data.jsCostMe = this.format_block('jstpl_tooltip_cost_me', {
                                translateCurrentCost: _("Current construction cost for you"),
                                translateTotal: _("Total"),
                                jsCoinHtml: meCoinHtml,
                                jsPayment: this.getPaymentPlan(position.payment[this.me_id], meIconHtml)
                            });

                            data.jsCostOpponent = this.format_block('jstpl_tooltip_cost_opponent', {
                                translateCurrentCost: _("Current construction cost for opponent"),
                                translateTotal: _("Total"),
                                jsCoinHtml: opponentCoinHtml,
                                jsPayment: this.getPaymentPlan(position.payment[this.opponent_id], opponentIconHtml)
                            });
                        }
                    }

                    return this.format_block('jstpl_building_tooltip', data);
                }
                return false;
            },

            getPaymentPlan: function (data, linkedIcon) {
                var output = '';
                var steps = data.steps;
                for (var i = 0; i < steps.length; i++) {
                    if (steps[i].resource == "linked") {
                        output += linkedIcon.replace('linked_building_icon_small', '');
                    } else {
                        output += this.getResourceIcon(steps[i].resource, steps[i].amount);
                    }
                    output += ' &rightarrow; ';
                    let args = steps[i].args;
                    Object.keys(args).forEach(dojo.hitch(this, function (key) {
                        args[key] = _(args[key]);
                    }));
                    args.costIcon = steps[i].cost ? this.getResourceIcon('coin', steps[i].cost) : '';
                    if (Array.isArray(steps[i].string)) {
                        for (let j = 0; j < steps[i].string.length; j++) {
                            output += dojo.string.substitute(_(steps[i].string[j]), args) + " ";
                        }
                    }
                    else {
                        output += dojo.string.substitute(_(steps[i].string), args);
                    }
                    output += '<br/>';
                }
                return output;
            },

            getTextWithArgs: function(text, args) {
                if (!args) args = {};
                Object.keys(args).forEach(dojo.hitch(this, function (key) {
                    args[key] = _(args[key]);
                }));
                return dojo.string.substitute(_(text), args);
            },

            getTextHtml: function (text) {
                if (text instanceof Array) {
                    if (text.length == 0) return '';
                    else if (text.length == 1) {
                        return this.getTextWithArgs(text[0][0], text[0][2]);
                    }
                    else {
                        var string = '';
                        for (let i = 0; i < text.length; i++) {
                            // Replace/translate arguments in text.
                            let output = this.getTextWithArgs(text[i][0], text[i][2]);
                            string += "<li " + (text[i][1] ? '' : 'class="no_li"') + ">" + output + "</li>";
                        }
                        return "<ul>" + string + "</ul>";
                    }
                } else {
                    return _(text);
                }
            },

            getWonderTooltip: function (id, playerId, coinHtml) {
                if (typeof this.gamedatas.wonders[id] != 'undefined') {
                    var wonder = this.gamedatas.wonders[id];

                    var spritesheetColumns = 5;

                    var data = {};
                    data.translateWonder = _("Wonder");
                    data.jsName = _(wonder.name);
                    data.jsText = this.getTextHtml(wonder.text);

                    data.jsBackX = ((id - 1) % spritesheetColumns);
                    data.jsBackY = Math.floor((id - 1) / spritesheetColumns);
                    data.jsCost = '';
                    if (playerId) {
                        var cardData = this.getWonderCardData(playerId, id);
                        if (!cardData) return false; // Happens in Edge sometimes.
                        if (!cardData.constructed) {
                            data.jsCost = this.format_block(playerId == this.me_id ? 'jstpl_tooltip_cost_me' : 'jstpl_tooltip_cost_opponent', {
                                translateCurrentCost: playerId == this.me_id ? _("Current construction cost for you") : _("Current construction cost for opponent"),
                                translateTotal: _("Total"),
                                jsCoinHtml: coinHtml,
                                jsPayment: this.getPaymentPlan(cardData.payment)
                            });
                        }
                    }

                    return this.format_block('jstpl_wonder_tooltip', data);
                }
                return false;
            },

            getProgressTokenTooltip: function (id) {
                if (typeof this.gamedatas.progressTokens[id] != 'undefined') {
                    var progressToken = this.gamedatas.progressTokens[id];

                    var spritesheetColumns = 4;

                    var data = {};
                    data.translateProgressToken = _("Progress token");
                    data.jsName = _(progressToken.name);
                    data.jsText = this.getTextHtml(progressToken.text);
                    data.jsBackX = ((id - 1) % spritesheetColumns);
                    data.jsBackY = Math.floor((id - 1) / spritesheetColumns);
                    return this.format_block('jstpl_progress_token_tooltip', data);
                }
                return false;
            },

            getDivinityTooltip: function (id, type, node) {
                let hidden = id == 0;
                let spriteId = hidden ? (16 + type) : id;

                var divinity = this.gamedatas.divinities[id];

                var spritesheetColumns = 6;

                var data = {};
                data.translateDivinity = hidden ? _("Face down Divinity") : _("Divinity");
                data.jsName = hidden ? '' : '“' + _(divinity.name) + '”';
                data.jsNameOnCard = hidden ? '' : _(divinity.name);
                data.jsDivinityType = _(this.gamedatas.divinityTypeNames[type]);
                data.jsDivinityColor = this.gamedatas.divinityTypeColors[type];

                data.jsText = this.getTextHtml(divinity.text);
                data.jsBackX = ((spriteId - 1) % spritesheetColumns);
                data.jsBackY = Math.floor((spriteId - 1) / spritesheetColumns);

                data.jsCostMe = '';
                data.jsCostOpponent = '';
                var spaceNode = dojo.query(node).closest(".pantheon_space")[0];
                if (spaceNode) {
                    let space = dojo.attr(spaceNode, 'data-space');
                    if (this.gamedatas.divinitiesSituation.spaces[space].payment) {
                        meCoinHtml = dojo.query('.pantheon_cost_containers div[data-space=' + space + '] .me .coin')[0].outerHTML;
                        data.jsCostMe = this.format_block('jstpl_tooltip_cost_me', {
                            translateCurrentCost: _("Current activation cost for you"),
                            translateTotal: _("Total"),
                            jsCoinHtml: meCoinHtml,
                            jsPayment: this.getPaymentPlan(this.gamedatas.divinitiesSituation.spaces[space].payment[this.me_id])
                        });

                        opponentCoinHtml = dojo.query('.pantheon_cost_containers div[data-space=' + space + '] .opponent .coin')[0].outerHTML;
                        data.jsCostOpponent = this.format_block('jstpl_tooltip_cost_opponent', {
                            translateCurrentCost: _("Current activation cost for opponent"),
                            translateTotal: _("Total"),
                            jsCoinHtml: opponentCoinHtml,
                            jsPayment: this.getPaymentPlan(this.gamedatas.divinitiesSituation.spaces[space].payment[this.opponent_id])
                        });
                    }
                }
                return this.format_block('jstpl_divinity_tooltip', data);
            },

            getDecreeTooltip: function (id) {
                var decree = this.gamedatas.decrees[id];

                var spritesheetColumns = 6;

                let hidden = id == 17;

                var data = {};
                data.translateDecree = hidden ? _("Face down Decree token") : _("Decree token");
                // data.jsName = _(decree.name);
                data.jsText = this.getTextHtml(decree.text);
                data.jsBackX = ((id - 1) % spritesheetColumns);
                data.jsBackY = Math.floor((id - 1) / spritesheetColumns);
                return this.format_block('jstpl_decree_tooltip', data);
            },

            getMythologyTokenTooltip: function (id) {
                var token = this.gamedatas.mythologyTokens[id];

                var data = {};
                data.jsId = id;
                data.jsType = token.type;
                data.translateToken = _("Mythology token");
                data.jsDivinityType = _(this.gamedatas.divinityTypeNames[token.type]);
                data.jsDivinityColor = this.gamedatas.divinityTypeColors[token.type];
                data.jsText = this.getTextHtml(token.text);
                return this.format_block('jstpl_mythology_token_tooltip', data);
            },

            getOfferingTokenTooltip: function (id) {
                var token = this.gamedatas.offeringTokens[id];

                var data = {};
                data.jsId = id;
                data.jsDiscount = token.discount;
                data.translateToken = _("Offering token");
                data.jsText = this.getTextHtml(token.text);
                return this.format_block('jstpl_offering_token_tooltip', data);
            },

            getConspiracyTooltip: function (id, node) {
                if (id == 18) id = 17;
                let hidden = id == 17;

                var conspiracy = this.gamedatas.conspiracies[id];

                var spritesheetColumns = 6;

                var data = {};
                data.translateConspiracy = node.dataset.conspiracyTriggered == "0" ? _("Face down Conspiracy") : _("Conspiracy");
                data.jsName = hidden ? '' : '“' + _(conspiracy.name) + '”';
                data.jsNameOnCard = hidden ? '' : _(conspiracy.name);

                if(node.dataset.conspiracyTriggered == "1") {
                    data.jsState = ' - ' + _('Triggered');
                }
                else if(node.dataset.conspiracyPrepared == "1") {
                    data.jsState = ' - ' + _('Prepared');
                }
                else if(node.dataset.conspiracyPrepared === "0") {
                    data.jsState = ' - ' + _('Unprepared');
                }
                else {
                    data.jsState = '';
                }
                data.jsText = this.getTextHtml(conspiracy.text);
                data.jsBackX = ((id - 1) % spritesheetColumns);
                data.jsBackY = Math.floor((id - 1) / spritesheetColumns);
                return this.format_block('jstpl_conspiracy_tooltip', data);
            },

            getSenateChamberTooltip: function (chamber) {
                var spritesheetColumns = 6;

                var data = {};
                let section = '';
                let points = 0;
                switch(chamber) {
                    case 1:
                    case 2:
                        section = _('Left section')
                        break;
                    case 3:
                    case 4:
                        section = _('Middle section')
                        break;
                    case 5:
                    case 6:
                        section = _('Right section')
                        break;
                }
                switch(chamber) {
                    case 1:
                    case 6:
                        points = 1;
                        break;
                    case 2:
                    case 5:
                        points = 2;
                        break;
                    case 3:
                    case 4:
                        points = 3;
                        break;
                }
                let text = [];
                text.push([_('This Chamber is worth ${points} victory point(s) if you control it at the end of the game').replace('${points}', points), true]);
                if (chamber %2 == 0) {
                    text.push([_('The Decree token in this Chamber is face down until the first Influence cube is placed in the Chamber'), true]);
                }
                else {
                    text.push([_('The Decree token in this Chamber is visible from the start of the game'), true]);
                }
                data.jsName = _("Senate Chamber") + " " + chamber + ' - ' + section;
                data.jsText = this.getTextHtml(text);
                data.jsBackX = ((chamber - 1) % spritesheetColumns);
                data.jsSection = chamber <= 2 ? 'left' : (chamber <= 4 ? 'center' : 'right');
                return this.format_block('jstpl_senate_chamber_tooltip', data);
            },

            //   ____  _                             ___        __
            //  |  _ \| | __ _ _   _  ___ _ __ ___  |_ _|_ __  / _| ___
            //  | |_) | |/ _` | | | |/ _ \ '__/ __|  | || '_ \| |_ / _ \
            //  |  __/| | (_| | |_| |  __/ |  \__ \  | || | | |  _| (_) |
            //  |_|   |_|\__,_|\__, |\___|_|  |___/ |___|_| |_|_|  \___/
            //                 |___/

            getPlayerCoins: function (playerId) {
                return this.gamedatas.playersSituation[playerId].coins;
            },

            increasePlayerCoins: function (playerId, coins) {
                $('player_area_' + playerId + '_coins').innerHTML = parseInt($('player_area_' + playerId + '_coins').innerHTML) + coins;
            },

            increasePlayerCubes: function (playerId, cubes) {
                $('player_area_' + playerId + '_cubes').innerHTML = parseInt($('player_area_' + playerId + '_cubes').innerHTML) + cubes;
                if (this.debug) console.log('increasePlayerCubes', $('player_area_' + playerId + '_cubes'), cubes);
            },

            updatePlayersSituation: function (situation) {
                if (this.debug) console.log('updatePlayersSituation', situation)
                this.gamedatas.playersSituation = situation;
                for (var playerId in this.gamedatas.players) {
                    $('player_area_' + playerId + '_coins').innerHTML = situation[playerId].coins;
                    $('player_area_' + playerId + '_score').innerHTML = situation[playerId].score;
                    $('player_area_' + playerId + '_cubes').innerHTML = situation[playerId].cubes;
                    if (typeof situation[playerId].winner != "undefined" && this.scoreCtrl[playerId]) {
                        this.scoreCtrl[playerId].setValue(situation[playerId].winner);
                    }
                    var scienceCountNode = dojo.query('.player_buildings.player' + playerId + ' .science_progress')[0];
                    dojo.query('span', scienceCountNode)[0].innerHTML = situation[playerId].scienceSymbolCount + '/6 ' + _('symbols');
                    dojo.style(scienceCountNode, 'display', situation[playerId].scienceSymbolCount ? 'block' : 'none');
                }

                if (typeof situation.endGameCondition != "undefined") {
                    switch (situation.endGameCondition) {
                        case 1:
                            this.scientificSupremacyAnimation(situation);
                            break;
                        case 2:
                            this.militarySupremacyAnimation(situation);
                            break;
                        case 3:
                        case 4:
                        case 5:
                            this.endGameScoringAnimation(situation);
                            break;
                        case 6:
                            this.politicalSupremacyAnimation(situation);
                            break;
                    }
                }
            },

            //  __        __              _                      _           _   _
            //  \ \      / /__  _ __   __| | ___ _ __   ___  ___| | ___  ___| |_(_) ___  _ __
            //   \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__| / __|/ _ \ |/ _ \/ __| __| |/ _ \| '_ \
            //    \ V  V / (_) | | | | (_| |  __/ |    \__ \  __/ |  __/ (__| |_| | (_) | | | |
            //     \_/\_/ \___/|_| |_|\__,_|\___|_|    |___/\___|_|\___|\___|\__|_|\___/|_| |_|


            onEnterSelectWonder: function (args) {
                if (args.updateWonderSelection) {
                    this.callFunctionAfterLoading(dojo.hitch(this, "updateWonderSelection"), [args.wonderSelection])
                }
            },

            callFunctionAfterLoading: function(functionToCall, args = []) {
                var loaderNode = $('loader_mask');
                if (!loaderNode || loaderNode.style.display == 'none') {
                    functionToCall(...args);
                } else {
                    const functionToCallPromise = () => this.callFunctionAfterLoading(functionToCall, args);
                    // Wait till the loader is no longer visible.
                    setTimeout(functionToCallPromise, 50);
                }
            },

            updateWonderSelection: function (cards) {
                var block = $('wonder_selection_block');
                if (cards.length > 0) {
                    var animationDelay = 0;
                    var position = 1;
                    Object.keys(cards).forEach(dojo.hitch(this, function (index) {
                        var card = cards[index];

                        var container = dojo.query('#wonder_selection_container>div:nth-of-type(' + (parseInt(card.location_arg) + 1) + ')')[0];
                        dojo.empty(container);

                        var wonderNode = dojo.place(this.getWonderDivHtml(card.id, false), 'swd'); // Temporary add it to #swd, so card_outline container stay empty and show the outline.

                        if ((typeof g_replayFrom != 'undefined' || g_archive_mode) || !this.checkPossibleActions( "actionSelectWonder" )) {
                            // Don't animate when replaying to prevent TypeError: Cannot read property 'parentElement' of null
                            dojo.place(wonderNode, container);
                        }
                        else {
                            dojo.style(wonderNode, 'display', 'none');
                            dojo.animateProperty({
                                node: wonderNode,
                                delay: animationDelay,
                                duration: this.putDraftpoolCard,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    opacity: {start: 0.0, end: 1.0},
                                    propertyScale: {start: 1.15, end: 1},
                                    propertyTransform: {start: -40, end: 0},
                                },
                                onPlay: function (values) {
                                    dojo.place(wonderNode, container); // This will hide the outline of the container, which is why we do it as late as possible.
                                    dojo.style(wonderNode, 'display', 'inline-block');
                                    dojo.style(wonderNode, 'opacity', 0);
                                },
                                onAnimate: function (values) {
                                    dojo.style(wonderNode, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg) scale(' + parseFloat(values.propertyScale.replace("px", "")) + ')');
                                }
                            }).play();
                            animationDelay += this.putDraftpoolCard * 0.75;
                        }


                        position++;
                    }));
                }
            },

            onWonderSelectionClick: function (e) {
                if (this.debug) console.log('onWonderSelectionClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.isCurrentPlayerActive()) {
                    var wonder = dojo.hasClass(e.target, 'wonder') ? dojo.query(e.target) : dojo.query(e.target).closest(".wonder");

                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionSelectWonder')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionSelectWonder.html", {
                            lock: true,
                            wonderId: wonder.attr('data-wonder-id')
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        });
                }
            },

            notif_wonderSelected: function (notif) {
                if (this.debug) console.log('notif_wonderSelected', notif);

                var wonderContainerNode = $('wonder_' + notif.args.wonderId + '_container');
                if (!wonderContainerNode) {
                    // In case of a replay, it's possible this node doesn't exist yet. In that case keep trying.
                    const functionToCallPromise = () => this.notif_wonderSelected(notif);
                    // Wait till the wonderContainerNode is present.
                    setTimeout(functionToCallPromise, 50);
                    return;
                }

                var selectionContainer = wonderContainerNode.parentElement;
                var wonderNode = $('wonder_' + notif.args.wonderId);
                var targetNode = dojo.query('.player_wonders.player' + notif.args.playerId + '>div:nth-of-type(' + notif.args.playerWonderCount + ')')[0];
                dojo.place(wonderContainerNode, targetNode);

                // Next we slide (while adjusting the scale during the animation) the wonder.
                var startScale = 0.8 * this.getCssVariable('--scale');
                var endScale = 0.58 * this.getCssVariable('--scale');

                wonderNode.style.setProperty('--wonder-small-scale', startScale);
                this.placeOnObject(wonderNode, selectionContainer);

                var anim = dojo.fx.combine([
                    dojo.animateProperty({
                        node: wonderNode,
                        duration: this.selectWonderAnimationDuration,
                        properties: {
                            propertyScale: {start: startScale, end: endScale}
                        },
                        onEnd: function () {
                            wonderNode.style.removeProperty('--wonder-small-scale');
                        },
                        onAnimate: function (values) {
                            wonderNode.style.setProperty('--wonder-small-scale', parseFloat(values.propertyScale.replace("px", "")));
                        }
                    }),
                    this.slideToObjectPos(wonderNode, targetNode, 0, 0, this.selectWonderAnimationDuration),
                ]);
                anim.play();

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);
            },

            //   ____  _                         _____
            //  |  _ \| | __ _ _   _  ___ _ __  |_   _|   _ _ __ _ __
            //  | |_) | |/ _` | | | |/ _ \ '__|   | || | | | '__| '_ \
            //  |  __/| | (_| | |_| |  __/ |      | || |_| | |  | | | |
            //  |_|   |_|\__,_|\__, |\___|_|      |_| \__,_|_|  |_| |_|
            //                 |___/

            onEnterPlayerTurn: function (args) {
                if (this.debug) console.log('in onEnterPlayerTurn', args);

                let activatable = 0;
                let nodes = dojo.query('.pantheon_space_containers .divinity[data-activatable=1]');
                if (this.isCurrentPlayerActive()) {
                    nodes.addClass('divinity_border');
                    activatable = nodes.length;
                }

                if (this.pantheon && this.currentAge >= 2) {
                    Object.keys(this.gamedatas.divinitiesSituation.spaces).forEach(dojo.hitch(this, function (space) {
                        if (this.gamedatas.divinitiesSituation.spaces[space]) {
                            activatable += 1;
                        }
                    }));
                }

                let triggerable = 0;
                if (this.agora) {
                    Object.keys(this.gamedatas.conspiraciesSituation[this.getActivePlayerId()]).forEach(dojo.hitch(this, function (index) {
                        var conspiracyData = this.gamedatas.conspiraciesSituation[this.getActivePlayerId()][index];
                        if (args.mayTriggerConspiracy && conspiracyData.prepared && !conspiracyData.triggered && conspiracyData.useful) {
                            if (this.isCurrentPlayerActive()) {
                                let conspiracyNode = dojo.query('#player_conspiracies_' + this.getActivePlayerId() + ' div[data-conspiracy-position="' + conspiracyData.position + '"]')[0];
                                dojo.addClass(conspiracyNode, 'green_border');
                            }
                            triggerable++;
                        }
                    }));
                }

                if (this.agora && triggerable > 0) {
                    if (activatable > 0) {
                        this.playerTurnDescription = _('Age ${ageRoman}: ${actplayer} must play an Age card, activate a card from the Pantheon or trigger a Conspiracy first');
                        this.playerTurnDescriptionMyTurn = _('Age ${ageRoman}: ${you} must play an Age card, activate a card from the Pantheon or trigger a Conspiracy first');
                    }
                    else {
                        this.playerTurnDescription = _('Age ${ageRoman}: ${actplayer} must choose and use an age card or activate a card from the Pantheon');
                        this.playerTurnDescriptionMyTurn = _('Age ${ageRoman}: ${you} must choose an age card, or activate a card from the Pantheon');
                    }
                }
                else {
                    if (activatable > 0) {
                        this.playerTurnDescription = _('Age ${ageRoman}: ${actplayer} must choose and use an age card or activate a card from the Pantheon');
                        this.playerTurnDescriptionMyTurn = _('Age ${ageRoman}: ${you} must choose an age card, or activate a card from the Pantheon');
                    }
                    else {
                        this.playerTurnDescription = _('Age ${ageRoman}: ${actplayer} must choose and use an age card');
                        this.playerTurnDescriptionMyTurn = _('Age ${ageRoman}: ${you} must choose an age card');
                    }
                }
                this.updatePlayerTurnStateDescription();
            },

            updatePlayerTurnStateDescription: function() {
                this.gamedatas.gamestate.description = this.playerTurnDescription;
                this.gamedatas.gamestate.descriptionmyturn = this.playerTurnDescriptionMyTurn;
                this.updatePageTitle();
            },

            cancelDraftpoolClick: function() {
                this.clearPlayerTurnNodeGlow();
                this.clearRedBorder();
                dojo.setStyle('draftpool_actions', 'display', 'none');
                this.updatePlayerTurnStateDescription();
            },

            onPlayerTurnDraftpoolClick: function (e) {
                if (this.debug) console.log('onPlayerTurnDraftpoolClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.isCurrentPlayerActive()) {
                    this.clearPlayerTurnNodeGlow();
                    this.clearRedBorder();
                    this.hideActivateDivinityDialog();

                    var building = dojo.hasClass(e.target, 'building') ? e.target : dojo.query(e.target).closest(".building")[0];
                    dojo.addClass(building, 'glow');

                    this.playerTurnBuildingId = dojo.attr(building, 'data-building-id');
                    this.playerTurnNode = building;

                    var cardData = this.getDraftpoolCardData(this.playerTurnBuildingId);
                    dojo.query('#buttonDiscardBuilding .coin>span')[0].innerHTML = '+' + this.gamedatas.draftpool.discardGain[this.player_id];

                    var playerCoins = this.gamedatas.playersSituation[this.player_id].coins;

                    var canAffordBuilding = cardData.cost[this.player_id] <= playerCoins;
                    dojo.removeClass($('buttonConstructBuilding'), 'bgabutton_blue');
                    dojo.removeClass($('buttonConstructBuilding'), 'bgabutton_darkgray');
                    dojo.addClass($('buttonConstructBuilding'), canAffordBuilding ? 'bgabutton_blue' : 'bgabutton_darkgray');

                    var buildingData = this.gamedatas.buildings[this.playerTurnBuildingId];
                    if (buildingData.type == "Senator") {
                        dojo.query('#buttonConstructBuilding > span')[0].innerHTML = _('Recruit senator');
                    }
                    else {
                        dojo.query('#buttonConstructBuilding > span')[0].innerHTML = _('Construct building');
                    }

                    var canAffordWonder = false;
                    Object.keys(this.gamedatas.wondersSituation[this.player_id]).forEach(dojo.hitch(this, function (index) {
                        var wonderData = this.gamedatas.wondersSituation[this.player_id][index];
                        if (!wonderData.constructed) {
                            if (wonderData.cost <= playerCoins) {
                                canAffordWonder = true;
                            }
                        }
                    }));
                    dojo.removeClass($('buttonConstructWonder'), 'bgabutton_blue');
                    dojo.removeClass($('buttonConstructWonder'), 'bgabutton_darkgray');
                    dojo.addClass($('buttonConstructWonder'), canAffordWonder ? 'bgabutton_blue' : 'bgabutton_darkgray');

                    let conspiraciesToPrepare = dojo.query('#player_conspiracies_' + this.player_id + ' .conspiracy_small[data-conspiracy-prepared="0"][data-conspiracy-triggered="0"]');
                    dojo.toggleClass($('buttonPrepareConspiracy'), 'bgabutton_blue', conspiraciesToPrepare.length > 0);
                    dojo.toggleClass($('buttonPrepareConspiracy'), 'bgabutton_darkgray', conspiraciesToPrepare.length == 0);

                    dojo.setStyle('draftpool_actions', 'display', 'flex');

                    this.setClientState("client_useAgeCard", {
                        descriptionmyturn: _("${you} must choose the action for the age card, or select a different age card"),
                    });
                }
            },

            notif_takeToken: function (notif) {
                if (this.debug) console.log('notif_progressTokenChosen', notif);

                let container = dojo.query('.player_info.' + this.getPlayerAlias(notif.args.playerId) + ' .player_area_progress_tokens')[0];
                if (notif.args.type == 'mythology') {
                    // Destroy first outline node if it exists
                    dojo.destroy(dojo.query('.player_info.' + this.getPlayerAlias(notif.args.playerId) + ' .player_area_progress_tokens .mythology_token_outline:empty')[0]);

                    newTokenContainerNode = dojo.place(this.getMythologyTokenHtml(notif.args.tokenId, Math.ceil(notif.args.tokenId / 2)), container);
                    newTokenNode = dojo.query('.mythology_token', newTokenContainerNode)[0];
                    oldTokenNode = dojo.query('#draftpool .mythology_token[data-mythology-token-id=' + notif.args.tokenId + ']')[0];
                }
                if (notif.args.type == 'offering') {
                    // Destroy first outline node if it exists
                    dojo.destroy(dojo.query('.player_info.' + this.getPlayerAlias(notif.args.playerId) + ' .player_area_progress_tokens .offering_token_outline:empty')[0]);

                    newTokenContainerNode = dojo.place(this.getOfferingTokenHtml(notif.args.tokenId), container);
                    newTokenNode = dojo.query('.offering_token', newTokenContainerNode)[0];
                    oldTokenNode = dojo.query('#draftpool .offering_token[data-offering-token-id=' + notif.args.tokenId + ']')[0];
                }
                oldTokenContainerNode = oldTokenNode.parentElement;
                this.placeOnObject(newTokenNode, oldTokenNode);
                dojo.destroy(oldTokenContainerNode);

                var anim = dojo.fx.chain([
                    this.slideToObjectPos(newTokenNode, newTokenContainerNode, 0, 0, this.progressTokenDuration),
                ]);

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                anim.play();
            },

            clearPlayerTurnNodeGlow: function () {
                if (this.playerTurnNode) {
                    dojo.removeClass(this.playerTurnNode, 'glow');
                }
            },

            clearGreenBorder: function (container=null) {
                dojo.query('.green_border', container).removeClass("green_border");
            },
            clearRedBorder: function () {
                dojo.query('.red_border').removeClass("red_border");
            },
            clearGrayBorder: function () {
                dojo.query('.gray_border').removeClass("gray_border");
            },
            clearDivinityBorder: function () {
                dojo.query('.divinity_border').removeClass("divinity_border");
            },

            //     _        _   _               _____     _                          ____                      _
            //    / \   ___| |_(_) ___  _ __   |_   _| __(_) __ _  __ _  ___ _ __   / ___|___  _ __  ___ _ __ (_)_ __ __ _  ___ _   _
            //   / _ \ / __| __| |/ _ \| '_ \    | || '__| |/ _` |/ _` |/ _ \ '__| | |   / _ \| '_ \/ __| '_ \| | '__/ _` |/ __| | | |
            //  / ___ \ (__| |_| | (_) | | | |   | || |  | | (_| | (_| |  __/ |    | |__| (_) | | | \__ \ |_) | | | | (_| | (__| |_| |
            // /_/   \_\___|\__|_|\___/|_| |_|   |_||_|  |_|\__, |\__, |\___|_|     \____\___/|_| |_|___/ .__/|_|_|  \__,_|\___|\__, |
            //                                              |___/ |___/                                 |_|                     |___/

            onPlayerTurnTriggerConspiracyClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnTriggerConspiracyClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionTriggerConspiracy')) {
                        return;
                    }

                    var conspiracyNode = dojo.hasClass(e.target, 'conspiracy') ? e.target : dojo.query(e.target).closest(".conspiracy")[0];
                    var conspiracyId = dojo.attr(conspiracyNode, "data-conspiracy-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionTriggerConspiracy.html", {
                            lock: true,
                            conspiracyId: conspiracyId,
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'display', 'none');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_triggerConspiracy: function (notif) {
                if (this.debug) console.log('notif_triggerConspiracy', notif);

                this.clearPlayerTurnNodeGlow();
                this.clearRedBorder();
                this.clearGreenBorder();

                let oldConspiracyNodeHtml = dojo.query('#player_conspiracies_' + notif.args.playerId + ' div[data-conspiracy-position="' + notif.args.conspiracyPosition + '"]')[0].outerHTML;

                // Update the conspiracies situation, because now the conspiracy is prepared and the age card has been rendered.
                this.updateConspiraciesSituation(notif.args.conspiraciesSituation);

                // Animate conspiracy revealed:
                if (1) {

                    let conspiracyNode = dojo.query('#player_conspiracies_' + notif.args.playerId + ' div[data-conspiracy-position="' + notif.args.conspiracyPosition + '"]')[0];
                    var conspiracyContainer = conspiracyNode.parentElement;
                    var conspiracy = this.gamedatas.conspiracies[notif.args.conspiracyId];

                    var ageCardContainer = dojo.query('.age_card_container', conspiracyContainer)[0];
                    var conspiracyBackNode = dojo.place(oldConspiracyNodeHtml, conspiracyContainer);

                    // Move age card to start position and set starting properties.
                    this.placeOnObjectPos(conspiracyBackNode, conspiracyNode, 0, 0);
                    dojo.style(conspiracyBackNode, 'z-index', 15);

                    var conspiracyNodePosition = dojo.position(conspiracyNode);

                    var anim = dojo.fx.chain([
                        dojo.fx.combine([
                            dojo.animateProperty({
                                node: conspiracyBackNode,
                                duration: this.turnAroundCardDuration,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: 0, end: 180}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                },
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                })
                            }),
                            dojo.animateProperty({
                                node: conspiracyNode,
                                duration: this.turnAroundCardDuration,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: -180, end: 0}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                }
                            }),
                        ]),
                        // TODO: conspiracy effects
                        // Military Track animation (pawn movement, token handling)
                        bgagame.MilitaryTrackAnimator.get().getAnimation(notif.args.playerId, notif.args.payment, "agora"),
                        // Conspiracy Blackmail (coins going from opponent to player)
                        this.getEconomyProgressTokenAnimation(notif.args.payment.coinsFromOpponent, this.getOppositePlayerId(notif.args.playerId)),
                        // Conspiracy Embezzlement part 1
                        bgagame.CoinAnimator.get().getAnimation(
                            conspiracyNode,
                            this.getPlayerCoinContainer(notif.args.playerId),
                            notif.args.payment.coinReward,
                            notif.args.playerId,
                            [conspiracy.visualCoinPosition[0] * conspiracyNodePosition.w, conspiracy.visualCoinPosition[1] * conspiracyNodePosition.h]
                        ),
                        // Conspiracy Embezzlement part 2
                        bgagame.CoinAnimator.get().getAnimation(
                            this.getPlayerCoinContainer(notif.args.playerId, true),
                            conspiracyNode,
                            notif.args.payment.opponentCoinLoss,
                            this.getOppositePlayerId(notif.args.playerId),
                            [0, 0],
                            [conspiracy.visualOpponentCoinLossPosition[0] * conspiracyNodePosition.w, conspiracy.visualOpponentCoinLossPosition[1] * conspiracyNodePosition.h]
                        ),
                    ]);

                    dojo.connect(anim, 'beforeBegin', dojo.hitch(this, function () {
                        dojo.style(conspiracyContainer, 'z-index', 11);
                        dojo.style(conspiracyNode, 'z-index', 20);
                    }));
                    dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                        // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                        anim.stop();

                        dojo.style(conspiracyBackNode, 'z-index', 1);
                        dojo.style(conspiracyNode, 'z-index', 2);
                        dojo.style(conspiracyContainer, 'z-index', 10);
                    }));

                    // Wait for animation before handling the next notification (= state change).
                    this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                    anim.play();
                }
            },

            //    ____                _                   _     ____        _ _     _ _
            //   / ___|___  _ __  ___| |_ _ __ _   _  ___| |_  | __ ) _   _(_) | __| (_)_ __   __ _
            //  | |   / _ \| '_ \/ __| __| '__| | | |/ __| __| |  _ \| | | | | |/ _` | | '_ \ / _` |
            //  | |__| (_) | | | \__ \ |_| |  | |_| | (__| |_  | |_) | |_| | | | (_| | | | | | (_| |
            //   \____\___/|_| |_|___/\__|_|   \__,_|\___|\__| |____/ \__,_|_|_|\__,_|_|_| |_|\__, |
            //                                                                                |___/

            onPlayerTurnConstructBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnConstructBuildingClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionConstructBuilding')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionConstructBuilding.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'display', 'none');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            getEconomyProgressTokenAnimation: function (coins, player_id) {
                return bgagame.CoinAnimator.get().getAnimation(
                    this.getPlayerCoinContainer(player_id),
                    this.getPlayerCoinContainer(this.getOppositePlayerId(player_id)),
                    coins,
                    player_id
                );
            },

            notif_constructBuilding: function (notif) {
                if (this.debug) console.log('notif_constructBuilding', notif);

                this.clearRedBorder(); // For Conspiracy 7, Property Fraud
                this.clearGreenBorder();

                var buildingNode = dojo.query(".building_small[data-building-id=" + notif.args.buildingId + "]")[0]; // Without the .building_small, the building might be selected from the (linked) buildings list.
                var buildingNodeParent = null;
                if (buildingNode) {
                    buildingNodeParent = buildingNode.parentElement; // Only used when we are constructing a discarded building.
                }
                else {
                    // Conspiracy 8, Treason
                    let conspiracyNode = dojo.query('[data-conspiracy-id="8"]')[0];
                    // Create nodes that are approprately positioned and can be destroyed afterwards.
                    buildingNodeParent = dojo.place('<div id="buildingNodeParent" style="position: relative; left: calc(70px * var(--scale)); top: calc(90px * var(--scale));"><div id="buildingNode" style="position: absolute;"></div></div>', conspiracyNode);
                    buildingNode = dojo.query('#buildingNode', buildingNodeParent)[0];
                }

                var building = this.gamedatas.buildings[notif.args.buildingId];
                var container = dojo.query('.player_buildings.player' + notif.args.playerId + ' .' + building.type)[0];
                var playerBuildingContainer = dojo.place(this.getBuildingDivHtml(notif.args.buildingId, building), container, "last");
                var playerBuildingId = 'player_building_' + notif.args.buildingId;

                this.placeOnObjectPos(playerBuildingId, buildingNode, 0.5 * this.getCssVariable('--scale'), -59.5 * this.getCssVariable('--scale'));
                dojo.style(playerBuildingId, 'opacity', 0);
                dojo.style(playerBuildingId, 'z-index', 20);

                this.autoUpdateScale(); // If the building is added to the highest stack, part of the layout will be pushed below the viewport. So it's better to update the scale now so all animations will match the updated scale.

                var playerAlias = this.getPlayerAlias(notif.args.playerId);
                var coinNode = dojo.query('.draftpool_building_cost.' + playerAlias + ' .coin', buildingNode)[0];

                var buildingMoveAnim = this.slideToObjectPos(playerBuildingId, playerBuildingContainer, 0, 0, this.constructBuildingAnimationDuration * 0.6);
                if (notif.args.payment.discardedCard) {
                    dojo.connect(buildingMoveAnim, 'onEnd', dojo.hitch(this, function (node) {
                        var whiteblock = $('lower_divs_container');
                        dojo.removeClass(whiteblock, 'red_border');
                        window.scroll(this.rememberScrollX, this.rememberScrollY); // Scroll back to the position before this state.
                        this.freezeLayout = 0;
                        dojo.destroy(buildingNodeParent); // Destroy old parent (container in discarded buildings block).
                    }));
                }

                var anim = dojo.fx.chain([
                    // Coin payment
                    bgagame.CoinAnimator.get().getAnimation(
                        this.getPlayerCoinContainer(notif.args.playerId),
                        coinNode,
                        notif.args.payment.cost - notif.args.payment.economyProgressTokenCoins,
                        notif.args.playerId
                    ),
                    // Economy Progress Token
                    this.getEconomyProgressTokenAnimation(notif.args.payment.economyProgressTokenCoins, notif.args.playerId),
                    // Cross-fade building into player-building (small header only building)
                    dojo.fx.combine([
                        dojo.fadeIn({node: playerBuildingId, duration: this.constructBuildingAnimationDuration * 0.4}),
                        dojo.fadeOut({
                            node: buildingNode,
                            duration: this.constructBuildingAnimationDuration * 0.4,
                            onEnd: dojo.hitch(this, function (node) {
                                dojo.destroy(node);
                            })
                        }),
                    ]),
                    // Move player building into it's column.
                    buildingMoveAnim,
                    // Coin reward
                    bgagame.CoinAnimator.get().getAnimation(
                        playerBuildingContainer,
                        this.getPlayerCoinContainer(notif.args.playerId),
                        notif.args.payment.coinReward,
                        notif.args.playerId
                    ),
                    // Decree coin reward
                    bgagame.CoinAnimator.get().getAnimation(
                        dojo.query('.decree_containers div[data-decree-id="' + notif.args.payment.decreeCoinRewardDecreeId + '"]')[0],
                        this.getPlayerCoinContainer(notif.args.payment.decreeCoinRewardPlayerId),
                        notif.args.payment.decreeCoinReward,
                        notif.args.payment.decreeCoinRewardPlayerId
                    ),
                    // Urbanism Progress Token (4 coins when constructing a Building through a linked building)
                    bgagame.CoinAnimator.get().getAnimation(
                        $('progress_token_10'),
                        this.getPlayerCoinContainer(notif.args.playerId),
                        notif.args.payment.urbanismAward,
                        notif.args.playerId
                    ),
                    // Military Track animation (pawn movement, token handling)
                    bgagame.MilitaryTrackAnimator.get().getAnimation(notif.args.playerId, notif.args.payment),
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                    anim.stop();
                    // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                    dojo.query("#swd_wrap .coin.animated").forEach(dojo.destroy);

                    dojo.style(playerBuildingId, 'z-index', 15);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                anim.play();
            },

            //   ____  _                       _   ____        _ _     _ _
            //  |  _ \(_)___  ___ __ _ _ __ __| | | __ ) _   _(_) | __| (_)_ __   __ _
            //  | | | | / __|/ __/ _` | '__/ _` | |  _ \| | | | | |/ _` | | '_ \ / _` |
            //  | |_| | \__ \ (_| (_| | | | (_| | | |_) | |_| | | | (_| | | | | | (_| |
            //  |____/|_|___/\___\__,_|_|  \__,_| |____/ \__,_|_|_|\__,_|_|_| |_|\__, |
            //                                                                   |___/

            onPlayerTurnDiscardBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnDiscardBuildingClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionDiscardBuilding')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionDiscardBuilding.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'display', 'none');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_discardBuilding: function (notif) {
                if (this.debug) console.log('notif_discardBuilding', notif);

                this.clearPlayerTurnNodeGlow();
                this.clearRedBorder();
                this.clearGreenBorder();

                var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];

                var coinAnimation = bgagame.CoinAnimator.get().getAnimation(
                    buildingNode,
                    dojo.query('.player_info.' + this.getPlayerAlias(notif.args.playerId) + ' .player_area_coins')[0],
                    notif.args.gain,
                    notif.args.playerId
                );

                // Set up a wrapper div so we can move the building to pos 0,0 of that wrapper
                var discardedCardsContainer = $('discarded_cards_container');
                var wrapperDiv = dojo.clone(dojo.query('.discarded_cards_cursor', discardedCardsContainer)[0]);
                dojo.removeClass(wrapperDiv, 'discarded_cards_cursor');
                dojo.place(wrapperDiv, discardedCardsContainer, discardedCardsContainer.children.length - 1);

                buildingNode = this.attachToNewParent(buildingNode, wrapperDiv); // attachToNewParent creates and returns a new instance of the node (replacing the old one).
                dojo.attr(buildingNode, 'id', ''); // Remove the draftpool specific id
                dojo.query(".draftpool_building_cost", buildingNode).forEach(dojo.destroy); // Remove cost coins from building

                var moveAnim = this.slideToObjectPos(buildingNode, wrapperDiv, 0, 0, this.discardBuildingAnimationDuration);

                var anim = dojo.fx.chain([
                    coinAnimation,
                    moveAnim
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    if (notif.args.draftpool) this.updateDraftpool(notif.args.draftpool);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                anim.play();
            },

            //    ____                _                   _    __        __              _
            //   / ___|___  _ __  ___| |_ _ __ _   _  ___| |_  \ \      / /__  _ __   __| | ___ _ __
            //  | |   / _ \| '_ \/ __| __| '__| | | |/ __| __|  \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__|
            //  | |__| (_) | | | \__ \ |_| |  | |_| | (__| |_    \ V  V / (_) | | | | (_| |  __/ |
            //   \____\___/|_| |_|___/\__|_|   \__,_|\___|\__|    \_/\_/ \___/|_| |_|\__,_|\___|_|

            onPlayerTurnConstructWonderClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnConstructWonderClick');

                if (this.isCurrentPlayerActive()) {
                    this.clearRedBorder();
                    Object.keys(this.gamedatas.wondersSituation[this.player_id]).forEach(dojo.hitch(this, function (index) {
                        var wonderData = this.gamedatas.wondersSituation[this.player_id][index];
                        if (!wonderData.constructed) {
                            if (wonderData.cost <= this.gamedatas.playersSituation[this.player_id].coins) {
                                dojo.addClass($('wonder_' + wonderData.wonder), 'red_border');
                            }
                        }
                    }));

                    this.setClientState("client_useAgeCard", {
                        descriptionmyturn: _("${you} must select a wonder to construct, or select a different card or action"),
                    });
                }
            },

            onPlayerTurnConstructWonderSelectedClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnConstructWonderSelectedClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionConstructWonder')) {
                        return;
                    }

                    var wonderNode = dojo.hasClass(e.target, 'wonder') ? e.target : dojo.query(e.target).closest(".wonder")[0];
                    var wonderId = dojo.attr(wonderNode, "data-wonder-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionConstructWonder.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId,
                            wonderId: wonderId,
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'display', 'none');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_constructWonder: function (notif) {
                if (this.debug) console.log('notif_constructWonder', notif);

                this.clearPlayerTurnNodeGlow();
                this.clearRedBorder();
                this.clearGreenBorder();

                var wonderContainer = dojo.query('#player_wonders_' + notif.args.playerId + ' #wonder_' + notif.args.wonderId + '_container')[0];
                var coinNode = dojo.query('.player_wonder_cost', wonderContainer)[0];
                var position = this.getWonderCardData(notif.args.playerId, notif.args.wonderId);

                var coinAnimation = dojo.fx.combine([
                    // Coin payment
                    bgagame.CoinAnimator.get().getAnimation(
                        this.getPlayerCoinContainer(notif.args.playerId),
                        coinNode,
                        position.cost - notif.args.payment.economyProgressTokenCoins,
                        notif.args.playerId
                    ),
                    // Economy Progress Token
                    this.getEconomyProgressTokenAnimation(notif.args.payment.economyProgressTokenCoins, notif.args.playerId),
                ]);

                dojo.connect(coinAnimation, 'onEnd', dojo.hitch(this, function (node) {
                    // Update the wonders situation, because now the wonder is constructed and the age card has been rendered.
                    this.updateWondersSituation(notif.args.wondersSituation);

                    var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];

                    // Animate age card towards wonder:
                    if (1) {
                        var wonderContainer = dojo.query('#player_wonders_' + notif.args.playerId + ' #wonder_' + notif.args.wonderId + '_container')[0];
                        var ageCardContainer = dojo.query('.age_card_container', wonderContainer)[0];
                        var ageCardNode = dojo.query('.building_small', ageCardContainer)[0];
                        var wonderNode = dojo.query('.wonder_small', wonderContainer)[0];
                        var wonder = this.gamedatas.wonders[notif.args.wonderId];

                        // Move age card to start position and set starting properties.
                        this.placeOnObjectPos(ageCardNode, buildingNode, 0, 0);
                        dojo.style(ageCardNode, 'z-index', 15);
                        dojo.style(ageCardNode, 'transform', 'rotate(0deg) perspective(40em)'); // Somehow affects the position of the element after the slide. Otherwise I would delete this line.

                        var wonderNodePosition = dojo.position(wonderNode);
                        var coinRewardAnimation = bgagame.CoinAnimator.get().getAnimation(
                            wonderNode,
                            this.getPlayerCoinContainer(notif.args.playerId),
                            notif.args.payment.coinReward,
                            notif.args.playerId,
                            [wonder.visualCoinPosition[0] * wonderNodePosition.w, wonder.visualCoinPosition[1] * wonderNodePosition.h]
                        );

                        var opponentCoinContainer = this.getPlayerCoinContainer(notif.args.playerId, true);
                        var opponentCoinContainerPosition = dojo.position(opponentCoinContainer);
                        var opponentCoinLossAnimation = bgagame.CoinAnimator.get().getAnimation(
                            opponentCoinContainer,
                            wonderNode,
                            notif.args.payment.opponentCoinLoss,
                            this.getOppositePlayerId(notif.args.playerId),
                            [0, 0],
                            [wonder.visualOpponentCoinLossPosition[0] * wonderNodePosition.w, wonder.visualOpponentCoinLossPosition[1] * wonderNodePosition.h]
                        );

                        var eightWonderFadeOut = dojo.fx.combine([]);
                        if (notif.args.payment.eightWonderId) {
                            eightWonderFadeOut = dojo.fadeOut({
                                node: $('wonder_' + notif.args.payment.eightWonderId + '_container'),
                                duration: 1000,
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                })
                            });
                        }

                        var anim = dojo.fx.chain([
                            dojo.fx.combine([
                                dojo.animateProperty({
                                    node: buildingNode,
                                    duration: this.constructWonderAnimationDuration / 3,
                                    easing: dojo.fx.easing.linear,
                                    properties: {
                                        propertyTransform: {start: 0, end: 180}
                                    },
                                    onAnimate: function (values) {
                                        dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                    },
                                    onEnd: dojo.hitch(this, function (node) {
                                        dojo.destroy(node);
                                    })
                                }),
                                dojo.animateProperty({
                                    node: ageCardNode,
                                    duration: this.constructWonderAnimationDuration / 3,
                                    easing: dojo.fx.easing.linear,
                                    properties: {
                                        propertyTransform: {start: -180, end: 0}
                                    },
                                    onAnimate: function (values) {
                                        dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                    }
                                }),
                            ]),
                            dojo.fx.combine([
                                dojo.animateProperty({
                                    node: ageCardNode,
                                    delay: this.constructWonderAnimationDuration / 6,
                                    duration: this.constructWonderAnimationDuration / 4,
                                    properties: {
                                        propertyTransform: {start: 0, end: -90}
                                    },
                                    onAnimate: function (values) {
                                        dojo.style(this.node, 'transform', 'rotate(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                    }
                                }),
                                this.slideToObjectPos(ageCardNode, ageCardContainer, 0, 0, this.constructWonderAnimationDuration / 3 * 2),
                            ]),
                            coinRewardAnimation,
                            // Decree coin reward
                            bgagame.CoinAnimator.get().getAnimation(
                                dojo.query('.decree_containers div[data-decree-id="' + notif.args.payment.decreeCoinRewardDecreeId + '"]')[0],
                                this.getPlayerCoinContainer(notif.args.payment.decreeCoinRewardPlayerId),
                                notif.args.payment.decreeCoinReward,
                                notif.args.payment.decreeCoinRewardPlayerId
                            ),
                            opponentCoinLossAnimation,
                            // Military Track animation (pawn movement, token handling)
                            bgagame.MilitaryTrackAnimator.get().getAnimation(notif.args.playerId, notif.args.payment),
                            eightWonderFadeOut,
                        ]);

                        dojo.connect(anim, 'beforeBegin', dojo.hitch(this, function () {
                            dojo.style(wonderContainer, 'z-index', 11);
                            dojo.style(wonderNode, 'z-index', 20);
                            dojo.style(ageCardNode, 'transform', 'rotate(0deg) perspective(40em) rotateY(-90deg)'); // The rotateY(-90deg) affects the position the element will end up after the slide. Here's the place to apply it therefor, not before the animation instantiation.
                        }));
                        dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                            // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                            anim.stop();
                            // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                            dojo.query("#swd_wrap .coin.animated").forEach(dojo.destroy);

                            dojo.style(ageCardNode, 'z-index', 1);
                            dojo.style(wonderNode, 'z-index', 2);
                            dojo.style(wonderContainer, 'z-index', 10);
                        }));

                        // Wait for animation before handling the next notification (= state change).
                        this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                        anim.play();
                    }

                }));
                coinAnimation.play();
            },

            //     _        _   _            _                 ____  _       _       _ _
            //    / \   ___| |_(_)_   ____ _| |_ ___    __ _  |  _ \(_)_   _(_)_ __ (_) |_ _   _
            //   / _ \ / __| __| \ \ / / _` | __/ _ \  / _` | | | | | \ \ / / | '_ \| | __| | | |
            //  / ___ \ (__| |_| |\ V / (_| | ||  __/ | (_| | | |_| | |\ V /| | | | | | |_| |_| |
            // /_/   \_\___|\__|_| \_/ \__,_|\__\___|  \__,_| |____/|_| \_/ |_|_| |_|_|\__|\__, |
            //                                                                             |___/

            onActivateDivinityClick: function (e) {
                if (this.debug) console.log('onActivateDivinityClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                this.activateDivinityNode = dojo.hasClass(e.target, 'divinity') ? e.target : dojo.query(e.target).closest(".divinity")[0];
                this.activateDivinityId = dojo.attr(this.activateDivinityNode, 'data-divinity-id');

                this.cancelDraftpoolClick();
                this.showActivateDivinityDialog();
            },

            showActivateDivinityDialog: function() {
                dojo.style('activate_divinity', 'display', 'block');
                this.autoUpdateScale();

                let divinityContainer = $('activate_divinity_container');
                dojo.empty(divinityContainer);

                // Till we implement offering token discounts
                dojo.empty($('activate_divinity_payment'));

                let divinityNode = dojo.clone(this.activateDivinityNode);
                dojo.removeClass(divinityNode, 'divinity_border');
                dojo.place(divinityNode, divinityContainer);
            },

            hideActivateDivinityDialog: function() {
                if (dojo.style('activate_divinity', 'display') != 'none') {
                    dojo.style('activate_divinity', 'display', 'none');
                    this.autoUpdateScale();
                }
            },

            onActivateDivinityConfirmClick: function (e) {
                if (this.debug) console.log('onActivateDivinityConfirmClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.isCurrentPlayerActive()) {

                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionActivateDivinity')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionActivateDivinity.html", {
                            divinityId: this.activateDivinityId,
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_activateDivinity: function (notif) {
                if (this.debug) console.log('notif_activateDivinity', notif);

                this.clearDivinityBorder();
                this.hideActivateDivinityDialog();

                var oldDivinityNode = dojo.query('.pantheon_space_containers .divinity[data-divinity-id="' + notif.args.divinityId + '"]')[0];
                var oldDivinityContainerNode = oldDivinityNode.parentElement;
                var spaceNode = dojo.query(oldDivinityNode).closest('.pantheon_space')[0];
                let space = dojo.attr(spaceNode, 'data-space');
                let coinNode = dojo.query('.pantheon_cost_containers .pantheon_space[data-space="' + space + '"] .' + this.getPlayerAlias(notif.args.playerId) + ' .coin')[0];

                var coinAnimation = bgagame.CoinAnimator.get().getAnimation(
                    this.getPlayerCoinContainer(notif.args.playerId),
                    coinNode,
                    notif.args.cost,
                    notif.args.playerId
                );

                dojo.connect(coinAnimation, 'onEnd', dojo.hitch(this, function (node) {
                    var newDivinityContainerNode = dojo.place(this.getDivinityDivHtml(notif.args.divinityId, notif.args.divinityType, false), 'player_conspiracies_' + notif.args.playerId);
                    var newDivinityNode = dojo.query('.divinity', newDivinityContainerNode)[0];
                    this.autoUpdateScale();

                    var coinRewardAnimation = bgagame.CoinAnimator.get().getAnimation(
                        newDivinityNode,
                        this.getPlayerCoinContainer(notif.args.playerId),
                        notif.args.payment.coinReward,
                        notif.args.playerId,
                    );

                    this.placeOnObjectPos(newDivinityNode, oldDivinityNode, 0, 0);
                    // dojo.style(newDivinityNode, 'transform', 'rotate(' + this.getCurrentRotation(spaceNode) + 'deg)');
                    dojo.destroy(oldDivinityContainerNode);

                    let anim = dojo.fx.chain([
                        dojo.fx.combine([
                            this.slideToObjectPos(newDivinityNode, newDivinityContainerNode, 0, 0, this.activate_divinity_duration),
                            dojo.animateProperty({
                                node: newDivinityNode,
                                // delay: this.constructWonderAnimationDuration / 6,
                                duration: this.activate_divinity_duration * 0.6,
                                properties: {
                                    propertyTransform: {start: this.getCurrentRotation(spaceNode), end: 0}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'rotate(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                }
                            })
                        ]),
                        coinRewardAnimation,
                        // Military Track animation (pawn movement, token handling)
                        bgagame.MilitaryTrackAnimator.get().getAnimation(notif.args.playerId, notif.args.payment),
                    ]);

                    anim.play();

                    this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);
                }));
                coinAnimation.play();
            },

            //  ____                                           ____                      _
            // |  _ \ _ __ ___ _ __   __ _ _ __ ___    __ _   / ___|___  _ __  ___ _ __ (_)_ __ __ _  ___ _   _
            // | |_) | '__/ _ \ '_ \ / _` | '__/ _ \  / _` | | |   / _ \| '_ \/ __| '_ \| | '__/ _` |/ __| | | |
            // |  __/| | |  __/ |_) | (_| | | |  __/ | (_| | | |__| (_) | | | \__ \ |_) | | | | (_| | (__| |_| |
            // |_|   |_|  \___| .__/ \__,_|_|  \___|  \__,_|  \____\___/|_| |_|___/ .__/|_|_|  \__,_|\___|\__, |
            //                |_|                                                 |_|                     |___/

            onPlayerTurnPrepareConspiracyClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnPrepareConspiracyClick');

                if (this.isCurrentPlayerActive()) {
                    this.clearRedBorder();
                    Object.keys(this.gamedatas.conspiraciesSituation[this.player_id]).forEach(dojo.hitch(this, function (index) {
                        var conspiracyData = this.gamedatas.conspiraciesSituation[this.player_id][index];
                        if (!conspiracyData.prepared && !conspiracyData.triggered) {
                            let conspiracyNode = dojo.query('#player_conspiracies_' + this.player_id + ' div[data-conspiracy-position="' + conspiracyData.position + '"]')[0];
                            dojo.addClass(conspiracyNode, 'red_border');
                        }
                    }));

                    this.setClientState("client_useAgeCard", {
                        descriptionmyturn: _("${you} must select a Conspiracy to prepare, or select a different card or action"),
                    });
                }
            },

            onPlayerTurnPrepareConspiracySelectedClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnPrepareConspiracySelectedClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionPrepareConspiracy')) {
                        return;
                    }

                    var conspiracyNode = dojo.hasClass(e.target, 'conspiracy') ? e.target : dojo.query(e.target).closest(".conspiracy")[0];
                    var conspiracyId = dojo.attr(conspiracyNode, "data-conspiracy-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionPrepareConspiracy.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId,
                            conspiracyId: conspiracyId,
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'display', 'none');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_prepareConspiracy: function (notif) {
                if (this.debug) console.log('notif_prepareConspiracy', notif);

                this.clearPlayerTurnNodeGlow();
                this.clearRedBorder();
                this.clearGreenBorder();

                // Update the conspiracies situation, because now the conspiracy is prepared and the age card has been rendered.
                this.updateConspiraciesSituation(notif.args.conspiraciesSituation);

                var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];

                // Animate age card towards conspiracy:
                if (1) {

                    let conspiracyNode = dojo.query('#player_conspiracies_' + notif.args.playerId + ' div[data-conspiracy-position="' + notif.args.position + '"]')[0];
                    var conspiracyContainer = conspiracyNode.parentElement;
                    dojo.addClass(conspiracyContainer, 'animating');

                    var ageCardContainer = dojo.query('.age_card_container', conspiracyContainer)[0];
                    var ageCardNode = dojo.query('.building_small', ageCardContainer)[0];

                    var peekNode = dojo.query('.conspiracy_peek', conspiracyContainer)[0];

                    // Move age card to start position and set starting properties.
                    this.placeOnObjectPos(ageCardNode, buildingNode, 0, 0);
                    dojo.style(ageCardNode, 'z-index', 15);
                    dojo.style(ageCardNode, 'transform', 'rotate(0deg) perspective(40em)'); // Somehow affects the position of the element after the slide. Otherwise I would delete this line.

                    var conspiracyNodePosition = dojo.position(conspiracyNode);

                    var anim = dojo.fx.chain([
                        dojo.fx.combine([
                            dojo.animateProperty({
                                node: buildingNode,
                                duration: this.prepareConspiracyAnimationDuration / 3,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: 0, end: 180}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                },
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                })
                            }),
                            dojo.animateProperty({
                                node: ageCardNode,
                                duration: this.prepareConspiracyAnimationDuration / 3,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: -180, end: 0}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                }
                            }),
                        ]),
                        dojo.fx.combine([
                            this.slideToObjectPos(ageCardNode, ageCardContainer, 0, 0, this.prepareConspiracyAnimationDuration / 3 * 2),
                        ]),
                    ]);

                    dojo.connect(anim, 'beforeBegin', dojo.hitch(this, function () {
                        dojo.style(conspiracyContainer, 'z-index', 11);
                        dojo.style(conspiracyNode, 'z-index', 20);
                        dojo.style(peekNode, 'z-index', 21);
                        dojo.style(ageCardNode, 'transform', 'rotate(0deg) perspective(40em) rotateY(-90deg)'); // The rotateY(-90deg) affects the position the element will end up after the slide. Here's the place to apply it therefor, not before the animation instantiation.
                    }));
                    dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                        // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                        anim.stop();
                        // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                        dojo.query("#swd_wrap .coin.animated").forEach(dojo.destroy);

                        dojo.style(ageCardNode, 'z-index', 1);
                        dojo.style(conspiracyNode, 'z-index', 2);
                        dojo.style(peekNode, 'z-index', null);
                        dojo.style(conspiracyContainer, 'z-index', 10);
                        dojo.removeClass(conspiracyContainer, 'animating');
                    }));

                    // Wait for animation before handling the next notification (= state change).
                    this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                    anim.play();
                }
            },

            //   ____ _                                                                     _     _           _ _     _ _
            //  / ___| |__   ___   ___  ___  ___    ___  _ __  _ __   ___  _ __   ___ _ __ | |_  | |__  _   _(_) | __| (_)_ __   __ _
            // | |   | '_ \ / _ \ / _ \/ __|/ _ \  / _ \| '_ \| '_ \ / _ \| '_ \ / _ \ '_ \| __| | '_ \| | | | | |/ _` | | '_ \ / _` |
            // | |___| | | | (_) | (_) \__ \  __/ | (_) | |_) | |_) | (_) | | | |  __/ | | | |_  | |_) | |_| | | | (_| | | | | | (_| |
            //  \____|_| |_|\___/ \___/|___/\___|  \___/| .__/| .__/ \___/|_| |_|\___|_| |_|\__| |_.__/ \__,_|_|_|\__,_|_|_| |_|\__, |
            //                                          |_|   |_|                                                               |___/

            onEnterChooseOpponentBuilding: function (args) {
                var opponentId = this.getOppositePlayerId(this.getActivePlayerId());
                var buildingColumn = dojo.query('.player' + opponentId + ' .player_building_column.' + args.buildingType)[0];
                dojo.addClass(buildingColumn, 'red_border');
            },

            onOpponentBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onOpponentBuildingClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionChooseOpponentBuilding')) {
                        return;
                    }

                    var buildingId = dojo.attr(e.target, "data-building-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseOpponentBuilding.html", {
                            lock: true,
                            buildingId: buildingId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_opponentDiscardBuilding: function (notif) {
                if (this.debug) console.log('notif_opponentDiscardBuilding', notif);

                var buildingNode = this.createDiscardedBuildingNode(notif.args.buildingId);
                var playerBuildingNode = $('player_building_' + notif.args.buildingId);

                this.placeOnObjectPos(buildingNode, playerBuildingNode, -0.5 * this.getCssVariable('--scale'), 59.5 * this.getCssVariable('--scale'));
                dojo.style(buildingNode, 'opacity', 0);
                dojo.style(buildingNode, 'z-index', 100);

                var anim = dojo.fx.chain([
                    // Cross-fade building into player-building (small header only building)
                    dojo.fx.combine([
                        dojo.fadeIn({node: buildingNode, duration: this.constructBuildingAnimationDuration * 0.4}),
                        dojo.fadeOut({
                            node: playerBuildingNode,
                            duration: this.constructBuildingAnimationDuration * 0.4
                        }),
                    ]),
                    this.slideToObjectPos(buildingNode, buildingNode.parentNode, 0, 0, this.constructBuildingAnimationDuration * 0.6),
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    dojo.style(buildingNode, 'z-index', 5);
                    var buildingColumn = dojo.query(playerBuildingNode).closest(".player_building_column")[0];
                    dojo.removeClass(buildingColumn, 'red_border');
                    dojo.destroy(playerBuildingNode.parentNode);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration);

                anim.play();
            },

            //   ____ _                                _ _                       _          _   _           _ _     _ _
            //  / ___| |__   ___   ___  ___  ___    __| (_)___  ___ __ _ _ __ __| | ___  __| | | |__  _   _(_) | __| (_)_ __   __ _
            // | |   | '_ \ / _ \ / _ \/ __|/ _ \  / _` | / __|/ __/ _` | '__/ _` |/ _ \/ _` | | '_ \| | | | | |/ _` | | '_ \ / _` |
            // | |___| | | | (_) | (_) \__ \  __/ | (_| | \__ \ (_| (_| | | | (_| |  __/ (_| | | |_) | |_| | | | (_| | | | | | (_| |
            //  \____|_| |_|\___/ \___/|___/\___|  \__,_|_|___/\___\__,_|_|  \__,_|\___|\__,_| |_.__/ \__,_|_|_|\__,_|_|_| |_|\__, |
            //                                                                                                                |___/

            onEnterChooseDiscardedBuilding: function (args) {
                if (this.isCurrentPlayerActive()) {
                    var whiteblock = $('discarded_cards_whiteblock');
                    dojo.addClass(whiteblock, 'red_border');

                    // Scroll so the discarded card whiteblock is visible (remember the scroll position so we can restore the view later).
                    this.freezeLayout = 1;
                    this.rememberScrollX = window.scrollX;
                    this.rememberScrollY = window.scrollY;
                    whiteblock.scrollIntoView(false);
                }
            },

            onDiscardedBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onDiscardedBuildingClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionChooseDiscardedBuilding')) {
                        return;
                    }

                    var buildingId = dojo.attr(e.target, "data-building-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseDiscardedBuilding.html", {
                            lock: true,
                            buildingId: buildingId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            //    ____ _                            ____                                      _____     _
            //   / ___| |__   ___   ___  ___  ___  |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __
            //  | |   | '_ \ / _ \ / _ \/ __|/ _ \ | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \
            //  | |___| | | | (_) | (_) \__ \  __/ |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | |
            //   \____|_| |_|\___/ \___/|___/\___| |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|
            //                                                      |___/

            onEnterChooseProgressToken: function (args) {
                if (this.debug) console.log('onEnterChooseProgressToken', args);
                dojo.addClass($('board_progress_tokens'), 'red_border');
            },

            onProgressTokenClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onProgressTokenClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionChooseProgressToken')) {
                        return;
                    }

                    var progressToken = dojo.hasClass(e.target, 'progress_token') ? dojo.query(e.target) : dojo.query(e.target).closest(".progress_token");
                    var progressTokenId = progressToken.attr("data-progress-token-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseProgressToken.html", {
                            lock: true,
                            progressTokenId: progressTokenId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_progressTokenChosen: function (notif) {
                if (this.debug) console.log('notif_progressTokenChosen', notif);

                dojo.removeClass($('board_progress_tokens'), 'red_border');

                let container = this.getNewProgressTokenContainer(notif.args.playerId);
                var progressTokenNode = dojo.query("[data-progress-token-id=" + notif.args.progressTokenId + "]")[0];
                if (progressTokenNode) {
                    progressTokenNode = this.attachToNewParent(progressTokenNode, container);
                }
                else {
                    // In case of "Choose progress token from box", the inactive player doesn't have the chosen
                    // progress token on the screen yet, so we place the progress token on the Wonder.
                    progressTokenNode = dojo.place(this.getProgressTokenDivHtml(notif.args.progressTokenId), container);
                    if (notif.args.source == 'wonder') {
                        this.placeOnObject(progressTokenNode, 'wonder_6');
                    }
                    else if (notif.args.source == 'conspiracy') {
                        this.placeOnObject(progressTokenNode, dojo.query('.conspiracy_compact[data-conspiracy-id="10"]')[0]);
                    }
                }

                var anim = dojo.fx.chain([
                    this.slideToObjectPos(progressTokenNode, container, 0, 0, this.progressTokenDuration),
                    bgagame.CoinAnimator.get().getAnimation(
                        progressTokenNode.parentNode,
                        this.getPlayerCoinContainer(notif.args.playerId),
                        notif.args.payment.coinReward,
                        notif.args.playerId
                    ),
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                    anim.stop();
                    // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                    dojo.query("#swd_wrap .coin.animated").forEach(dojo.destroy);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                anim.play();
            },

            notif_nextAgeDraftpoolReveal: function (notif) {
                if (this.debug) console.log('notif_nextAgeDraftpoolReveal', notif);

                if (notif.args.offeringTokensSituation) this.updateOfferingTokensSituation(notif.args.offeringTokensSituation); // Sets this.gamedatas.offeringTokensSituation so it can be used in this.updateDraftpool
                var animationDuration = this.updateDraftpool(notif.args.draftpool, false, true);

                if (notif.args.divinitiesSituation) {
                    this.divinitiesRevealAnimation(notif.args.doorSpace, notif.args.divinitiesSituation);
                }

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(animationDuration);
            },

            getPlayerCoinContainer: function (playerId, oppositePlayerInstead = false) {
                var playerAlias = this.getPlayerAlias(oppositePlayerInstead ? this.getOppositePlayerId(playerId) : playerId);
                return dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0];
            },

            getPlayerCubeContainer: function (playerId, oppositePlayerInstead = false) {
                var playerAlias = this.getPlayerAlias(oppositePlayerInstead ? this.getOppositePlayerId(playerId) : playerId);
                return dojo.query('.player_info.' + playerAlias + ' .player_area_cubes')[0];
            },

            //   _   _           _        _
            //  | \ | | _____  _| |_     / \   __ _  ___
            //  |  \| |/ _ \ \/ / __|   / _ \ / _` |/ _ \
            //  | |\  |  __/>  <| |_   / ___ \ (_| |  __/
            //  |_| \_|\___/_/\_\\__| /_/   \_\__, |\___|
            //                                |___/

            notif_nextAge: function (notif) {
                if (this.debug) console.log('notif_nextAge', notif);
            },

            //  ____       _           _         _             _           _
            // / ___|  ___| | ___  ___| |_   ___| |_ __ _ _ __| |_   _ __ | | __ _ _   _  ___ _ __
            // \___ \ / _ \ |/ _ \/ __| __| / __| __/ _` | '__| __| | '_ \| |/ _` | | | |/ _ \ '__|
            //  ___) |  __/ |  __/ (__| |_  \__ \ || (_| | |  | |_  | |_) | | (_| | |_| |  __/ |
            // |____/ \___|_|\___|\___|\__| |___/\__\__,_|_|   \__| | .__/|_|\__,_|\__, |\___|_|
            //                                                      |_|            |___/
            onEnterSelectStartPlayer: function (args) {
                if (this.debug) console.log('onEnterSelectStartPlayer', args, 'ageRoman: ', args.ageRoman);
                $('select_start_player_text').innerText = dojo.string.substitute(_("You must choose who begins Age ${ageRoman}"), {
                    ageRoman: args.ageRoman
                });
            },

            onStartPlayerClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onStartPlayerClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionSelectStartPlayer')) {
                        return;
                    }

                    var playerId = dojo.attr(e.target, "data-player-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionSelectStartPlayer.html", {
                            lock: true,
                            playerId: playerId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            //   ____ _                               _              _   ____  _                  ____  _       _       _ _
            //  / ___| |__   ___   ___  ___  ___     / \   _ __   __| | |  _ \| | __ _  ___ ___  |  _ \(_)_   _(_)_ __ (_) |_ _   _
            // | |   | '_ \ / _ \ / _ \/ __|/ _ \   / _ \ | '_ \ / _` | | |_) | |/ _` |/ __/ _ \ | | | | \ \ / / | '_ \| | __| | | |
            // | |___| | | | (_) | (_) \__ \  __/  / ___ \| | | | (_| | |  __/| | (_| | (_|  __/ | |_| | |\ V /| | | | | | |_| |_| |
            //  \____|_| |_|\___/ \___/|___/\___| /_/   \_\_| |_|\__,_| |_|   |_|\__,_|\___\___| |____/|_| \_/ |_|_| |_|_|\__|\__, |
            //                                                                                                                |___/

            onEnterChooseAndPlaceDivinity: function (args) {
                if (this.debug) console.log('onEnterChooseAndPlaceDivinity', args);

                let anims = [];
                if (this.isCurrentPlayerActive()) {
                    let i = 1;
                    Object.keys(args._private.divinities).forEach(dojo.hitch(this, function (divinityId) {
                        var card = args._private.divinities[divinityId];
                        let divinity = this.gamedatas.divinities[divinityId];
                        var container = dojo.query('#choose_and_place_divinity>div:nth-of-type(' + (parseInt(card.location_arg) + 1) + ')')[0];
                        dojo.empty(container);
                        let divinityContainer = dojo.place(this.getDivinityDivHtml(divinityId, divinity.type,true), container);
                        let divinityNode = dojo.query('.divinity', divinityContainer)[0];
                        this.placeOnObject(divinityNode, $('mythology' + divinity.type));
                        anims.push(this.slideToObjectPos( divinityNode, divinityContainer, 0, 0, this.conspire_duration, i * this.conspire_duration / 3));
                        i++;
                    }));
                    this.updateLayout();
                }
                else {
                    for (let i = 0; i <= 1; i++) {
                        var container = dojo.query('.player_buildings.player' + this.getActivePlayerId())[0];
                        let node = dojo.place(this.getDivinityDivHtml(0, args.divinitiesType, true), container);
                        this.placeOnObject(node, $('mythology' + args.divinitiesType));
                        let startDelay = i * this.conspire_duration / 3;

                        anims.push(
                            dojo.fx.combine([
                                this.slideToObject( node, container, this.conspire_duration, i * this.conspire_duration / 3),
                                dojo.fadeIn({
                                    node: node,
                                    duration: this.conspire_duration / 3
                                }),
                                dojo.fadeOut({
                                    node: node,
                                    delay: startDelay + this.conspire_duration / 3 * 2,
                                    duration: this.conspire_duration / 3,
                                    onEnd: dojo.hitch(this, function (node) {
                                        dojo.destroy(node);
                                    })
                                }),
                            ])
                        );
                    }
                }
                let anim = dojo.fx.combine(
                    anims
                );
                anim.play();
            },

            onChooseDivinityClick: function (e) {
                if (this.debug) console.log('onChooseDivinityClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.isCurrentPlayerActive()) {
                    this.clearRedBorder();
                    this.clearGrayBorder();

                    var divinityNode = dojo.hasClass(e.target, 'divinity') ? e.target : dojo.query(e.target).closest(".divinity")[0];
                    dojo.addClass(divinityNode, 'gray_border');

                    dojo.query('#swd .pantheon_space_containers > div:empty').addClass('red_border');

                    this.chooseDivinityId = dojo.attr(divinityNode, 'data-divinity-id');
                    this.chooseDivinityNode = divinityNode;
                }
            },

            onPlaceDivinityClick: function (e) {
                if (this.debug) console.log('onPlaceDivinityClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.isCurrentPlayerActive()) {
                    let space = dojo.attr(e.target, 'data-space');

                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionChooseAndPlaceDivinity')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseAndPlaceDivinity.html", {
                            divinityId: this.chooseDivinityId,
                            space: space,
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_placeDivinity: function (notif) {
                if (this.debug) console.log('notif_placeDivinity', notif);

                let divinityId = notif.args.divinityId ? notif.args.divinityId : 0;
                let divinityType = notif.args.divinityType;
                let space = notif.args.space;

                // Skip for the active player, who will get their own notification of the same type but with a real divinityId.
                if (this.isCurrentPlayerActive() && divinityId == 0) {
                    this.notifqueue.setSynchronousDuration(0);
                    return;
                }

                let anims = [];
                let startSlideAfterFlipPercentage = 0.5;

                for (let iteration = 1; iteration <= 2; iteration++) {
                    let targetNode = null;
                    if (iteration == 1) {
                        var spaceNode = dojo.query('.pantheon_space_containers div[data-space=' + space + ']')[0];
                        var newDivinityContainerNode = dojo.place(this.getDivinityDivHtml(0, divinityType, false), spaceNode);
                        var newDivinityNode = dojo.query('.divinity_compact', newDivinityContainerNode)[0];
                        dojo.style(newDivinityContainerNode, 'opacity', 0);
                        // Start: Correct position to match Mythology logo of full and compact nodes.
                        dojo.style(newDivinityNode, 'top', 'calc(-192px * var(--scale))');
                        if (!this.isCurrentPlayerActive()) {
                            // Don't know why but this is neccessary to correct the position for the inactive player.
                            dojo.style(newDivinityNode, 'left', 'calc(16px * var(--scale))');
                        }
                        targetNode = newDivinityNode;
                    }
                    else if (iteration == 2) {
                        targetNode = dojo.query('#mythology_decks_container #mythology' + notif.args.divinityType + ' .divinity')[0];
                    }

                    this.autoUpdateScale();

                    let flipAnims = [];
                    let slideAnims = [];
                    var backSideDivinityContainerNode = null;
                    if (this.isCurrentPlayerActive()) {
                        this.clearGrayBorder();
                        this.clearRedBorder();

                        // For the active player, turn the card first so the backside is visible.
                        var oldDivinityNode = null;
                        if (iteration == 1) {
                            oldDivinityNode = dojo.query('#choose_and_place_divinity .divinity[data-divinity-id=' + divinityId + ']')[0];
                        }
                        else if (iteration == 2) {
                            oldDivinityNode = dojo.query('#choose_and_place_divinity .divinity:not([data-divinity-id=' + divinityId + ']):not([data-divinity-id=0])')[0];
                        }

                        backSideDivinityContainerNode = dojo.place(this.getDivinityDivHtml(0, divinityType, true), oldDivinityNode.parentElement);
                        let backSideDivinityNode = dojo.query('.divinity', backSideDivinityContainerNode)[0];

                        dojo.style(backSideDivinityNode, 'transform', 'perspective(40em) rotateY(-180deg)'); // When delay > 0 this is necesarry to hide the new node.

                        flipAnims.push(
                            dojo.fx.combine([
                                dojo.animateProperty({
                                    node: oldDivinityNode,
                                    duration: this.turnAroundCardDuration,
                                    easing: dojo.fx.easing.linear,
                                    properties: {
                                        propertyTransform: {start: 0, end: 180}
                                    },
                                    onAnimate: function (values) {
                                        dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                    },
                                    onEnd: dojo.hitch(this, function (node) {
                                        dojo.destroy(node);
                                    })
                                }),
                                dojo.animateProperty({
                                    node: backSideDivinityNode,
                                    duration: this.turnAroundCardDuration,
                                    easing: dojo.fx.easing.linear,
                                    properties: {
                                        propertyTransform: {start: -180, end: 0}
                                    },
                                    onAnimate: function (values) {
                                        dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                    }
                                }),
                            ])
                        );
                    } else {
                        // For the inactive player / spectator, fadein the backside of the divinity and slide towards the final place
                        var playerBuildingsContainer = dojo.query('.player_buildings.player' + this.getActivePlayerId())[0];
                        backSideDivinityContainerNode = dojo.place(this.getDivinityDivHtml(0, divinityType, true), dojo.query('#pantheon_board_container')[0]);
                        this.placeOnObject(backSideDivinityContainerNode, playerBuildingsContainer); // Center the card in the player buildingsContainer
                        dojo.style(backSideDivinityContainerNode, 'opacity', 0);

                        slideAnims.push(
                            dojo.fx.combine([
                                dojo.fadeIn({
                                    node: backSideDivinityContainerNode,
                                    delay: this.turnAroundCardDuration * startSlideAfterFlipPercentage,
                                    duration: this.place_divinity_duration / 4
                                }),
                            ])
                        );
                    }
                    dojo.style(backSideDivinityContainerNode, 'z-index', 100);

                    if (iteration == 1) {
                        slideAnims.push(this.slideToObject(backSideDivinityContainerNode, targetNode, this.place_divinity_duration, this.turnAroundCardDuration * startSlideAfterFlipPercentage));
                    }
                    else if (iteration == 2) {
                        slideAnims.push(this.slideToObjectPos(backSideDivinityContainerNode, targetNode, 0, 0, this.place_divinity_duration, this.turnAroundCardDuration * startSlideAfterFlipPercentage));
                    }

                    if (iteration == 1) {
                        // End: Correct position to match Mythology logo of full and compact nodes.
                        dojo.style(newDivinityNode, 'top', '0px');
                        dojo.style(newDivinityNode, 'left', '0px');

                        slideAnims.push(
                            dojo.animateProperty({
                                node: backSideDivinityContainerNode,
                                delay: this.turnAroundCardDuration * startSlideAfterFlipPercentage,
                                duration: this.place_divinity_duration,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: 0, end: this.getCurrentRotation(spaceNode)}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'rotate(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                }
                            })
                        );
                    }

                    let fadeOutParams = {
                        node: backSideDivinityContainerNode,
                        duration: this.place_divinity_duration / 4,
                        onEnd: dojo.hitch(this, function (node) {
                            dojo.destroy(node);
                        })
                    };
                    if (iteration == 1) {
                        fadeOutParams.onPlay = dojo.hitch(this, function (node) {
                            // Show newDivinityContainerNode fully, since it's behind the backSideDivinityNode we don't have to fade it.
                            dojo.style(newDivinityContainerNode, 'opacity', 1);
                        });
                    }
                    let fadeOutAnim = dojo.fadeOut(fadeOutParams);

                    anims.push(dojo.fx.chain([
                        dojo.fx.combine(
                            flipAnims.concat(slideAnims)
                        ),
                        fadeOutAnim
                    ]));
                }

                let anim = dojo.fx.chain(anims);
                anim.play();

                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);
            },

            getCurrentRotation: function (el){
                var st = window.getComputedStyle(el, null);
                var tm = st.getPropertyValue("-webkit-transform") ||
                    st.getPropertyValue("-moz-transform") ||
                    st.getPropertyValue("-ms-transform") ||
                    st.getPropertyValue("-o-transform") ||
                    st.getPropertyValue("transform") ||
                    "none";
                if (tm != "none") {
                    var values = tm.split('(')[1].split(')')[0].split(',');
                    var angle = Math.round(Math.atan2(values[1],values[0]) * (180/Math.PI));
                    return (angle < 0 ? angle + 360 : angle); //adding 360 degrees here when angle < 0 is equivalent to adding (2 * Math.PI) radians before
                }
                return 0;
            },

            //   ____ _                                                                      _        _                 __                       _
            //  / ___| |__   ___   ___  ___  ___   _ __  _ __ ___   __ _ _ __ ___  ___ ___  | |_ ___ | | _____ _ __    / _|_ __ ___  _ __ ___   | |__   _____  __
            // | |   | '_ \ / _ \ / _ \/ __|/ _ \ | '_ \| '__/ _ \ / _` | '__/ _ \/ __/ __| | __/ _ \| |/ / _ \ '_ \  | |_| '__/ _ \| '_ ` _ \  | '_ \ / _ \ \/ /
            // | |___| | | | (_) | (_) \__ \  __/ | |_) | | | (_) | (_| | | |  __/\__ \__ \ | || (_) |   <  __/ | | | |  _| | | (_) | | | | | | | |_) | (_) >  <
            //  \____|_| |_|\___/ \___/|___/\___| | .__/|_|  \___/ \__, |_|  \___||___/___/  \__\___/|_|\_\___|_| |_| |_| |_|  \___/|_| |_| |_| |_.__/ \___/_/\_\
            //                                    |_|              |___/

            onEnterChooseProgressTokenFromBox: function (args) {
                dojo.query('#progress_token_from_box h3')[0].innerHTML = _("Choose a Progress token from the box");

                if (this.debug) console.log('onEnterChooseProgressTokenFromBox', args);
                if (this.isCurrentPlayerActive()) {
                    dojo.query('#progress_token_from_box_container>div').style('display', 'none');
                    dojo.query('#progress_token_from_box_container>div').empty();
                    Object.keys(args._private.progressTokensFromBox).forEach(dojo.hitch(this, function (progressTokenId) {
                        var card = args._private.progressTokensFromBox[progressTokenId];
                        var container = dojo.query('#progress_token_from_box_container>div:nth-of-type(' + (parseInt(card.location_arg) + 1) + ')')[0];
                        dojo.style(container, 'display', 'inline-block');
                        dojo.place(this.getProgressTokenDivHtml(card.id), container);
                    }));
                }
            },

            onProgressTokenFromBoxClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onProgressTokenFromBoxClick', e);

                // if (this.isCurrentPlayerActive()) {
                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if (!this.checkAction('actionChooseProgressTokenFromBox')) {
                    return;
                }

                var progressToken = dojo.hasClass(e.target, 'progress_token') ? dojo.query(e.target) : dojo.query(e.target).closest(".progress_token");
                var progressTokenId = progressToken.attr("data-progress-token-id");

                this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseProgressTokenFromBox.html", {
                        lock: true,
                        progressTokenId: progressTokenId
                    },
                    this, function (result) {
                        // What to do after the server call if it succeeded
                        // (most of the time: nothing)

                    }, function (is_error) {
                        // What to do after the server call in anyway (success or failure)
                        // (most of the time: nothing)

                    }
                );
                // }
            },

            //   ____ _                             ____                      _           _                          _   _
            //  / ___| |__   ___   ___  ___  ___   / ___|___  _ __  ___ _ __ (_)_ __ __ _| |_ ___  _ __    __ _  ___| |_(_) ___  _ __
            // | |   | '_ \ / _ \ / _ \/ __|/ _ \ | |   / _ \| '_ \/ __| '_ \| | '__/ _` | __/ _ \| '__|  / _` |/ __| __| |/ _ \| '_ \
            // | |___| | | | (_) | (_) \__ \  __/ | |__| (_) | | | \__ \ |_) | | | | (_| | || (_) | |    | (_| | (__| |_| | (_) | | | |
            //  \____|_| |_|\___/ \___/|___/\___|  \____\___/|_| |_|___/ .__/|_|_|  \__,_|\__\___/|_|     \__,_|\___|\__|_|\___/|_| |_|
            //                                                         |_|

            onEnterChooseConspiratorAction: function (args) {
                if (this.debug) console.log('onEnterChooseConspiratorAction', args);
                if (this.isCurrentPlayerActive()) {
                    let canPlace = parseInt(args.playersSituation[this.me_id].cubes) > 0;
                    dojo.query('#buttonPlaceInfluence').toggleClass('bgabutton_blue', canPlace);
                    dojo.query('#buttonPlaceInfluence').toggleClass('bgabutton_darkgray', !canPlace);
                }
            },

            onChooseConspiratorActionClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onChooseConspiratorActionClick', e);

                if (this.isCurrentPlayerActive()) {
                    switch (e.target.id) {
                        case "buttonConspire":
                            // Check that this action is possible (see "possibleactions" in states.inc.php)
                            if (!this.checkAction('actionConspire')) {
                                return;
                            }

                            this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionConspire.html", {
                                    lock: true
                                },
                                this, function (result) {
                                    // What to do after the server call if it succeeded
                                    // (most of the time: nothing)

                                }, function (is_error) {
                                    // What to do after the server call in anyway (success or failure)
                                    // (most of the time: nothing)

                                }
                            );
                            break;
                        case "buttonPlaceInfluence":
                            // Check that this action is possible (see "possibleactions" in states.inc.php)
                            if (!this.checkAction('actionChooseConspiratorActionPlaceInfluence')) {
                                return;
                            }

                            this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseConspiratorActionPlaceInfluence.html", {
                                    lock: true
                                },
                                this, function (result) {
                                    // What to do after the server call if it succeeded
                                    // (most of the time: nothing)

                                }, function (is_error) {
                                    // What to do after the server call in anyway (success or failure)
                                    // (most of the time: nothing)

                                }
                            );
                            break;
                    }

                }
            },

            //   ____                      _
            //  / ___|___  _ __  ___ _ __ (_)_ __ ___
            // | |   / _ \| '_ \/ __| '_ \| | '__/ _ \
            // | |__| (_) | | | \__ \ |_) | | | |  __/
            //  \____\___/|_| |_|___/ .__/|_|_|  \___|
            //                      |_|

            onEnterConspire: function (args) {
                if (this.debug) console.log('onEnterConspire', args);

                let anims = [];
                if (this.isCurrentPlayerActive()) {
                    let i = 1;
                    Object.keys(args._private.conspiracies).forEach(dojo.hitch(this, function (conspiracyId) {
                        var card = args._private.conspiracies[conspiracyId];
                        var container = dojo.query('#conspire>div:nth-of-type(' + i + ')')[0];
                        dojo.empty(container);
                        let node = dojo.place(this.getConspiracyDivHtml(conspiracyId, conspiracyId,true), container);
                        this.placeOnObject(node, $('conspiracy_deck'));
                        anims.push(this.slideToObject( node, container, this.conspire_duration, i * this.conspire_duration / 3));
                        i++;
                    }));
                    this.updateLayout();
                }
                else {
                    for (let i = 0; i <= 1; i++) {
                        var container = dojo.query('.player_buildings.player' + this.getActivePlayerId())[0];
                        let node = dojo.place(this.getConspiracyDivHtml(18, 18, true), container);
                        this.placeOnObject(node, $('conspiracy_deck'));
                        let startDelay = i * this.conspire_duration / 3;

                        anims.push(
                            dojo.fx.combine([
                                this.slideToObject( node, container, this.conspire_duration, i * this.conspire_duration / 3),
                                dojo.fadeIn({
                                    node: node,
                                    duration: this.conspire_duration / 3
                                }),
                                dojo.fadeOut({
                                    node: node,
                                    delay: startDelay + this.conspire_duration / 3 * 2,
                                    duration: this.conspire_duration / 3,
                                    onEnd: dojo.hitch(this, function (node) {
                                        dojo.destroy(node);
                                    })
                                }),
                            ])
                        );
                    }
                }
                let anim = dojo.fx.combine(
                    anims
                );
                anim.play();
            },

            onChooseConspiracyClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onChooseConspiratorActionClick', e);

                var conspiracy = dojo.hasClass(e.target, 'conspiracy') ? dojo.query(e.target) : dojo.query(e.target).closest(".conspiracy");
                var conspiracyId = conspiracy.attr("data-conspiracy-id");

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionChooseConspiracy')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseConspiracy.html", {
                            conspiracyId: conspiracyId,
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_conspireKeepBoth: function (notif) {
                if (this.debug) console.log('notif_conspireKeepBoth', notif);

                let conspiracyIds = notif.args.conspiracyIds;

                // Skip for the active player, who will get their own notification of the same type but with a real progressTokenId.
                if (this.isCurrentPlayerActive() && conspiracyIds[0] == 17) {
                    this.notifqueue.setSynchronousDuration(0);
                    return;
                }

                // First create the new conspiracies and update the scale, if we don't do this the animations won't have their target position correct.
                let newContainerNodes = [];
                for (let index = 0; index <= 1; index++) {
                    newContainerNodes[index] = dojo.place(this.getConspiracyDivHtml(conspiracyIds[index], 18, false), 'player_conspiracies_' + this.getActivePlayerId());
                }
                this.autoUpdateScale();

                let conspiracyAnims = [];
                for (let index = 0; index <= 1; index++) {
                    let delay = 100 + index * 500;
                    let conspiracyId = conspiracyIds[index];

                    let newConspiracyContainerNode = newContainerNodes[index];
                    let newConspiracyNode = dojo.query('.conspiracy_compact', newConspiracyContainerNode)[0];
                    dojo.style(newConspiracyContainerNode, 'opacity', 0);

                    let playerBuildingsContainer = dojo.query('.player_buildings.player' + this.getActivePlayerId())[0];
                    let backSideConspiracyNode = dojo.place(this.getConspiracyDivHtml(17, 17, true), playerBuildingsContainer);
                    dojo.style(backSideConspiracyNode, 'z-index', 50);
                    this.placeOnObject( backSideConspiracyNode, $('conspiracy_deck') ); // Center the card in the player buildingsContainer

                    let slideAnim = this.slideToObjectPos( backSideConspiracyNode, newConspiracyNode, 0, 0, this.construct_conspiracy_duration, delay);

                    conspiracyAnims.push(
                        dojo.fx.chain([
                            slideAnim,
                            dojo.fx.combine([
                                dojo.fadeOut({
                                    node: backSideConspiracyNode,
                                    duration: this.construct_conspiracy_duration / 4,
                                    onPlay: dojo.hitch(this, function (node) {
                                        // Show newConspiracyContainerNode fully, since it's behind the backSideConspiracyNode we don't have to fade it.
                                        dojo.style(newConspiracyContainerNode, 'opacity', 1);
                                    }),
                                    onEnd: dojo.hitch(this, function (node) {
                                        dojo.destroy(node);
                                    })
                                })
                            ])
                        ])
                    );
                }

                let anim = dojo.fx.chain([
                    dojo.fx.combine(
                        conspiracyAnims
                    )
                ]);

                anim.play();

                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);
            },

            notif_constructConspiracy: function (notif) {
                if (this.debug) console.log('notif_constructConspiracy', notif);

                let conspiracyId = notif.args.conspiracyId ? notif.args.conspiracyId : 18;

                // Skip for the active player, who will get their own notification of the same type but with a real conspiracyId.
                if (this.isCurrentPlayerActive() && conspiracyId == 18) {
                    this.notifqueue.setSynchronousDuration(0);
                    return;
                }

                var newConspiracyContainerNode = dojo.place(this.getConspiracyDivHtml(conspiracyId, 18, false, notif.args.conspiracyPosition), 'player_conspiracies_' + notif.args.playerId);
                var newConspiracyNode = dojo.query('.conspiracy_compact', newConspiracyContainerNode)[0];
                dojo.style(newConspiracyContainerNode, 'opacity', 0);
                this.autoUpdateScale();

                let flipAnims = [];
                let slideAnims = [];
                var backSideConspiracyNode = null;
                if (this.isCurrentPlayerActive()) {
                    // For the active player, turn the card first so the backside is visible.
                    var oldConspiracyNode = dojo.query('#conspire #conspiracy_' + conspiracyId)[0];

                    backSideConspiracyNode = dojo.place(this.getConspiracyDivHtml(17, 17, true), oldConspiracyNode.parentElement);

                    dojo.style(backSideConspiracyNode, 'transform', 'perspective(40em) rotateY(-180deg)'); // When delay > 0 this is necesarry to hide the new node.

                    flipAnims.push(
                        dojo.fx.combine([
                            dojo.animateProperty({
                                node: oldConspiracyNode,
                                delay: 100,
                                duration: this.turnAroundCardDuration,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: 0, end: 180}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                },
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                })
                            }),
                            dojo.animateProperty({
                                node: backSideConspiracyNode,
                                delay: 100,
                                duration: this.turnAroundCardDuration,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: -180, end: 0}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                }
                            }),
                        ])
                    );

                    slideAnims.push(this.slideToObjectPos( backSideConspiracyNode, newConspiracyNode, 0, 0, this.construct_conspiracy_duration));
                }
                else {
                    // For the inactive player / spectator, fadein the backside of the conspiracy and slide towards the final place
                    var playerBuildingsContainer = dojo.query('.player_buildings.player' + this.getActivePlayerId())[0];
                    backSideConspiracyNode = dojo.place(this.getConspiracyDivHtml(17, 17, true), playerBuildingsContainer);
                    this.placeOnObject( backSideConspiracyNode, playerBuildingsContainer ); // Center the card in the player buildingsContainer
                    dojo.style(backSideConspiracyNode, 'opacity', 0);

                    slideAnims.push(
                        dojo.fx.combine([
                            this.slideToObjectPos( backSideConspiracyNode, newConspiracyNode, 0, 0, this.construct_conspiracy_duration),
                            dojo.fadeIn({
                                node: backSideConspiracyNode,
                                duration: this.construct_conspiracy_duration / 4
                            }),
                        ])
                    );
                }

                let anim = dojo.fx.chain([
                    dojo.fx.combine(
                        flipAnims
                    ),
                    dojo.fx.combine(
                        slideAnims
                    ),
                    dojo.fx.combine([
                        dojo.fadeOut({
                            node: backSideConspiracyNode,
                            duration: this.construct_conspiracy_duration / 4,
                            onPlay: dojo.hitch(this, function (node) {
                                // Show newConspiracyContainerNode fully, since it's behind the backSideConspiracyNode we don't have to fade it.
                                dojo.style(newConspiracyContainerNode, 'opacity', 1);
                            }),
                            onEnd: dojo.hitch(this, function (node) {
                                dojo.destroy(node);
                            })
                        })
                    ])
                ]);
                anim.play();

                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);
            },

            //   ____ _                             ____                      _            ____                                  _     ____           _ _   _
            //  / ___| |__   ___   ___  ___  ___   / ___|___  _ __  ___ _ __ (_)_ __ ___  |  _ \ ___ _ __ ___  _ __   __ _ _ __ | |_  |  _ \ ___  ___(_) |_(_) ___  _ __
            // | |   | '_ \ / _ \ / _ \/ __|/ _ \ | |   / _ \| '_ \/ __| '_ \| | '__/ _ \ | |_) / _ \ '_ ` _ \| '_ \ / _` | '_ \| __| | |_) / _ \/ __| | __| |/ _ \| '_ \
            // | |___| | | | (_) | (_) \__ \  __/ | |__| (_) | | | \__ \ |_) | | | |  __/ |  _ <  __/ | | | | | | | | (_| | | | | |_  |  __/ (_) \__ \ | |_| | (_) | | | |
            //  \____|_| |_|\___/ \___/|___/\___|  \____\___/|_| |_|___/ .__/|_|_|  \___| |_| \_\___|_| |_| |_|_| |_|\__,_|_| |_|\__| |_|   \___/|___/_|\__|_|\___/|_| |_|
            //                                                         |_|

            onEnterChooseConspireRemnantPosition: function (args) {
                if (this.debug) console.log('onEnterChooseConspireRemnantPosition', args);
                if (this.isCurrentPlayerActive()) {
                    var container = dojo.query('#conspire>div:nth-of-type(1)')[0];
                    dojo.empty(container);
                    dojo.place(this.getConspiracyDivHtml(args._private.conspiracyId, args._private.conspiracyId, true), container);
                }
            },

            onChooseConspireRemnantPositionClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onChooseConspireRemnantPositionClick', e);

                var top = (e.target.id == 'buttonConspiracyRemnantTop') ? 1 : 0;

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionChooseConspireRemnantPosition')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionChooseConspireRemnantPosition.html", {
                            top: top,
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_chooseConspireRemnantPosition: function (notif) {
                if (this.debug) console.log('notif_constructConspiracy', notif);

                let conspiracyId = notif.args.conspiracyId ? notif.args.conspiracyId : 18;

                let flipAnims = [];
                let slideAnims = [];
                var backSideConspiracyNode = null;
                if (this.isCurrentPlayerActive()) {
                    // For the active player, turn the card first so the backside is visible.
                    var oldConspiracyNode = dojo.query('#conspire .conspiracy_small')[0];

                    backSideConspiracyNode = dojo.place(this.getConspiracyDivHtml(17, 17, true), oldConspiracyNode.parentElement);

                    dojo.style(backSideConspiracyNode, 'transform', 'perspective(40em) rotateY(-180deg)'); // When delay > 0 this is necesarry to hide the new node.
                    dojo.style(backSideConspiracyNode, 'z-index', '50');

                    flipAnims.push(
                        dojo.fx.combine([
                            dojo.animateProperty({
                                node: oldConspiracyNode,
                                delay: 100,
                                duration: this.turnAroundCardDuration,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: 0, end: 180}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                },
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                })
                            }),
                            dojo.animateProperty({
                                node: backSideConspiracyNode,
                                delay: 100,
                                duration: this.turnAroundCardDuration,
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    propertyTransform: {start: -180, end: 0}
                                },
                                onAnimate: function (values) {
                                    dojo.style(this.node, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                }
                            }),
                        ])
                    );

                    slideAnims.push(this.slideToObject( backSideConspiracyNode, 'conspiracy_deck', this.choose_conspire_remnant_position_duration));
                }
                else {
                    // For the inactive player / spectator, fadein the backside of the conspiracy and slide towards the final place
                    var playerBuildingsContainer = dojo.query('.player_buildings.player' + this.getActivePlayerId())[0];
                    backSideConspiracyNode = dojo.place(this.getConspiracyDivHtml(17, 17, true), playerBuildingsContainer);

                    let deckPositionNode = dojo.query('.deck_position', backSideConspiracyNode)[0];
                    deckPositionNode.innerHTML = notif.args.onTop ? _('To the top') : _('To the bottom');
                    dojo.style(deckPositionNode, 'display', 'inline-block');

                    this.placeOnObject( backSideConspiracyNode, playerBuildingsContainer ); // Center the card in the player buildingsContainer
                    dojo.style(backSideConspiracyNode, 'opacity', 0);
                    dojo.style(backSideConspiracyNode, 'z-index', '50');

                    slideAnims.push(
                        dojo.fx.chain([
                            dojo.fadeIn({
                                node: backSideConspiracyNode,
                                duration: this.choose_conspire_remnant_position_duration / 4
                            }),
                            this.slideToObject( backSideConspiracyNode, 'conspiracy_deck', this.choose_conspire_remnant_position_duration / 4 * 3, this.choose_conspire_remnant_position_duration / 4),
                        ])
                    );
                }

                let anim = dojo.fx.chain([
                    dojo.fx.combine(
                        flipAnims
                    ),
                    dojo.fx.combine(
                        slideAnims
                    )
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    dojo.destroy(backSideConspiracyNode);
                }));

                anim.play();

                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);
            },

            //  ____                   _            _        _   _
            // / ___|  ___ _ __   __ _| |_ ___     / \   ___| |_(_) ___  _ __  ___
            // \___ \ / _ \ '_ \ / _` | __/ _ \   / _ \ / __| __| |/ _ \| '_ \/ __|
            //  ___) |  __/ | | | (_| | ||  __/  / ___ \ (__| |_| | (_) | | | \__ \
            // |____/ \___|_| |_|\__,_|\__\___| /_/   \_\___|\__|_|\___/|_| |_|___/

            onEnterSenateActions: function (args) {
                if (this.debug) console.log('onEnterSenateActions', args);
                if (this.isCurrentPlayerActive()) {
                    this.senateActionsSection = parseInt(args.senateActionsSection);

                    let canPlace = parseInt(args.playersSituation[this.me_id].cubes) > 0;
                    dojo.query('#buttonSenateActionsPlaceInfluence').toggleClass('bgabutton_blue', canPlace);
                    dojo.query('#buttonSenateActionsPlaceInfluence').toggleClass('bgabutton_darkgray', !canPlace);
                    let canMove = parseInt(args.playersSituation[this.me_id].cubes) < 12;
                    dojo.query('#buttonSenateActionsMoveInfluence').toggleClass('bgabutton_blue', canMove);
                    dojo.query('#buttonSenateActionsMoveInfluence').toggleClass('bgabutton_darkgray', !canMove);
                }
            },

            onSenateActionsPlaceInfluenceButtonClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onSenateActionsPlaceInfluenceButtonClick');

                if (this.isCurrentPlayerActive()) {
                    this.setClientState("client_placeInfluence", {
                        descriptionmyturn: _("${you} select a Senate chamber to place an Influence cube, or select a different action"),
                    });
                    this.markSection(this.senateActionsSection);
                }
            },

            onSenateActionsMoveInfluenceButtonClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onSenateActionsPlaceInfluenceButtonClick');

                if (this.isCurrentPlayerActive()) {
                    this.selectMoveInfluenceMode();
                }
            },

            updateSenateChamber: function(chamber, data) {
                if (this.debug) console.log('updateSenateChamber', data);
                // parseInt(data[this.me_id])
                // parseInt(data[this.opponent_id])
                // parseInt(data.controller)
                let container = dojo.query('.influence_containers>div:nth-of-type(' + chamber + ')')[0];
                for (let player_id of Object.values([this.me_id, this.opponent_id])) {
                    let node = dojo.query('.player' + player_id, container)[0];
                    dojo.query('span', node)[0].innerHTML = data[player_id];
                    dojo.style(node, 'opacity', data[player_id] > 0 ? '1' : '0');
                    dojo.toggleClass(node, 'agora_control' , data.controller && player_id == parseInt(data.controller));
                }

                if (data.revealDecrees) {
                    for (let i = 0; i < data.revealDecrees.length; i++) {
                        let decreeData = data.revealDecrees[i];

                        let oldNode = this.getDecreeNode(decreeData.position);
                        let newNode = this.placeDecree(parseInt(decreeData.id), decreeData.position);
                        this.twistAnimation(oldNode, newNode);
                    }
                }
            },

            markSection(section) {
                let chamberStart = (section * 2) - 1;
                this.markChambers([chamberStart, chamberStart + 1]);
            },

            markChambers(redChambers, greenChambers = []) {
                for (let chamber = 1; chamber <= 6; chamber++) {
                    let node = $('chamber' + chamber);
                    // dojo.addClass doesn't work for path/svg, so we use setAttribute
                    node.setAttribute("class", "");
                    node.setAttribute("data-stroke", "");
                    if (redChambers.indexOf(chamber) > -1) {
                        node.setAttribute("class",  "red_stroke");
                        node.setAttribute("data-stroke", "red");
                    }
                    else if (greenChambers.indexOf(chamber) > -1) {
                        node.setAttribute("class",  "gray_stroke");
                        node.setAttribute("data-stroke", "gray");
                    }

                    // Re-add element to DOM, this way the animations are all in sync (else if an element previously had red_stroke it will be out of sync).
                    var nodeCopy = node.cloneNode(true);
                    node.parentNode.replaceChild(nodeCopy, node);
                }
            },

            getChambersWithPlayerInfluenceCubes: function(player_id) {
                let returnChambers = [];
                Object.keys(this.gamedatas.senateSituation.chambers).forEach(dojo.hitch(this, function (chamber) {
                    var chamberData = this.gamedatas.senateSituation.chambers[chamber];
                    if (chamberData[player_id] > 0) {
                        returnChambers.push(parseInt(chamber));
                    }
                }));
                return returnChambers;
            },

            //     _        _   _               ____  _                  ___        __ _
            //    / \   ___| |_(_) ___  _ __   |  _ \| | __ _  ___ ___  |_ _|_ __  / _| |_   _  ___ _ __   ___ ___
            //   / _ \ / __| __| |/ _ \| '_ \  | |_) | |/ _` |/ __/ _ \  | || '_ \| |_| | | | |/ _ \ '_ \ / __/ _ \
            //  / ___ \ (__| |_| | (_) | | | | |  __/| | (_| | (_|  __/  | || | | |  _| | |_| |  __/ | | | (_|  __/
            // /_/   \_\___|\__|_|\___/|_| |_| |_|   |_|\__,_|\___\___| |___|_| |_|_| |_|\__,_|\___|_| |_|\___\___|

            onEnterPlaceInfluence: function (args) {
                if (this.debug) console.log('onEnterPlaceInfluence', args);
                if (this.isCurrentPlayerActive()) {
                    this.markChambers([1,2,3,4,5,6]);
                }
            },

            onPlaceInfluenceClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlaceInfluenceClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionPlaceInfluence')) {
                        return;
                    }

                    var chamber = dojo.attr(e.target, "data-chamber");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionPlaceInfluence.html", {
                            chamber: chamber,
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_placeInfluence: function (notif) {
                if (this.debug) console.log('notif_placeInfluence', notif);

                this.markChambers([]);

                var anim = dojo.fx.chain([
                    // Influence cube
                    bgagame.CubeAnimator.get().getAnimation(
                        this.getPlayerCubeContainer(notif.args.playerId),
                        dojo.query('.influence_containers div:nth-of-type(' + notif.args.chamber + ') .agora_cube.player'+notif.args.playerId)[0],
                        1,
                        notif.args.playerId
                    ),
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                    anim.stop();
                    // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                    dojo.query("#swd_wrap .agora_cube.animated").forEach(dojo.destroy);

                    // dojo.style(playerBuildingId, 'z-index', 15);

                    Object.keys(notif.args.senateAction.chambers).forEach(dojo.hitch(this, function (chamber) {
                        var chamberData = notif.args.senateAction.chambers[chamber];
                        this.updateSenateChamber(chamber, chamberData);
                    }));
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.twistCoinDuration + this.notification_safe_margin);

                anim.play();
            },

            //     _        _   _               __  __                  ___        __ _
            //    / \   ___| |_(_) ___  _ __   |  \/  | _____   _____  |_ _|_ __  / _| |_   _  ___ _ __   ___ ___
            //   / _ \ / __| __| |/ _ \| '_ \  | |\/| |/ _ \ \ / / _ \  | || '_ \| |_| | | | |/ _ \ '_ \ / __/ _ \
            //  / ___ \ (__| |_| | (_) | | | | | |  | | (_) \ V /  __/  | || | | |  _| | |_| |  __/ | | | (_|  __/
            // /_/   \_\___|\__|_|\___/|_| |_| |_|  |_|\___/ \_/ \___| |___|_| |_|_| |_|\__,_|\___|_| |_|\___\___|

            onEnterMoveInfluence: function (args) {
                if (this.debug) console.log('onEnterMoveInfluence', args);
                if (this.isCurrentPlayerActive()) {
                    this.setClientState("client_moveInfluenceFrom");

                    this.markChambers(this.getChambersWithPlayerInfluenceCubes(this.me_id));
                }
            },

            selectMoveInfluenceMode: function() {
                this.setClientState("client_moveInfluenceFrom", {
                    descriptionmyturn: _("${you} select a Senate chamber to move an Influence cube from, or select a different action"),
                });

                this.markChambers(this.getChambersWithPlayerInfluenceCubes(this.me_id));
            },

            onMoveInfluenceFromClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onMoveInfluenceFromClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionMoveInfluence')) {
                        return;
                    }

                    this.setClientState("client_moveInfluenceTo", {
                        descriptionmyturn: _("${you} select a Senate chamber to move the Influence cube to, or select a different action"),
                    });

                    let chamber = parseInt(dojo.attr(e.target, "data-chamber"));
                    let toChambers = [];
                    for (let i = Math.max(1, chamber-1); i <= Math.min(6, chamber + 1); i++) {
                        if (i != chamber) {
                            toChambers.push(i);
                        }
                    }
                    this.markChambers(toChambers, [chamber]);
                    this.moveInfluenceFrom = chamber;

                }
            },

            onMoveInfluenceCancelClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onMoveInfluenceCancelClick');

                if (this.isCurrentPlayerActive()) {
                    this.selectMoveInfluenceMode();
                }
            },

            onMoveInfluenceToClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onMoveInfluenceToClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionMoveInfluence')) {
                        return;
                    }

                    var chamber = dojo.attr(e.target, "data-chamber");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionMoveInfluence.html", {
                            chamberFrom: this.moveInfluenceFrom,
                            chamberTo: chamber,
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_moveInfluence: function (notif) {
                if (this.debug) console.log('notif_moveInfluence', notif);

                this.markChambers([]);

                var anim = dojo.fx.chain([
                    // Influence cube
                    bgagame.CubeAnimator.get().getAnimation(
                        dojo.query('.influence_containers div:nth-of-type(' + notif.args.chamberFrom + ') .agora_cube.player'+notif.args.playerId)[0],
                        dojo.query('.influence_containers div:nth-of-type(' + notif.args.chamberTo + ') .agora_cube.player'+notif.args.playerId)[0],
                        1,
                        notif.args.playerId
                    ),
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                    anim.stop();

                    Object.keys(notif.args.senateAction.chambers).forEach(dojo.hitch(this, function (chamber) {
                        var chamberData = notif.args.senateAction.chambers[chamber];
                        this.updateSenateChamber(chamber, chamberData);
                    }));

                    // Clean up any existing coin nodes (normally cleaned up by their onEnd).
                    dojo.query("#swd_wrap .agora_cube.animated").forEach(dojo.destroy);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.twistCoinDuration + this.notification_safe_margin);

                anim.play();
            },

            onSenateActionsSkipButtonClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onSenateActionsSkipButtonClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionSkipMoveInfluence')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionSkipMoveInfluence.html", {
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            this.markChambers([]);

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            //     _        _   _               ____                                 ___        __ _
            //    / \   ___| |_(_) ___  _ __   |  _ \ ___ _ __ ___   _____   _____  |_ _|_ __  / _| |_   _  ___ _ __   ___ ___
            //   / _ \ / __| __| |/ _ \| '_ \  | |_) / _ \ '_ ` _ \ / _ \ \ / / _ \  | || '_ \| |_| | | | |/ _ \ '_ \ / __/ _ \
            //  / ___ \ (__| |_| | (_) | | | | |  _ <  __/ | | | | | (_) \ V /  __/  | || | | |  _| | |_| |  __/ | | | (_|  __/
            // /_/   \_\___|\__|_|\___/|_| |_| |_| \_\___|_| |_| |_|\___/ \_/ \___| |___|_| |_|_| |_|\__,_|\___|_| |_|\___\___|

            onEnterRemoveInfluence: function (args) {
                if (this.debug) console.log('onEnterRemoveInfluence', args);
                if (this.isCurrentPlayerActive()) {
                    this.markChambers(this.getChambersWithPlayerInfluenceCubes(this.opponent_id));
                }
            },

            onRemoveInfluenceClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onRemoveInfluenceClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionRemoveInfluence')) {
                        return;
                    }

                    var chamber = dojo.attr(e.target, "data-chamber");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionRemoveInfluence.html", {
                            chamber: chamber,
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_removeInfluence: function (notif) {
                if (this.debug) console.log('notif_removeInfluence', notif);

                this.markChambers([]);

                var anim = dojo.fx.chain([
                    // Influence cube
                    bgagame.CubeAnimator.get().getAnimation(
                        dojo.query('.influence_containers div:nth-of-type(' + notif.args.chamber + ') .agora_cube.player' + notif.args.opponentId)[0],
                        this.getPlayerCubeContainer(notif.args.opponentId),
                        1,
                        notif.args.opponentId
                    ),
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayersSituation.
                    anim.stop();
                    // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                    dojo.query("#swd_wrap .agora_cube.animated").forEach(dojo.destroy);

                    // dojo.style(playerBuildingId, 'z-index', 15);

                    Object.keys(notif.args.senateAction.chambers).forEach(dojo.hitch(this, function (chamber) {
                        var chamberData = notif.args.senateAction.chambers[chamber];
                        this.updateSenateChamber(chamber, chamberData);
                    }));
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.twistCoinDuration + this.notification_safe_margin);

                anim.play();
            },

            //  _____     _                         _   _                                               _    ____                      _
            // |_   _| __(_) __ _  __ _  ___ _ __  | | | |_ __  _ __  _ __ ___ _ __   __ _ _ __ ___  __| |  / ___|___  _ __  ___ _ __ (_)_ __ __ _  ___ _   _
            //   | || '__| |/ _` |/ _` |/ _ \ '__| | | | | '_ \| '_ \| '__/ _ \ '_ \ / _` | '__/ _ \/ _` | | |   / _ \| '_ \/ __| '_ \| | '__/ _` |/ __| | | |
            //   | || |  | | (_| | (_| |  __/ |    | |_| | | | | |_) | | |  __/ |_) | (_| | | |  __/ (_| | | |__| (_) | | | \__ \ |_) | | | | (_| | (__| |_| |
            //   |_||_|  |_|\__, |\__, |\___|_|     \___/|_| |_| .__/|_|  \___| .__/ \__,_|_|  \___|\__,_|  \____\___/|_| |_|___/ .__/|_|_|  \__,_|\___|\__, |
            //              |___/ |___/                        |_|            |_|                                               |_|                     |___/

            onEnterTriggerUnpreparedConspiracy: function() {
                if (this.isCurrentPlayerActive()) {
                    Object.keys(this.gamedatas.conspiraciesSituation[this.player_id]).forEach(dojo.hitch(this, function (index) {
                        var conspiracyData = this.gamedatas.conspiraciesSituation[this.player_id][index];
                        if (!conspiracyData.prepared && !conspiracyData.triggered && conspiracyData.useful) {
                            let conspiracyNode = dojo.query('#player_conspiracies_' + this.player_id + ' div[data-conspiracy-position="' + conspiracyData.position + '"]')[0];
                            dojo.addClass(conspiracyNode, 'green_border');
                        }
                    }));
                }
            },

            onPlayerTurnSkipTriggerConspiracyClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onPlayerTurnSkipTriggerConspiracyClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionSkipTriggerUnpreparedConspiracy')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionSkipTriggerUnpreparedConspiracy.html", {
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            //   ____                _                   _     ____        _ _     _ _               _____                      ____
            //  / ___|___  _ __  ___| |_ _ __ _   _  ___| |_  | __ ) _   _(_) | __| (_)_ __   __ _  |  ___| __ ___  _ __ ___   | __ )  _____  __
            // | |   / _ \| '_ \/ __| __| '__| | | |/ __| __| |  _ \| | | | | |/ _` | | '_ \ / _` | | |_ | '__/ _ \| '_ ` _ \  |  _ \ / _ \ \/ /
            // | |__| (_) | | | \__ \ |_| |  | |_| | (__| |_  | |_) | |_| | | | (_| | | | | | (_| | |  _|| | | (_) | | | | | | | |_) | (_) >  <
            //  \____\___/|_| |_|___/\__|_|   \__,_|\___|\__| |____/ \__,_|_|_|\__,_|_|_| |_|\__, | |_|  |_|  \___/|_| |_| |_| |____/ \___/_/\_\
            //                                                                               |___/

            onEnterConstructBuildingFromBox: function (args) {
                dojo.query('#construct_building_from_box td.td_margin_right').removeClass("td_margin_right");
                if (args._private && args._private.buildingsFromBox) {
                    for (let age = 1; age <= 3; age++) {
                        if (args._private.buildingsFromBox[age]) {
                            Object.keys(args._private.buildingsFromBox[age]).forEach(dojo.hitch(this, function (index) {
                                var building = this.gamedatas.buildings[args._private.buildingsFromBox[age][index]];

                                // Set up a wrapper div so we can move the building to pos 0,0 of that wrapper
                                let container = dojo.query('#construct_building_from_box td[data-construct-building-from-box-age="' + age + '"]')[0];
                                if (args._private.buildingsFromBox[age + 1]) {
                                    dojo.addClass(container, 'td_margin_right');
                                }

                                var wrapperDiv = dojo.clone(dojo.query('.discarded_cards_cursor')[0]);
                                dojo.removeClass(wrapperDiv, 'discarded_cards_cursor');
                                dojo.place(wrapperDiv, container);

                                let newNode = this.getDiscardedBuildingNode(building.id);
                                newNode.style.top = '0px';
                                newNode.style.left = '0px';
                                dojo.place(newNode, wrapperDiv);
                            }));
                        }
                        else {
                            dojo.query('#construct_building_from_box .age' + age).style('display', 'none');
                        }
                    }
                }
            },

            onConstructBuildingFromBoxClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onConstructBuildingFromBoxClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionConstructBuildingFromBox')) {
                        return;
                    }

                    var buildingId = dojo.attr(e.target, "data-building-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionConstructBuildingFromBox.html", {
                            lock: true,
                            buildingId: buildingId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            //   ____                _                   _     _              _     ____                 ____        _ _     _ _
            //  / ___|___  _ __  ___| |_ _ __ _   _  ___| |_  | |    __ _ ___| |_  |  _ \ _____      __ | __ ) _   _(_) | __| (_)_ __   __ _
            // | |   / _ \| '_ \/ __| __| '__| | | |/ __| __| | |   / _` / __| __| | |_) / _ \ \ /\ / / |  _ \| | | | | |/ _` | | '_ \ / _` |
            // | |__| (_) | | | \__ \ |_| |  | |_| | (__| |_  | |__| (_| \__ \ |_  |  _ < (_) \ V  V /  | |_) | |_| | | | (_| | | | | | (_| |
            //  \____\___/|_| |_|___/\__|_|   \__,_|\___|\__| |_____\__,_|___/\__| |_| \_\___/ \_/\_/   |____/ \__,_|_|_|\__,_|_|_| |_|\__, |
            //                                                                                                                         |___/

            onEnterConstructLastRowBuilding: function (args) {
                dojo.query('#draftpool .row1:not([data-building-type="Senator"])').addClass('red_border');
            },

            onConstructLastRowBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onConstructLastRowBuildingClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionConstructBuilding')) {
                        return;
                    }

                    var building = dojo.hasClass(e.target, 'building') ? e.target : dojo.query(e.target).closest(".building")[0];
                    var buildingId = dojo.attr(building, "data-building-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionConstructBuilding.html", {
                            lock: true,
                            buildingId: buildingId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            //  ____            _                      ____                _                   _           _  __        __              _
            // |  _ \  ___  ___| |_ _ __ ___  _   _   / ___|___  _ __  ___| |_ _ __ _   _  ___| |_ ___  __| | \ \      / /__  _ __   __| | ___ _ __
            // | | | |/ _ \/ __| __| '__/ _ \| | | | | |   / _ \| '_ \/ __| __| '__| | | |/ __| __/ _ \/ _` |  \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__|
            // | |_| |  __/\__ \ |_| | | (_) | |_| | | |__| (_) | | | \__ \ |_| |  | |_| | (__| ||  __/ (_| |   \ V  V / (_) | | | | (_| |  __/ |
            // |____/ \___||___/\__|_|  \___/ \__, |  \____\___/|_| |_|___/\__|_|   \__,_|\___|\__\___|\__,_|    \_/\_/ \___/|_| |_|\__,_|\___|_|
            //                                |___/

            onEnterDestroyConstructedWonder: function (args) {
                dojo.query('#player_wonders_' + this.getOppositePlayerId(this.getActivePlayerId()) + ' .wonder[data-constructed="1"]').addClass('red_border');
            },

            onDestroyConstructedWonderClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onDestroyConstructedWonderClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionDestroyConstructedWonder')) {
                        return;
                    }

                    var wonderId = dojo.attr(e.target, "data-wonder-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionDestroyConstructedWonder.html", {
                            lock: true,
                            wonderId: wonderId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_destroyConstructedWonder: function (notif) {
                if (this.debug) console.log('notif_destroyConstructedWonder', notif);

                this.clearRedBorder();

                let wonderNode = $('wonder_' + notif.args.wonderId);
                let wonderContainer = $('wonder_' + notif.args.wonderId + '_container');
                let ageCardNode = dojo.query('.building_small', wonderContainer)[0];

                var buildingNode = this.createDiscardedBuildingNode(notif.args.buildingId);
                this.placeOnObjectPos(buildingNode, ageCardNode, 0, 0);
                dojo.style(buildingNode, 'transform', 'rotate(-90deg) rotateY(-180deg) perspective(40em)'); // The rotateY(-90deg) affects the position the element will end up after the slide. Here's the place to apply it therefor, not before the animation instantiation.

                var anim = dojo.fx.chain([
                    dojo.fadeOut({
                        node: wonderNode,
                        duration: 1000,
                        onEnd: dojo.hitch(this, function (node) {
                            dojo.destroy(node);
                        })
                    }),
                    dojo.fx.combine([
                        dojo.animateProperty({
                            node: ageCardNode,
                            duration: this.constructWonderAnimationDuration / 3,
                            easing: dojo.fx.easing.linear,
                            properties: {
                                propertyXTransform: {start: -90, end: 0},
                                propertyYTransform: {start: 0, end: 180}
                            },
                            onAnimate: function (values) {
                                dojo.style(this.node, 'transform', 'perspective(40em) rotate(' + parseFloat(values.propertyXTransform.replace("px", "")) + 'deg) rotateY(' + parseFloat(values.propertyYTransform.replace("px", "")) + 'deg)');
                            },
                            onEnd: dojo.hitch(this, function (node) {
                                dojo.destroy(node);
                                dojo.destroy(wonderContainer);
                            })
                        }),
                        dojo.animateProperty({
                            node: buildingNode,
                            duration: this.constructWonderAnimationDuration / 3,
                            easing: dojo.fx.easing.linear,
                            properties: {
                                propertyXTransform: {start: -90, end: 0},
                                propertyYTransform: {start: -180, end: 0}
                            },
                            onAnimate: function (values) {
                                dojo.style(this.node, 'transform', 'perspective(40em) rotate(' + parseFloat(values.propertyXTransform.replace("px", "")) + 'deg) rotateY(' + parseFloat(values.propertyYTransform.replace("px", "")) + 'deg)');
                            }
                        }),
                    ]),
                    dojo.animateProperty({
                        node: buildingNode,
                        duration: this.constructWonderAnimationDuration / 2,
                        easing: dojo.fx.easing.linear,
                        delay: this.constructWonderAnimationDuration / 4,
                        properties: {
                            top: 0,
                            left: 0
                        },
                        onPlay: function (values) {
                            dojo.style(this.node, 'z-index', 50);
                        },
                        onEnd: function (values) {
                            dojo.style(this.node, 'z-index', 1);
                        }
                    }),
                ]);

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                anim.play();
            },

            //  ____  _                       _      _             _ _       _     _         ____              _
            // |  _ \(_)___  ___ __ _ _ __ __| |    / \__   ____ _(_) | __ _| |__ | | ___   / ___|__ _ _ __ __| |
            // | | | | / __|/ __/ _` | '__/ _` |   / _ \ \ / / _` | | |/ _` | '_ \| |/ _ \ | |   / _` | '__/ _` |
            // | |_| | \__ \ (_| (_| | | | (_| |  / ___ \ V / (_| | | | (_| | |_) | |  __/ | |__| (_| | | | (_| |
            // |____/|_|___/\___\__,_|_|  \__,_| /_/   \_\_/ \__,_|_|_|\__,_|_.__/|_|\___|  \____\__,_|_|  \__,_|

            onEnterDiscardAvailableCard: function (args) {
                dojo.query('#draftpool .available').addClass('red_border');


                if (args.round == 1) {
                    if (this.isCurrentPlayerActive()) {
                        dojo.style($('simple_skip'), 'display', 'none');
                    }
                    this.gamedatas.gamestate.description = _('${actplayer} can move an available card to the discard pile (and may repeat this action a second time if possible)');
                    this.gamedatas.gamestate.descriptionmyturn = _('${you} can move an available card to the discard pile (and may repeat this action a second time if possible)');
                }
                else {
                    if (this.isCurrentPlayerActive()) {
                        dojo.style($('simple_skip'), 'display', 'block');
                    }
                    this.gamedatas.gamestate.description = _('${actplayer} can move a second available card to the discard pile or skip');
                    this.gamedatas.gamestate.descriptionmyturn = _('${you} can move a second available card to the discard pile or skip');
                }
                this.updatePageTitle();
            },

            onDiscardAvailableCardClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onDiscardAvailableCardClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionDiscardAvailableCard')) {
                        return;
                    }

                    var building = dojo.hasClass(e.target, 'building') ? e.target : dojo.query(e.target).closest(".building")[0];

                    var buildingId = dojo.attr(building, "data-building-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionDiscardAvailableCard.html", {
                            lock: true,
                            buildingId: buildingId
                        },
                        this, function (result) {
                            this.clearRedBorder();
                            if (this.isCurrentPlayerActive()) {
                                dojo.style($('simple_skip'), 'display', ''); // Clear the manually set display block/none.
                            }
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            onDiscardAvailableCardSkipButtonClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onDiscardAvailableCardSkipButtonClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionSkipDiscardAvailableCard')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionSkipDiscardAvailableCard.html", {
                            lock: true
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            this.clearRedBorder();
                            if (this.isCurrentPlayerActive()) {
                                dojo.style($('simple_skip'), 'display', ''); // Clear the manually set display block/none.
                            }

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            //  _               _      ____                                      _____     _
            // | |    ___   ___| | __ |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __
            // | |   / _ \ / __| |/ / | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \
            // | |__| (_) | (__|   <  |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | |
            // |_____\___/ \___|_|\_\ |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|
            //                                         |___/

            onEnterLockProgressToken: function (args) {
                dojo.query('#progress_token_from_box h3')[0].innerHTML = _("Choose a Progress token from the board, your opponent or the box and lock it away for the rest of the game");

                if (this.debug) console.log('onEnterLockProgressToken', args);
                if (this.isCurrentPlayerActive()) {
                    dojo.query('#progress_token_from_box_container>div').style('display', 'none');
                    dojo.query('#progress_token_from_box_container>div').empty();
                    Object.keys(args._private.progressTokensFromBox).forEach(dojo.hitch(this, function (progressTokenId) {
                        var card = args._private.progressTokensFromBox[progressTokenId];
                        var container = dojo.query('#progress_token_from_box_container>div:nth-of-type(' + (parseInt(card.location_arg) + 1) + ')')[0];
                        dojo.style(container, 'display', 'inline-block');
                        dojo.place(this.getProgressTokenDivHtml(card.id), container);
                    }));
                }

                dojo.addClass($('board_progress_tokens'), 'red_border');
                dojo.query('.player' + this.getOppositePlayerId(this.getActivePlayerId()) + ' .player_area_progress_tokens > .progress_token_outline:not(:empty)').addClass('red_border');
            },

            onLockProgressTokenClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onLockProgressTokenClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionLockProgressToken')) {
                        return;
                    }

                    var progressToken = dojo.hasClass(e.target, 'progress_token') ? dojo.query(e.target) : dojo.query(e.target).closest(".progress_token");
                    var progressTokenId = dojo.attr(progressToken[0], "data-progress-token-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionLockProgressToken.html", {
                            lock: true,
                            progressTokenId: progressTokenId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_lockProgressToken: function (notif) {
                if (this.debug) console.log('notif_lockProgressToken', notif);

                // Skip for the active player, who will get their own notification of the same type but with a real progressTokenId.
                if (this.isCurrentPlayerActive() && notif.args.progressTokenId == 16) {
                    this.notifqueue.setSynchronousDuration(0);
                    return;
                }

                this.clearRedBorder();

                var conspiracyNode = dojo.query('.conspiracy_compact[data-conspiracy-id="5"]')[0];
                if (notif.args.progressTokenId < 16) {
                    let progressTokenNode = dojo.query("[data-progress-token-id=" + notif.args.progressTokenId + "]")[0];
                    dojo.style(progressTokenNode, 'z-index', 100);
                    var anim = this.slideToObject(progressTokenNode, conspiracyNode, this.progressTokenDuration);

                    dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                        dojo.destroy(progressTokenNode);
                        conspiracyNode.dataset.conspiracyProgressToken = 1;
                    }));

                    // Wait for animation before handling the next notification (= state change).
                    this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                    anim.play();
                }
                else {
                    conspiracyNode.dataset.conspiracyProgressToken = 1;
                    this.notifqueue.setSynchronousDuration(0);
                }

            },

            //  __  __                  ____
            // |  \/  | _____   _____  |  _ \  ___  ___ _ __ ___  ___
            // | |\/| |/ _ \ \ / / _ \ | | | |/ _ \/ __| '__/ _ \/ _ \
            // | |  | | (_) \ V /  __/ | |_| |  __/ (__| | |  __/  __/
            // |_|  |_|\___/ \_/ \___| |____/ \___|\___|_|  \___|\___|

            onEnterMoveDecree: function (args) {
                if (this.debug) console.log('onEnterMoveDecree', args);

                if (this.isCurrentPlayerActive()) {
                    this.selectMoveDecreeFromMode();
                }
                else {
                    dojo.query('.decree_containers .decree_small').addClass('red_border');
                }
            },

            selectMoveDecreeFromMode: function() {
                this.setClientState("client_moveDecreeFrom", {
                    descriptionmyturn: _("${you} must choose a Decree token to move to a Chamber of your choice, under the existing Decree"),
                });

                this.markChambers([]);
                dojo.query('.decree_containers .decree_small').removeClass('gray_border'); // For both players
                dojo.query('.decree_containers .decree_small').addClass('red_border'); // For both players
            },

            onMoveDecreeFromClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                e.stopImmediatePropagation();

                if (this.debug) console.log('onMoveDecreeFromClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionMoveDecree')) {
                        return;
                    }

                    this.clearRedBorder();

                    this.setClientState("client_moveDecreeTo", {
                        descriptionmyturn: _("${you} must choose a Senate Chamber to move the Decree token to, under the existing Decree"),
                    });

                    let chamber = parseInt($(e.target).closest(".chamber_decrees_container").dataset.chamber);
                    let toChambers = [];
                    for (let i = 1; i <= 6; i++) {
                        if (i != chamber) {
                            toChambers.push(i);
                        }
                    }
                    // Current chamber = green/gray
                    dojo.query('.decree_containers .decree_small').removeClass('red_border'); // For both players
                    dojo.addClass($(e.target), 'gray_border');
                    this.markChambers(toChambers);

                    this.moveDecreeFrom = chamber;
                }
            },

            onMoveDecreeCancelClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                e.stopImmediatePropagation();

                this.moveDecreeFrom = 0;

                if (this.debug) console.log('onMoveDecreeCancelClick');

                if (this.isCurrentPlayerActive()) {
                    this.selectMoveDecreeFromMode();
                }
            },

            onMoveDecreeToClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onMoveDecreeToClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionMoveDecree')) {
                        return;
                    }

                    var chamber = dojo.attr(e.target, "data-chamber");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionMoveDecree.html", {
                            lock: true,
                            chamberFrom: this.moveDecreeFrom,
                            chamberTo: chamber,
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_moveDecree: function (notif) {
                if (this.debug) console.log('notif_moveDecree', notif);

                this.updateDecreesSituation(notif.args.decreesSituation);

                this.markChambers([]);
                this.clearGrayBorder();

                // Update senate chambers
                Object.keys(notif.args.senateAction.chambers).forEach(dojo.hitch(this, function (chamber) {
                    var chamberData = notif.args.senateAction.chambers[chamber];
                    this.updateSenateChamber(chamber, chamberData);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(this.twistCoinDuration + this.notification_safe_margin);
            },

            //  ____                       ____        _ _     _ _
            // / ___|_      ____ _ _ __   | __ ) _   _(_) | __| (_)_ __   __ _
            // \___ \ \ /\ / / _` | '_ \  |  _ \| | | | | |/ _` | | '_ \ / _` |
            //  ___) \ V  V / (_| | |_) | | |_) | |_| | | | (_| | | | | | (_| |
            // |____/ \_/\_/ \__,_| .__/  |____/ \__,_|_|_|\__,_|_|_| |_|\__, |
            //                    |_|                                    |___/

            onEnterSwapBuilding: function (args) {
                var opponentId = this.getOppositePlayerId(this.getActivePlayerId());
                args.columns.forEach(function(columnName) {
                    dojo.query('.player_building_column.' + columnName).addClass('red_border');
                });
            },

            onSwapBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onSwapBuildingClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionSwapBuilding')) {
                        return;
                    }

                    var playerBuildings = dojo.query(e.target).closest(".player_buildings")[0];
                    var buildingId = dojo.attr(e.target, "data-building-id");

                    this.clearGreenBorder(playerBuildings);
                    dojo.addClass(e.target, 'green_border');

                    let playerId = dojo.hasClass(playerBuildings, 'opponent') ? this.opponent_id : this.me_id;
                    if (playerId == this.opponent_id) {
                        this.swapOpponentBuildingId = buildingId;
                    }
                    else {
                        this.swapMeBuildingId = buildingId;
                    }

                    if (this.swapOpponentBuildingId > 0 && this.swapMeBuildingId > 0) {
                        this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionSwapBuilding.html", {
                                lock: true,
                                opponentBuildingId: this.swapOpponentBuildingId,
                                meBuildingId: this.swapMeBuildingId
                            },
                            this, function (result) {
                                // What to do after the server call if it succeeded
                                // (most of the time: nothing)

                            }, function (is_error) {
                                // What to do after the server call in anyway (success or failure)
                                // (most of the time: nothing)

                            }
                        );
                    }
                }
            },

            notif_swapBuilding: function (notif) {
                if (this.debug) console.log('notif_swapBuilding', notif);

                this.clearGreenBorder();

                let buildingOpponentId = notif.args.buildingOpponentId;
                let buildingPlayerId = notif.args.buildingPlayerId;

                var buildingPlayerNodeContainer = $('player_building_container_' + buildingPlayerId);
                var buildingPlayerNode = dojo.query('.building' , buildingPlayerNodeContainer)[0];
                var buildingOpponentNodeContainer = $('player_building_container_' + buildingOpponentId);
                var buildingOpponentNode = dojo.query('.building' , buildingOpponentNodeContainer)[0];

                let anims = [];
                anims.push(this.slideToObjectPos(buildingPlayerNode, buildingOpponentNode, 0, 0, this.constructBuildingAnimationDuration * 0.6));
                anims.push(this.slideToObjectPos(buildingOpponentNode, buildingPlayerNode, 0, 0, this.constructBuildingAnimationDuration * 0.6));

                let anim = dojo.fx.combine(anims);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    var buildingOpponentNodeContainerCopy = dojo.clone(buildingOpponentNodeContainer);
                    var buildingPlayerNodeContainerCopy = dojo.clone(buildingPlayerNodeContainer);

                    dojo.place(buildingPlayerNodeContainerCopy, buildingOpponentNodeContainer, "replace");
                    dojo.place(buildingOpponentNodeContainerCopy, buildingPlayerNodeContainer, "replace");

                    var buildingPlayerNodeCopy = dojo.query('.building' , buildingPlayerNodeContainerCopy)[0];
                    var buildingOpponentNodeCopy = dojo.query('.building' , buildingOpponentNodeContainerCopy)[0];

                    dojo.style(buildingPlayerNodeCopy, 'top', '0px');
                    dojo.style(buildingOpponentNodeCopy, 'top', '0px');

                    this.clearRedBorder();
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                anim.play();
            },

            //  _____     _          ____        _ _     _ _
            // |_   _|_ _| | _____  | __ ) _   _(_) | __| (_)_ __   __ _
            //   | |/ _` | |/ / _ \ |  _ \| | | | | |/ _` | | '_ \ / _` |
            //   | | (_| |   <  __/ | |_) | |_| | | | (_| | | | | | (_| |
            //   |_|\__,_|_|\_\___| |____/ \__,_|_|_|\__,_|_|_| |_|\__, |
            //                                                     |___/

            onEnterTakeBuilding: function (args) {
                var opponentId = this.getOppositePlayerId(this.getActivePlayerId());
                var columns = ["Brown", "Grey"];
                columns.forEach(function(columnName) {
                    var buildingColumn = dojo.query('.player' + opponentId + ' .player_building_column.' + columnName)[0];
                    dojo.addClass(buildingColumn, 'red_border');
                });
            },

            onTakeBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onTakeBuildingClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionTakeBuilding')) {
                        return;
                    }

                    var buildingId = dojo.attr(e.target, "data-building-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionTakeBuilding.html", {
                            lock: true,
                            buildingId: buildingId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_takeBuilding: function (notif) {
                if (this.debug) console.log('notif_takeBuilding', notif);

                this.clearRedBorder();

                var buildingNodeContainer = $('player_building_container_' + notif.args.buildingId);
                var buildingNode = dojo.query('.building' , buildingNodeContainer)[0];

                // Copy buildingNodeContainer so we have an easy source and target.
                var buildingNodeContainerCopy = dojo.clone(buildingNodeContainer);

                let buildingColumn = dojo.query('.player_buildings.player' + notif.args.playerId + ' .player_building_column.' + notif.args.buildingColumn)[0];
                dojo.place(buildingNodeContainerCopy, buildingColumn);

                var buildingNodeCopy = dojo.query('.building' , buildingNodeContainerCopy)[0];
                dojo.style(buildingNodeCopy, 'z-index', 100);

                dojo.style(buildingNodeContainer, 'opacity', 0); // Hide original buildingNodeContainer (destroy at the end of the animation.

                // Place building at start and then slide to copies' container position.
                this.placeOnObject(buildingNodeCopy, buildingNode);
                var anim = this.slideToObjectPos(buildingNodeCopy, buildingNodeContainerCopy, 0, 0, this.constructBuildingAnimationDuration * 0.6);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    dojo.style(buildingNodeCopy, 'z-index', 5);
                    dojo.destroy(buildingNodeContainer);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration);

                anim.play();
            },

            //  _____     _          _   _                           _                   _           _  __        __              _
            // |_   _|_ _| | _____  | | | |_ __   ___ ___  _ __  ___| |_ _ __ _   _  ___| |_ ___  __| | \ \      / /__  _ __   __| | ___ _ __
            //   | |/ _` | |/ / _ \ | | | | '_ \ / __/ _ \| '_ \/ __| __| '__| | | |/ __| __/ _ \/ _` |  \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__|
            //   | | (_| |   <  __/ | |_| | | | | (_| (_) | | | \__ \ |_| |  | |_| | (__| ||  __/ (_| |   \ V  V / (_) | | | | (_| |  __/ |
            //   |_|\__,_|_|\_\___|  \___/|_| |_|\___\___/|_| |_|___/\__|_|   \__,_|\___|\__\___|\__,_|    \_/\_/ \___/|_| |_|\__,_|\___|_|

            onEnterTakeUnconstructedWonder: function (args) {
                dojo.query('#player_wonders_' + this.getOppositePlayerId(this.getActivePlayerId()) + ' .wonder[data-constructed="0"]').addClass('red_border');
            },

            onTakeUnconstructedWonderClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.debug) console.log('onTakeUnconstructedWonderClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionTakeUnconstructedWonder')) {
                        return;
                    }

                    var wonderId = dojo.attr(e.target, "data-wonder-id");

                    this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionTakeUnconstructedWonder.html", {
                            lock: true,
                            wonderId: wonderId
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            notif_takeUnconstructedWonder: function (notif) {
                if (this.debug) console.log('notif_takeUnconstructedWonder', notif);

                this.clearRedBorder();

                var wonderContainerNode = $('wonder_' + notif.args.wonderId + '_container');
                var newContainer = dojo.query('#player_wonders_' + notif.args.playerId + '>div:nth-of-type(' + notif.args.position + ')')[0];
                // Show the 5th wonder container
                if (parseInt(notif.args.position) > 4) {
                    this.updatePlayerNumberOfWondersStyling(notif.args.playerId, notif.args.position);
                }

                wonderContainerNode = this.attachToNewParent(wonderContainerNode, newContainer);
                wonderNode = dojo.query('.wonder', wonderContainerNode)[0];
                dojo.style(dojo.query('.player_wonder_cost', wonderNode)[0], 'display', 'none');

                var anim = dojo.fx.chain([
                    this.slideToObjectPos(wonderContainerNode, newContainer, 0, 0, this.selectWonderAnimationDuration),
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    // Update the wonders situation, so cost is updated for the new wonder owner.
                    this.updateWondersSituation(notif.args.wondersSituation);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration);

                anim.play();
            },

            //  _   _           _     ____  _                         _____
            // | \ | | _____  _| |_  |  _ \| | __ _ _   _  ___ _ __  |_   _|   _ _ __ _ __
            // |  \| |/ _ \ \/ / __| | |_) | |/ _` | | | |/ _ \ '__|   | || | | | '__| '_ \
            // | |\  |  __/>  <| |_  |  __/| | (_| | |_| |  __/ |      | || |_| | |  | | | |
            // |_| \_|\___/_/\_\\__| |_|   |_|\__,_|\__, |\___|_|      |_| \__,_|_|  |_| |_|
            //                                      |___/

            notif_nextPlayerTurnScientificSupremacy: function (notif) {
                if (this.debug) console.log('notif_nextPlayerTurnScientificSupremacy', notif);

                var animationDuration = this.scientificSupremacyAnimation(notif.args.playersSituation);

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(animationDuration);
            },

            scientificSupremacyAnimation: function (playersSituation) {
                if (playersSituation.winner) { // if statement for: Bit of a hacky way to achieve both players' green cards getting highlighted part 3
                    if (this.debug) console.log('scientificSupremacyAnimation', playersSituation);
                    dojo.addClass(dojo.query('.player' + playersSituation.winner + ' .player_building_column.Green')[0], 'endgame_highlight');
                    var progressTokenNode = dojo.query('.player_info.player' + playersSituation.winner + ' #progress_token_4')[0];
                    if (progressTokenNode) {
                        dojo.addClass(progressTokenNode, 'endgame_highlight');
                    }
                    var divinityNode = dojo.query('#player_conspiracies_' + playersSituation.winner + ' .divinity[data-divinity-id="2"]')[0];
                    if (divinityNode) {
                        dojo.addClass(divinityNode, 'endgame_highlight');
                    }

                    // Unset endGameCondition to prevent an infinite loop.
                    playersSituation.endGameCondition = undefined;
                    this.updatePlayersSituation(playersSituation);
                    return 500;
                }
                return 0;
            },

            notif_nextPlayerTurnMilitarySupremacy: function (notif) {
                if (this.debug) console.log('notif_nextPlayerTurnMilitarySupremacy', notif);

                var animationDuration = this.militarySupremacyAnimation(notif.args.playersSituation);

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(animationDuration);
            },

            militarySupremacyAnimation: function (playersSituation) {
                if (this.debug) console.log('militarySupremacyAnimation', playersSituation);
                dojo.addClass($('conflict_pawn'), 'endgame_highlight');

                // Unset endGameCondition to prevent an infinite loop.
                playersSituation.endGameCondition = undefined;
                this.updatePlayersSituation(playersSituation);
                return 500;
            },

            notif_nextPlayerTurnPoliticalSupremacy: function (notif) {
                if (this.debug) console.log('notif_nextPlayerTurnPoliticalSupremacy', notif);

                var animationDuration = this.politicalSupremacyAnimation(notif.args.playersSituation);

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(animationDuration);
            },

            politicalSupremacyAnimation: function (playersSituation) {
                if (this.debug) console.log('politicalSupremacyAnimation', playersSituation);

                dojo.query(".influence_containers .agora_cube.player" + playersSituation.winner).addClass("endgame_highlight");

                // Unset endGameCondition to prevent an infinite loop.
                playersSituation.endGameCondition = undefined;
                this.updatePlayersSituation(playersSituation);
                return 500;
            },

            notif_nextPlayerTurnEndGameScoring: function (notif) {
                if (this.debug) console.log('notif_nextPlayerTurnEndGameScoring', notif);
                // First update the playersSituation with the pre-endgame situation.
                this.updatePlayersSituation(notif.args.playersSituation);

                var animationDuration = this.endGameScoringAnimation(notif.args.endGamePlayersSituation);

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(animationDuration);
            },

            endGameScoringAnimation: function (playersSituation) {
                if (this.debug) console.log('endGameScoringAnimation', playersSituation);
                dojo.style($('draftpool_container'), 'display', 'none');
                dojo.style($('end_game_container'), 'display', 'block');
                this.updateLayout();

                // First set the table to the situation in this.gamedatas.playersSituation
                var categories = ['blue', 'green', 'yellow', 'purple', 'wonders', 'progresstokens', 'coins', 'military'];
                if (this.agora) {
                    categories.push('senate');
                }
                Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                    for (var i = 0; i < categories.length; i++) {
                        dojo.query('#end_game_container .end_game_' + categories[i] + '.player' + playerId)[0].innerHTML = this.gamedatas.playersSituation[playerId]['player_score_' + categories[i]];
                    }
                    // dojo.query('#end_game_container .end_game_total.player' + playerId + ' span')[0].innerHTML = this.gamedatas.playersSituation[playerId]['score'];

                    var playerPointsNode = $('player_area_' + playerId + '_score').parentElement;

                    // Only do this animation when the player points are in their original position, not in the end game table already.
                    var playerWondersNode = dojo.query(playerPointsNode).closest(".player_area_points");
                    if (playerWondersNode[0]) {
                        var targetContainer = dojo.query('#end_game_container .end_game_total.player' + playerId)[0];
                        playerPointsNode = this.attachToNewParent(playerPointsNode, targetContainer); // attachToNewParent creates and returns a new instance of the node (replacing the old one).

                        // Next we slide (while adjusting the scale during the animation) the wonder.
                        var startScale = 1.0;
                        var endScale = 1.4;
                        playerPointsNode.style.setProperty('--element-scale', startScale);

                        var anim = dojo.fx.combine([
                            dojo.animateProperty({
                                node: playerPointsNode,
                                duration: this.victorypoints_slide_duration,
                                properties: {
                                    propertyScale: {start: startScale, end: endScale}
                                },
                                onEnd: dojo.hitch(this, function (node) {
                                    playerPointsNode.style.removeProperty('--element-scale');
                                }),
                                onAnimate: function (values) {
                                    playerPointsNode.style.setProperty('--element-scale', parseFloat(values.propertyScale.replace("px", "")));
                                }
                            }),
                            this.slideToObjectPos(playerPointsNode, targetContainer, 0, 0, this.victorypoints_slide_duration),
                        ]);
                        anim.play();
                    }
                }));

                // Unset endGameCondition to prevent an infinite loop.
                playersSituation.endGameCondition = undefined;
                return this.victorypoints_slide_duration;
            },

            notif_endGameCategoryUpdate: function (notif) {
                var anims = [];

                var zIndex = 1;
                if (notif.args.highlightId) {
                    if (notif.args.category == "senate") {
                        for (let i = 0; i < notif.args.highlightId.length; i++) {
                            $(notif.args.highlightId[i]).setAttribute("class",  "red_stroke");
                        }
                    }
                    else {
                        zIndex = dojo.style(notif.args.highlightId, 'z-index');
                        dojo.style(notif.args.highlightId, 'z-index', 100);
                        dojo.addClass(notif.args.highlightId, 'endgame_highlight');
                    }
                }

                for (var i = 0; i < notif.args.playerIds.length; i++) {
                    let playerId = notif.args.playerIds[i];
                    var categoryNode = dojo.query('#end_game_container .end_game_' + notif.args.category + '.player' + playerId)[0];
                    var totalNode = dojo.query('#end_game_container .end_game_total.player' + playerId + ' span')[0];
                    dojo.addClass(categoryNode, 'endgame_highlight');
                    let stickyCategory = notif.args.stickyCategory !== undefined;

                    var pointAnims = [];
                    if (notif.args.points > 0) {
                        pointAnims.push(
                            dojo.animateProperty({
                                node: 'swd',
                                duration: notif.args.points * 100,
                                properties: {
                                    propertyScale: {
                                        start: parseInt(categoryNode.innerHTML),
                                        end: parseInt(categoryNode.innerHTML) + parseInt(notif.args.points)
                                    }
                                },
                                onAnimate: function (values) {
                                    var score = Math.floor(parseFloat(values.propertyScale.replace("px", "")));
                                    if (parseInt(score) != parseInt(categoryNode.innerHTML)) {
                                        categoryNode.innerHTML = score;
                                        totalNode.innerHTML = parseInt(totalNode.innerHTML) + 1;
                                    }
                                }
                            })
                        );
                    }

                    anims.push(dojo.fx.chain([
                        dojo.animateProperty({
                            node: 'swd',
                            duration: 400
                        }),
                        dojo.fx.combine(pointAnims),
                        dojo.animateProperty({
                            node: 'swd',
                            duration: 400,
                            onEnd: function (node) {
                                if (!stickyCategory) {
                                    dojo.removeClass(categoryNode, 'endgame_highlight');
                                }
                                if (notif.args.highlightId) {
                                    if (notif.args.category == "senate") {
                                        for (let i = 0; i < notif.args.highlightId.length; i++) {
                                            $(notif.args.highlightId[i]).setAttribute("class",  "");
                                        }
                                    }
                                    else {
                                        dojo.style(notif.args.highlightId, 'z-index', zIndex);
                                        dojo.removeClass(notif.args.highlightId, 'endgame_highlight');
                                    }
                                }
                            }
                        }),
                        dojo.animateProperty({
                            node: 'swd',
                            duration: 200
                        }),
                    ]));
                }
                var anim = dojo.fx.combine(anims);
                anim.play();

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration);
            },

            //   ____  _           _               _____                 _   _
            //  |  _ \(_)___ _ __ | | __ _ _   _  |  ___|   _ _ __   ___| |_(_) ___  _ __  ___
            //  | | | | / __| '_ \| |/ _` | | | | | |_ | | | | '_ \ / __| __| |/ _ \| '_ \/ __|
            //  | |_| | \__ \ |_) | | (_| | |_| | |  _|| |_| | | | | (__| |_| | (_) | | | \__ \
            //  |____/|_|___/ .__/|_|\__,_|\__, | |_|   \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
            //              |_|            |___/

            getOffset: function (el) {
                var rect = el.getBoundingClientRect();
                return {
                    left: rect.left + window.rememberScrollX,
                    top: rect.top + window.rememberScrollY
                };
            },

            setLayout: function (layout) {
                var swdNode = $('swd_wrap');
                dojo.removeClass(swdNode, this.LAYOUT_SQUARE);
                dojo.removeClass(swdNode, this.LAYOUT_PORTRAIT);
                dojo.removeClass(swdNode, this.LAYOUT_LANDSCAPE);
                dojo.addClass(swdNode, layout);

                switch(layout) {
                    case this.LAYOUT_LANDSCAPE:
                    case this.LAYOUT_SQUARE:
                        Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                            dojo.place('player_conspiracies_' + playerId, 'player_wonders_container_' + playerId);
                            dojo.place('player_wonders_' + playerId, 'player_wonders_container_' + playerId);
                            dojo.empty('player_wonders_mobile_container_' + playerId);
                        }));
                        break;
                    case this.LAYOUT_PORTRAIT:
                        Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                            if (parseInt(playerId) == this.me_id) dojo.place('player_wonders_' + playerId, 'player_wonders_mobile_container_' + playerId);
                            dojo.place('player_conspiracies_' + playerId, 'player_wonders_mobile_container_' + playerId);
                            if (parseInt(playerId) == this.opponent_id) dojo.place('player_wonders_' + playerId, 'player_wonders_mobile_container_' + playerId);
                            dojo.empty('player_wonders_container_' + playerId);
                        }));
                        break;
                }
            },

            setScale: function (scale) {
                this.setCssVariable('--scale', scale);
                $('setting_scale').value = parseInt(scale * 100);

                this.updateLowerDivsWidth();
            },

            getCssVariable: function (name, node=null) {
                return getComputedStyle(document.documentElement).getPropertyValue(name);
            },

            setCssVariable: function (name, value, node=null) {
                if (node) {
                    node.style.setProperty(name, value);
                }
                else {
                    document.documentElement.style.setProperty(name, value);
                }
            },

            onScreenWidthChange: function () {
                this.onWindowUpdate();
            },

            onWindowUpdate: function (e) {
                // First check if the resolution has changed. We don't have to update when just scrolling down the page (which results in stuttering motion / impossible scrolling).
                let availableDimensions = this.getAvailableDimensions();
                if (!this.previousAvailableDimensions || availableDimensions.toString() != this.previousAvailableDimensions.toString()) {
                    this.previousAvailableDimensions = availableDimensions;
                    this.viewportChange();
                }
            },

            viewportChange: function (e) {
                // Hide zoom button as it is actually counter productive with the scaling interface.
                dojo.style("globalaction_zoom_wrap", "display", "none");

                clearTimeout(this.windowResizeTimeoutId);
                // Set up the callback
                this.windowResizeTimeoutId = setTimeout(dojo.hitch(this, "updateLayout"), 50);
            },

            getAvailableDimensions: function() {
                var pageZoom = dojo.style($('page-content'), "zoom");
                if (pageZoom == undefined) pageZoom = 1;

                var titlePosition = dojo.position('page-title', true);
                var titleMarginBottom = 5;

                if (this.debug) console.log('titlePosition: ', titlePosition);
                if (this.debug) console.log('pageZoom', pageZoom);

                let width = titlePosition.w;
                var swdPosition = dojo.position('swd', true);
                let height = (window.innerHeight / pageZoom - swdPosition.y  - titleMarginBottom);

                return [width, height]
            },

            getAvailableRatio: function() {
                let dimensions = this.getAvailableDimensions();
                return dimensions[0] / dimensions[1];
            },

            getCurrentDimensions: function() {
                let width = dojo.style($('layout_flexbox'), 'width');
                let height = dojo.style($('swd_wrap'), 'height');
                return [width, height]
            },

            getCurrentRatio: function() {
                let dimensions = this.getCurrentDimensions();
                return dimensions[0] / dimensions[1];
            },

            updateLayout: function () {
                if (this.layout != this.LAYOUT_AUTO) {
                    this.layout = $('setting_layout').value;
                    dojo.cookie('swd_layout_v2', this.layout, { expires: this.cookieExpireDays });
                }
                else {
                    dojo.cookie("swd_layout_v2", null, {expires: -1});
                }

                if (!this.autoScale) {
                    this.scale = Math.min(200, Math.max(50, parseInt($('setting_scale').value))) / 100;
                    this.setScale(this.scale);
                    dojo.cookie('swd_scale', this.scale, { expires: this.cookieExpireDays });
                }
                else {
                    dojo.cookie("swd_scale", null, {expires: -1});
                }

                var availableDimensions = this.getAvailableDimensions();
                var availableRatio = availableDimensions[0] / availableDimensions[1];
                if (this.debug) {
                    var debugPlayArea = $('debugPlayArea');
                    dojo.style(debugPlayArea, "width", availableDimensions[0] + 'px');
                    dojo.style(debugPlayArea, "height", availableDimensions[1] + 'px');

                    if (this.debug) console.log('available play area: ', availableDimensions[0], availableDimensions[1]);
                    if (this.debug) console.log('ratio', availableRatio);
                }

                // Measured in 75% view, without any player buildings (meaning the height can become heigher:
                var portrait = 0.88;
                // var square = 947 / 897; // 1.056
                var landscape = 1.4;

                if (this.layout == this.LAYOUT_AUTO) {
                    if (availableRatio >= landscape) {
                        if (this.debug) console.log('ratio: ', availableRatio, 'choosing landscape');
                        this.layout = this.LAYOUT_LANDSCAPE;
                    } else if (availableRatio < landscape && availableRatio > portrait) {
                        if (this.debug) console.log('ratio: ', availableRatio, 'choosing square');
                        this.layout = this.LAYOUT_SQUARE;
                    } else { // ratio <= portrait
                        if (this.debug) console.log('ratio: ', availableRatio, 'choosing portrait');
                        this.layout = this.LAYOUT_PORTRAIT;
                    }
                }
                this.setLayout(this.layout);

                // Already update based on layout change. (Update later again in autoUpdateScale -> setScale).
                this.updateLowerDivsWidth();

                if (this.autoScale && !this.freezeLayout) {
                    // Delayed call to update the scale.
                    // Why delayed? Because possibly changing the position of the player wonders div in setLayout, takes a while for css/dom/browser to process.
                    clearTimeout(this.autoUpdateScaleTimeoutId);
                    this.autoUpdateScaleTimeoutId = setTimeout(dojo.hitch(this, "autoUpdateScale"), 50);
                }
                else if(this.settingsScrollIntoView) {
                    this.scrollSettingsIntoView();
                }
            },

            updateLowerDivsWidth: function() {
                dojo.style($('lower_divs_container'), 'width', $('layout_flexbox').offsetWidth + 'px');
                dojo.style($('settings_whiteblock'), 'width', $('layout_flexbox').offsetWidth + 'px');
                dojo.query('.player_wonders_mobile').forEach(dojo.hitch(this, function (node, index, arr) {
                    dojo.style(node, 'width', 'calc(' + dojo.style('layout_flexbox', 'width') + 'px' + ' - 2 * var(--gutter))');
                }));
            },

            autoUpdateScale: function() {
                if (this.autoScale && !this.freezeLayout) { // Also check here, since autoUpdateScale is called directly after building construction (because other wise animations don't match with the delayed scale update).
                    let availableDimensions = this.getAvailableDimensions();
                    let availableRatio = availableDimensions[0] / availableDimensions[1];

                    let currentDimensions = this.getCurrentDimensions();
                    if (currentDimensions[0] > 0) { // Only to check if the game tab is open (not the Game results tab)
                        this.setScale(1);
                        currentDimensions = this.getCurrentDimensions();
                        let currentRatio = currentDimensions[0] / currentDimensions[1];

                        switch(this.autoScale) {
                            case 1:
                                if (availableRatio > currentRatio) {
                                    this.scale = availableDimensions[1] / currentDimensions[1];
                                }
                                else {
                                    this.scale = availableDimensions[0] / currentDimensions[0];
                                }
                                break;
                            case 2:
                                this.scale = availableDimensions[0] / currentDimensions[0];
                                break;
                        }
                        this.setScale(this.scale);
                    }
                }

                if(this.settingsScrollIntoView) {
                    this.scrollSettingsIntoView();
                }
            },

            //  ____       _   _   _
            // / ___|  ___| |_| |_(_)_ __   __ _ ___
            // \___ \ / _ \ __| __| | '_ \ / _` / __|
            //  ___) |  __/ |_| |_| | | | | (_| \__ \
            // |____/ \___|\__|\__|_|_| |_|\__, |___/
            //                             |___/

            onSettingAutoScaleChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingAutoScaleChange');

                this.settingsScrollIntoView = true;

                this.autoScale = parseInt($('setting_auto_scale').value);
                dojo.cookie('swd_autoScale_v2', this.autoScale, { expires: this.cookieExpireDays });

                this.updateLayout();
                this.updateSettings();
            },

            scrollSettingsIntoView: function() {
                this.settingsScrollIntoView = false;
                document.getElementById("settings_whiteblock").scrollIntoView({
                    behavior: 'auto',
                    block: 'center',
                    inline: 'center'
                });
            },

            onSettingScaleChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingScaleChange');

                this.settingsScrollIntoView = true;

                // Input can be cleared, in that case restore the current scale.
                if (isNaN(parseInt(e.target.value))) {
                    e.target.value = parseInt(Math.round(this.scale * 100));
                }

                this.updateLayout();
                this.updateSettings();
            },

            onSettingLayoutChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingLayoutChange');

                this.settingsScrollIntoView = true;

                this.updateLayout();
                this.updateSettings();
            },

            onSettingQualityChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingQualityChange');

                this.updateQuality();
                this.updateSettings();
            },

            onSettingOpponentCostChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingOpponentCostChange');

                this.updateSettings();
            },

            updateSettings: function() {
                $('setting_auto_scale').value = this.autoScale;
                if (this.autoScale > 0) {
                    dojo.attr('setting_scale', 'disabled', '');
                    dojo.style('setting_scale_container', 'display', 'none');
                }
                else {
                    dojo.removeAttr('setting_scale', 'disabled');
                    dojo.style('setting_scale_container', 'display', 'inline-block');
                }
                dojo.attr('setting_scale', 'title', _("(value between 50 and 200)"));

                $('setting_quality').value = this.quality;

                this.showOpponentCost = parseInt($('setting_opponent_cost').value);
                dojo.cookie('swd_opponent_cost', this.showOpponentCost, { expires: this.cookieExpireDays });
                dojo.attr('swd', 'data-show-opponent-cost', this.showOpponentCost);
            },

            updateQuality: function () {
                this.quality = $('setting_quality').value;
                dojo.cookie('swd_quality_v2', this.quality, { expires: this.cookieExpireDays });

                dojo.attr('swd', 'data-quality', this.quality);
                dojo.attr('ebd-body', 'data-swd-quality', this.quality);
            },

            //     _       _           _          __                  _   _
            //    / \   __| |_ __ ___ (_)_ __    / _|_   _ _ __   ___| |_(_) ___  _ __  ___
            //   / _ \ / _` | '_ ` _ \| | '_ \  | |_| | | | '_ \ / __| __| |/ _ \| '_ \/ __|
            //  / ___ \ (_| | | | | | | | | | | |  _| |_| | | | | (__| |_| | (_) | | | \__ \
            // /_/   \_\__,_|_| |_| |_|_|_| |_| |_|  \__,_|_| |_|\___|\__|_|\___/|_| |_|___/

            onAdminFunctionClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                var _function = e.target.id;

                this.ajaxcall("/sevenwondersduelpantheon/sevenwondersduelpantheon/actionAdminFunction.html", {
                        function: _function
                    },
                    this, function (result) {
                        // What to do after the server call if it succeeded
                        // (most of the time: nothing)
                        location.reload();
                    }, function (is_error) {
                        // What to do after the server call in anyway (success or failure)
                        // (most of the time: nothing)

                    }
                );
            },

            //  _   _      _                   _____                 _   _
            // | | | | ___| |_ __   ___ _ __  |  ___|   _ _ __   ___| |_(_) ___  _ __  ___
            // | |_| |/ _ \ | '_ \ / _ \ '__| | |_ | | | | '_ \ / __| __| |/ _ \| '_ \/ __|
            // |  _  |  __/ | |_) |  __/ |    |  _|| |_| | | | | (__| |_| | (_) | | | \__ \
            // |_| |_|\___|_| .__/ \___|_|    |_|   \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
            //              |_|

            getPlayerAlias: function (playerId) {
                return playerId == this.me_id ? 'me' : 'opponent'
            },

            getOppositePlayerId: function (playerId) {
                return parseInt(playerId) == this.me_id ? this.opponent_id : this.me_id;
            },

            getCostValue: function (cost) {
                if (typeof cost == "undefined") return false;

                if (cost == 0) {
                    return '✓';
                } else {
                    return cost;
                }
            },

            getCostColor: function (cost, playerCoins) {
                if (typeof cost == "undefined") return false;

                if (cost == 0) {
                    return '#008000';
                } else if (cost <= playerCoins) {
                    return 'black';
                } else {
                    return 'red';
                }
            },

            ageRoman: function (age) {
                return "I".repeat(age);
            },

            getResourceIcon(resource, amount) {
                let html = '<div class="resource ' + resource + '"><span>';
                html += (amount > 1 || resource == "coin") ? amount : '&nbsp;';
                html += '</span></div>';
                return html;
            },

            isCookieSetAndValid: function(name, checkIsNumber=false) {
                if (dojo.cookie(name) !== undefined) {
                    if (checkIsNumber && isNaN(dojo.cookie(name))) {
                        // Cookie value is buggy, unset the cookie
                        dojo.cookie(name, null, {expires: -1});
                        return false;
                    }
                    else {
                        return true;
                    }
                }
                return false;
            },

            getDummyAnimation: function(duration) {
                return dojo.animateProperty({ // End with a dummy animation to make sure the onEnd of the last coin is also executed.
                    node: 'swd',
                    duration: duration,
                    properties: {
                        dummy: 1
                    }
                });
            }

        });
    });
