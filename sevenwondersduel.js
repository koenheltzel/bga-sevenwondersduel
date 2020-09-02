/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * sevenwondersduel.js
 *
 * SevenWondersDuel user interface script
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
        g_gamethemeurl + "modules/js/MilitaryTrackAnimator.js",
    ],
    function (dojo, declare, on, dom, cookie) {
        return declare("bgagame.sevenwondersduelagora", ebg.core.gamegui, {

            instance: null,

            LAYOUT_LANDSCAPE: 'landscape',
            LAYOUT_SQUARE: 'square',
            LAYOUT_PORTRAIT: 'portrait',

            // Show console.log messages
            debug: 0,

            // Settings
            autoScale: 1,
            scale: 1,
            autoLayout: 1,
            layout: "",
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

            // General properties
            customTooltips: [],
            windowResizeTimeoutId: null,

            // Animation durations
            constructBuildingAnimationDuration: 1000,
            discardBuildingAnimationDuration: 400,
            constructWonderAnimationDuration: 1600,
            selectWonderAnimationDuration: 300,
            progressTokenDuration: 1000,
            twistCoinDuration: 250,
            turnAroundCardDuration: 500,
            putDraftpoolCard: 250,
            coin_slide_duration: 500,
            coin_slide_delay: 100,
            notification_safe_margin: 100,
            victorypoints_slide_duration: 1000,

            constructor: function () {
                bgagame.sevenwondersduelagora.instance = this;

                if (dojo.cookie('swd_autoLayout') !== undefined) {
                    this.autoLayout = parseInt(dojo.cookie('swd_autoLayout'));
                }
                if (!this.autoLayout && dojo.cookie('swd_layout') !== undefined) {
                    this.layout = dojo.cookie('swd_layout');
                }
                if (dojo.cookie('swd_autoScale') !== undefined) {
                    this.autoScale = parseInt(dojo.cookie('swd_autoScale'));
                }
                if (!this.autoScale && dojo.cookie('swd_scale') !== undefined) {
                    this.scale = parseFloat(dojo.cookie('swd_scale'));
                }
                this.updateSettings();

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

                this.dontPreloadImage("game_banner.jpg");
                this.dontPreloadImage("game_display0.jpg");
                var isRetina = "(-webkit-min-device-pixel-ratio: 2), (min-device-pixel-ratio: 2), (min-resolution: 192dpi)";
                if (window.matchMedia(isRetina).matches) {
                    this.dontPreloadImage('board.png');
                    this.dontPreloadImage('buildings.jpg');
                    this.dontPreloadImage('linked-building-icons.jpg');
                    this.dontPreloadImage('progress_tokens.jpg');
                    this.dontPreloadImage('sprites.png');
                    this.dontPreloadImage('wonders.jpg');
                } else {
                    this.dontPreloadImage('board@2X.png');
                    this.dontPreloadImage('buildings@2X.jpg');
                    this.dontPreloadImage('linked-building-icons@2X.jpg');
                    this.dontPreloadImage('progress_tokens@2X.jpg');
                    this.dontPreloadImage('sprites@2X.png');
                    this.dontPreloadImage('wonders@2X.jpg');
                }

                dojo.destroy('debug_output'); // TODO: Remove? See http://en.doc.boardgamearena.com/Tools_and_tips_of_BGA_Studio#Speed_up_game_re-loading_by_disabling_Input.2FOutput_debug_section

                this.gamedatas = gamedatas;

                // Because of spectators we can't assume everywhere that this.player_id is one of the two players.
                this.me_id = parseInt(this.gamedatas.me_id); // me = alias for the player on the bottom
                this.opponent_id = parseInt(this.gamedatas.opponent_id); // opponent = alias for the player on the top

                // Setup game situation.
                this.updateWondersSituation(this.gamedatas.wondersSituation);
                this.updateDraftpool(this.gamedatas.draftpool, true);
                this.updateProgressTokensSituation(this.gamedatas.progressTokensSituation);
                this.updateMilitaryTrack(this.gamedatas.militaryTrack);
                this.updateDiscardedBuildings(this.gamedatas.discardedBuildings);
                this.updatePlayersSituation(this.gamedatas.playersSituation);

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
                    this.updatePlayerProgressTokens(player_id, this.gamedatas.progressTokensSituation[player_id]);
                }

                // Set setting dropdown values (translations don't work yet in the constructor, so we do it here).
                dojo.query('#setting_layout option[value="' + this.LAYOUT_PORTRAIT + '"]')[0].innerText = _('Portrait');
                dojo.query('#setting_layout option[value="' + this.LAYOUT_SQUARE + '"]')[0].innerText = _('Square');
                dojo.query('#setting_layout option[value="' + this.LAYOUT_LANDSCAPE + '"]')[0].innerText = _('Landscape');

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
                    .on("#swd[data-state=playerTurn] #draftpool .building.available:click," +
                        "#swd[data-state=client_useAgeCard] #draftpool .building.available:click",
                        dojo.hitch(this, "onPlayerTurnDraftpoolClick")
                    );
                dojo.query('body')
                    .on("#swd[data-state=client_useAgeCard] #player_wonders_" + this.me_id + " .wonder_small:click",
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

                // Click handlers without event delegation:
                dojo.query("#buttonConstructBuilding").on("click", dojo.hitch(this, "onPlayerTurnConstructBuildingClick"));
                dojo.query("#buttonDiscardBuilding").on("click", dojo.hitch(this, "onPlayerTurnDiscardBuildingClick"));
                dojo.query("#buttonConstructWonder").on("click", dojo.hitch(this, "onPlayerTurnConstructWonderClick"));

                dojo.query("#setting_auto_scale").on("change", dojo.hitch(this, "onSettingAutoScaleChange"));
                dojo.query("#setting_scale").on("change", dojo.hitch(this, "onSettingScaleChange"));
                dojo.query("#setting_auto_layout").on("change", dojo.hitch(this, "onSettingAutoLayoutChange"));
                dojo.query("#setting_layout").on("change", dojo.hitch(this, "onSettingLayoutChange"));

                // Resize/scroll handler to determine layout and scale factor
                window.addEventListener('resize', dojo.hitch(this, "onWindowUpdate"));
                window.addEventListener('scroll', dojo.hitch(this, "onWindowUpdate"));

                // Tool tips using event delegation:
                this.setupTooltips();

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                // Debug tooltip content by placing a tooltip at the top of the screen.
                // dojo.place( this.getWonderTooltip(11, this.opponent_id, '<div class="coin"><span style="color: red !important">9</span></div>'), 'swd_wrap', 'first' );
            },

            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:

                In this method, you associate each of your game notifications with your local method to handle it.

                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your sevenwondersduel.game.php file.

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

                dojo.subscribe('nextPlayerTurnEndGameScoring', this, "notif_nextPlayerTurnEndGameScoring");
                this.notifqueue.setSynchronous('nextPlayerTurnEndGameScoring');

                dojo.subscribe('endGameCategoryUpdate', this, "notif_endGameCategoryUpdate");
                this.notifqueue.setSynchronous('endGameCategoryUpdate');


            },

            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                if (this.debug) console.log('Entering state: ' + stateName, args);

                dojo.attr($('swd'), 'data-state', stateName);

                if (args.args && stateName.substring(0, 7) != "client_") {
                    // Update player coins / scores
                    if (args.args.playersSituation) {
                        this.updatePlayersSituation(args.args.playersSituation);
                    }

                    if (args.args.draftpool) this.updateDraftpool(args.args.draftpool);
                    if (args.args.wondersSituation) this.updateWondersSituation(args.args.wondersSituation);

                    // We chose to group all of the states' functions together, so we create a seperate "onEnter{StateName}" function and call it here if it exists.
                    var functionName = 'onEnter' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
                    if (typeof this[functionName] === 'function') {
                        this[functionName](args.args);
                    }
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


                    case 'dummmy':
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
                    jsDisplayCost: displayCost ? 'inline-block' : 'none',
                    jsCost: this.getCostValue(cost),
                    jsCostColor: this.getCostColor(cost, playerCoins),
                };
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
            twistAnimation: function (oldNode, newNode,) {
                if (!oldNode || dojo.style(oldNode, 'display') == 'none' || oldNode.innerHTML != newNode.innerHTML) {
                    var displayValue = dojo.style(newNode, 'display'); // probably inline-block or block.
                    dojo.style(newNode, 'display', 'none');

                    var anims = [];
                    if (oldNode) {
                        dojo.place(oldNode, newNode.parentElement);
                        var oldNodeAnim = dojo.animateProperty({
                            node: oldNode,
                            duration: this.twistCoinDuration / 2,
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
                        duration: this.twistCoinDuration / 2,
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
                    anim.play();
                } else {
                    dojo.destroy(oldNode);
                }
            },

            updatePlayerWonders: function (playerId, rows) {
                if (this.debug) console.log('updatePlayerWonders', playerId, rows);
                var i = 1;
                Object.keys(rows).forEach(dojo.hitch(this, function (index) {
                    var row = rows[index];
                    var container = dojo.query('.player_wonders.player' + playerId + '>div:nth-of-type(' + row.position + ')')[0];

                    var oldNode = dojo.query('.wonder_container', container)[0];

                    var wonderDivHtml = this.getWonderDivHtml(row.wonder, row.constructed == 0, row.cost, this.gamedatas.playersSituation[playerId].coins);
                    var newNode = dojo.place(wonderDivHtml, container);
                    if (row.constructed > 0) {
                        var age = row.constructed;
                        id = 77 + age;
                        var data = {
                            jsData: '',
                            jsId: id
                        };
                        var spritesheetColumns = 12;
                        data.jsX = (id - 1) % spritesheetColumns;
                        data.jsY = Math.floor((id - 1) / spritesheetColumns);
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
                };
                var spritesheetColumns = 12;
                data.jsX = (id - 1) % spritesheetColumns;
                data.jsY = Math.floor((id - 1) / spritesheetColumns);
                data.jsOrder = 0;
                if (building.type == "Green") {
                    data.jsOrder = building.scientificSymbol.toString() + building.age.toString();
                }

                return this.format_block('jstpl_player_building', data);
            },

            updatePlayerBuildings: function (playerId, cards) {
                if (cards.constructor == Object) {
                    var i = 1;
                    Object.keys(cards).forEach(dojo.hitch(this, function (buildingId) {
                        var building = this.gamedatas.buildings[buildingId];
                        var container = dojo.query('.player_buildings.player' + playerId + ' .' + building.type)[0];
                        dojo.place(this.getBuildingDivHtml(buildingId, building), container);
                        i++;
                    }));
                }
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
            updateDraftpool: function (draftpool, setupGame = false) {
                if (this.debug) console.log('updateDraftpool: ', draftpool, setupGame, 'age: ', draftpool.age);

                dojo.style('draftpool_container', 'display', draftpool.age > 0 ? 'block' : 'none');

                // New age. Animate the build up
                if (draftpool.age > 0 && draftpool.age >= this.currentAge) {
                    this.currentAge = draftpool.age; // This currentAge business is a bit of dirty check to prevent older notifications (due to animations finishing) arriving after newer notifications. Especially when a new age has arrived.
                    this.gamedatas.draftpool = draftpool;

                    this.setCssVariable('--draftpool-row-height-multiplier', draftpool.age == 3 ? 0.4 : 0.536);

                    var animationDelay = 200; // Have some initial delay, so this function can finish updating the DOM.
                    for (var i = 0; i < draftpool.cards.length; i++) {
                        var position = draftpool.cards[i];

                        var oldNode = $(position.row + '_' + position.column);

                        var spriteId = null;
                        var linkedBuildingId = 0;
                        var data = {
                            jsId: '',
                            jsName: '',
                            jsRow: position.row,
                            jsColumn: position.column,
                            jsZindex: position.row,
                            jsAvailable: position.available ? 'available' : '',
                            jsDisplayCostMe: 'none',
                            jsCostColorMe: 'black',
                            jsCostMe: -1,
                            jsDisplayCostOpponent: 'none',
                            jsCostColorOpponent: 'black',
                            jsCostOpponent: -1,
                            jsLinkX: 0,
                            jsLinkY: 0,
                        };
                        if (typeof position.building != 'undefined') {
                            var buildingData = this.gamedatas.buildings[position.building];
                            spriteId = position.building;
                            data.jsId = position.building;
                            data.jsName = _(buildingData.name);
                            if (position.available) {
                                data.jsDisplayCostMe = position.available ? 'block' : 'none',
                                    data.jsCostColorMe = this.getCostColor(position.cost[this.me_id], this.gamedatas.playersSituation[this.me_id].coins),
                                    data.jsCostMe = this.getCostValue(position.cost[this.me_id]);
                                data.jsDisplayCostOpponent = position.available ? 'block' : 'none',
                                    data.jsCostColorOpponent = this.getCostColor(position.cost[this.opponent_id], this.gamedatas.playersSituation[this.opponent_id].coins),
                                    data.jsCostOpponent = this.getCostValue(position.cost[this.opponent_id]);
                            }

                            // Linked building symbol
                            linkedBuildingId = buildingData.linkedBuilding;
                            if (linkedBuildingId > 0) {
                                var spritesheetColumns = 9;
                                var linkedBuildingSpriteId = this.gamedatas.buildingIdsToLinkIconId[linkedBuildingId];
                                data.jsLinkX = (linkedBuildingSpriteId - 1) % spritesheetColumns;
                                data.jsLinkY = Math.floor((linkedBuildingSpriteId - 1) / spritesheetColumns);
                            }
                        } else {
                            spriteId = position.back;
                        }
                        var spritesheetColumns = 12;
                        data.jsX = (spriteId - 1) % spritesheetColumns;
                        data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);

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

                    // Adjust the height of the age divs based on the age cards absolutely positioned within.
                    var rows = draftpool.age == 3 ? 7 : 5;
                    dojo.query('.draftpool').style("height", "calc(var(--building-height) * var(--building-small-scale) + " + (rows - 1) + " * var(--draftpool-row-height))");

                    this.updateLayout();

                    return animationDelay + this.putDraftpoolCard;
                }
                return 0;
            },

            createDiscardedBuildingNode: function (buildingId) {
                var buildingData = this.gamedatas.buildings[buildingId];
                var spriteId = buildingId;
                var linkedBuildingId = 0;
                var data = {
                    jsId: buildingId,
                    jsName: _(buildingData.name),
                    jsRow: '',
                    jsColumn: '',
                    jsZindex: 1,
                    jsAvailable: '',
                    jsDisplayCostMe: 'none',
                    jsCostColorMe: 'black',
                    jsCostMe: -1,
                    jsDisplayCostOpponent: 'none',
                    jsCostColorOpponent: 'black',
                    jsCostOpponent: -1,
                    jsLinkX: 0,
                    jsLinkY: 0,
                };

                var spritesheetColumns = 12;
                data.jsX = (spriteId - 1) % spritesheetColumns;
                data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);

                // Set up a wrapper div so we can move the building to pos 0,0 of that wrapper
                var discardedCardsContainer = $('discarded_cards_container');
                var wrapperDiv = dojo.clone(dojo.query('.discarded_cards_cursor', discardedCardsContainer)[0]);
                dojo.removeClass(wrapperDiv, 'discarded_cards_cursor');
                dojo.place(wrapperDiv, discardedCardsContainer, discardedCardsContainer.children.length - 1);

                var newNode = dojo.place(this.format_block('jstpl_draftpool_building', data), 'draftpool');
                dojo.attr(newNode, 'id', ''); // Remove the draftpool specific id
                dojo.place(newNode, wrapperDiv);
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
                this.progressTokensSituation = progressTokensSituation;

                dojo.query("#board_progress_tokens>div").forEach(dojo.empty);

                for (var i = 0; i < progressTokensSituation.board.length; i++) {
                    var location = progressTokensSituation.board[i];
                    var progressToken = this.gamedatas.progressTokens[location.id];
                    var position = parseInt(progressTokensSituation.board[i].location_arg);
                    var container = dojo.query('#board_progress_tokens>div:nth-of-type(' + (position + 1) + ')')[0];
                    dojo.place(this.getProgressTokenDivHtml(progressToken.id), container);
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

                Object.keys(deckCards).forEach(dojo.hitch(this, function (index) {
                    var container = dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens>div:nth-of-type(' + (parseInt(index) + 1) + ')')[0]; // parseInt(index) is apparently necessary?
                    dojo.empty(container);
                    var deckCard = deckCards[index];
                    dojo.place(this.getProgressTokenDivHtml(deckCard.id), container);
                }));
                this.maybeShowSecondRowProgressTokens(playerId);
            },

            maybeShowSecondRowProgressTokens: function (playerId) {
                var nodes = dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens>div>div');
                var containers = dojo.query('.player_info.' + this.getPlayerAlias(playerId) + ' .player_area_progress_tokens>div');
                for (var i = 4; i <= 5; i++) {
                    dojo.style(containers[i - 1], 'display', nodes.length >= 3 ? 'inline-block' : 'none');
                }
                dojo.style(containers[6 - 1], 'display', nodes.length >= 5 ? 'inline-block' : 'none');
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

                let militaryTokenText = _('Military token: the opponent of the active player discards ${x} coins');
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

                this.addTooltip('conflict_pawn',
                    _('Conflict pawn: When it enters a zone, active player applies the effect of the corresponding token, then returns it to the box'), ''
                );

                this.addTooltipToClass('science_progress',
                    _('If you gather 6 different scientific symbols, you immediately win the game (Scientific Supremacy)'), '', this.toolTipDelay);


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
                        position: ["above-centered", "below-centered"],
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
                        position: ['before'],
                        showDelay: this.toolTipDelay,
                        getContent: dojo.hitch(this, function (node) {
                            var id = dojo.attr(node, "data-progress-token-id");
                            return this.getProgressTokenTooltip(id);
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
                    } else {
                        data.cardType = _("Guild card");
                    }
                    data.jsName = _(building.name);
                    data.jsBuildingTypeColor = building.typeColor;
                    data.jsBuildingTypeDescription = _(building.typeDescription);
                    data.jsText = this.getTextHtml(building.text);
                    data.jsBackX = ((id - 1) % spritesheetColumns);
                    data.jsBackY = Math.floor((id - 1) / spritesheetColumns);
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
                    output += dojo.string.substitute(steps[i].string, {costIcon: steps[i].cost ? this.getResourceIcon('coin', steps[i].cost) : ''});
                    output += '<br/>';
                }
                return output;
            },

            getTextHtml: function (text) {
                if (text instanceof Array) {
                    if (text.length == 0) return '';
                    else if (text.length == 1) return _(text[0]);
                    else {
                        var string = '';
                        for (let i = 0; i < text.length; i++) {
                            string += _(text[i]);
                            if (i < text.length - 1) {
                                string += "</li><li>";
                            }
                        }
                        return "<ul><li>" + string + "</li></ul>";
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

            updatePlayersSituation: function (situation) {
                if (this.debug) console.log('updatePlayersSituation', situation)
                this.gamedatas.playersSituation = situation;
                for (var playerId in this.gamedatas.players) {
                    $('player_area_' + playerId + '_coins').innerHTML = situation[playerId].coins;
                    $('player_area_' + playerId + '_score').innerHTML = situation[playerId].score;
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
                    }
                }
            },

            //  __        __              _                      _           _   _
            //  \ \      / /__  _ __   __| | ___ _ __   ___  ___| | ___  ___| |_(_) ___  _ __
            //   \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__| / __|/ _ \ |/ _ \/ __| __| |/ _ \| '_ \
            //    \ V  V / (_) | | | | (_| |  __/ |    \__ \  __/ |  __/ (__| |_| | (_) | | | |
            //     \_/\_/ \___/|_| |_|\__,_|\___|_|    |___/\___|_|\___|\___|\__|_|\___/|_| |_|


            onEnterSelectWonder: function (args) {
                $('wonder_selection_block_title').innerText = dojo.string.substitute(_("Wonders selection - round ${round} of 2"), {
                    round: args.round
                });

                if (args.updateWonderSelection) {
                    this.callFunctionAfterLoading(dojo.hitch(this, "updateWonderSelection"), [args.wonderSelection])
                }
            },

            callFunctionAfterLoading: function(functionToCall, args) {
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
                                dojo.place(this.node, container); // This will hide the outline of the container, which is why we do it as late as possible.
                                dojo.style(wonderNode, 'display', 'inline-block');
                                dojo.style(this.node, 'opacity', 0);
                            },
                            onAnimate: function (values) {
                                dojo.style(wonderNode, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg) scale(' + parseFloat(values.propertyScale.replace("px", "")) + ')');
                            }
                        }).play();
                        animationDelay += this.putDraftpoolCard * 0.75;

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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionSelectWonder.html", {
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
                this.notifqueue.setSynchronousDuration(anim.duration);
            },

            //   ____  _                         _____
            //  |  _ \| | __ _ _   _  ___ _ __  |_   _|   _ _ __ _ __
            //  | |_) | |/ _` | | | |/ _ \ '__|   | || | | | '__| '_ \
            //  |  __/| | (_| | |_| |  __/ |      | || |_| | |  | | | |
            //  |_|   |_|\__,_|\__, |\___|_|      |_| \__,_|_|  |_| |_|
            //                 |___/

            onEnterPlayerTurn: function (args) {
                if (this.debug) console.log('in onEnterPlayerTurn', args);
            },

            onPlayerTurnDraftpoolClick: function (e) {
                if (this.debug) console.log('onPlayerTurnDraftpoolClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                if (this.isCurrentPlayerActive()) {
                    this.clearPlayerTurnNodeGlow();
                    this.clearRedBorder();

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

                    dojo.setStyle('draftpool_actions', 'visibility', 'visible');

                    this.setClientState("client_useAgeCard", {
                        descriptionmyturn: "${you} must choose the action for the age card, or select a different age card.",
                    });
                }
            },

            clearPlayerTurnNodeGlow: function () {
                if (this.playerTurnNode) {
                    dojo.removeClass(this.playerTurnNode, 'glow');
                }
            },

            clearRedBorder: function () {
                Object.keys(this.gamedatas.wondersSituation[this.me_id]).forEach(dojo.hitch(this, function (index) {
                    var wonderData = this.gamedatas.wondersSituation[this.me_id][index];
                    dojo.removeClass($('wonder_' + wonderData.wonder), 'red_border');
                }));
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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionConstructBuilding.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
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

                var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];
                var buildingNodeParent = buildingNode.parentElement; // Only used when we are constructing a discarded building.

                var building = this.gamedatas.buildings[notif.args.buildingId];
                var container = dojo.query('.player_buildings.player' + notif.args.playerId + ' .' + building.type)[0];
                var playerBuildingContainer = dojo.place(this.getBuildingDivHtml(notif.args.buildingId, building), container, "last");
                var playerBuildingId = 'player_building_' + notif.args.buildingId;

                this.placeOnObjectPos(playerBuildingId, buildingNode, 0.5 * this.getCssVariable('--scale'), -59.5 * this.getCssVariable('--scale'));
                dojo.style(playerBuildingId, 'opacity', 0);
                dojo.style(playerBuildingId, 'z-index', 20);

                this.updateLayout(); // If the building is added to the highest stack, part of the layout will be pushed below the viewport. So it's better to update the layout now so all animations will be fully visible.

                var playerAlias = this.getPlayerAlias(notif.args.playerId);
                var coinNode = dojo.query('.draftpool_building_cost.' + playerAlias + ' .coin', buildingNode)[0];
                var position = this.getDraftpoolCardData(notif.args.buildingId);

                var buildingMoveAnim = this.slideToObjectPos(playerBuildingId, playerBuildingContainer, 0, 0, this.constructBuildingAnimationDuration * 0.6);
                if (notif.args.payment.discardedCard) {
                    dojo.connect(buildingMoveAnim, 'onEnd', dojo.hitch(this, function (node) {
                        var whiteblock = $('discarded_cards_whiteblock');
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
                        (position ? position.cost[notif.args.playerId] : 0) - notif.args.payment.economyProgressTokenCoins,
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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionDiscardBuilding.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
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
                    Object.keys(this.gamedatas.wondersSituation[this.player_id]).forEach(dojo.hitch(this, function (index) {
                        var wonderData = this.gamedatas.wondersSituation[this.player_id][index];
                        if (!wonderData.constructed) {
                            if (wonderData.cost <= this.gamedatas.playersSituation[this.player_id].coins) {
                                dojo.addClass($('wonder_' + wonderData.wonder), 'red_border');
                            }
                        }
                    }));

                    this.setClientState("client_useAgeCard", {
                        descriptionmyturn: "${you} must select a wonder to construct, or select a different card or action.",
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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionConstructWonder.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId,
                            wonderId: wonderId,
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionChooseOpponentBuilding.html", {
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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionChooseDiscardedBuilding.html", {
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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionChooseProgressToken.html", {
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

                var container = dojo.query('.player_info.' + this.getPlayerAlias(notif.args.playerId) + ' .player_area_progress_tokens>div:nth-of-type(' + notif.args.progressTokenPosition + ')')[0];
                var progressTokenNode = dojo.query("[data-progress-token-id=" + notif.args.progressTokenId + "]")[0];
                if (progressTokenNode) {
                    progressTokenNode = this.attachToNewParent(progressTokenNode, container);
                }
                else {
                    // In case of "Choose progress token from box", the inactive player doesn't have the chosen
                    // progress token on the screen yet, so we place the progress token on the Wonder.
                    progressTokenNode = dojo.place(this.getProgressTokenDivHtml(notif.args.progressTokenId), container);
                    this.placeOnObject(progressTokenNode, 'wonder_6');
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
                    this.maybeShowSecondRowProgressTokens(notif.args.playerId);
                }));

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(anim.duration + this.notification_safe_margin);

                anim.play();
            },

            notif_nextAgeDraftpoolReveal: function (notif) {
                var animationDuration = this.updateDraftpool(notif.args.draftpool);

                // Wait for animation before handling the next notification (= state change).
                this.notifqueue.setSynchronousDuration(animationDuration);
            },

            getPlayerCoinContainer: function (playerId, oppositePlayerInstead = false) {
                var playerAlias = this.getPlayerAlias(oppositePlayerInstead ? this.getOppositePlayerId(playerId) : playerId);
                return dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0];
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

                    this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionSelectStartPlayer.html", {
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

            //   ____ _                                                                      _        _                 __                       _
            //  / ___| |__   ___   ___  ___  ___   _ __  _ __ ___   __ _ _ __ ___  ___ ___  | |_ ___ | | _____ _ __    / _|_ __ ___  _ __ ___   | |__   _____  __
            // | |   | '_ \ / _ \ / _ \/ __|/ _ \ | '_ \| '__/ _ \ / _` | '__/ _ \/ __/ __| | __/ _ \| |/ / _ \ '_ \  | |_| '__/ _ \| '_ ` _ \  | '_ \ / _ \ \/ /
            // | |___| | | | (_) | (_) \__ \  __/ | |_) | | | (_) | (_| | | |  __/\__ \__ \ | || (_) |   <  __/ | | | |  _| | | (_) | | | | | | | |_) | (_) >  <
            //  \____|_| |_|\___/ \___/|___/\___| | .__/|_|  \___/ \__, |_|  \___||___/___/  \__\___/|_|\_\___|_| |_| |_| |_|  \___/|_| |_| |_| |_.__/ \___/_/\_\
            //                                    |_|              |___/

            onEnterChooseProgressTokenFromBox: function (args) {
                if (this.debug) console.log('onEnterChooseProgressTokenFromBox', args);
                if (this.isCurrentPlayerActive()) {
                    Object.keys(args._private.progressTokensFromBox).forEach(dojo.hitch(this, function (progressTokenId) {
                        var card = args._private.progressTokensFromBox[progressTokenId];
                        var container = dojo.query('#progress_token_from_box_container>div:nth-of-type(' + (parseInt(card.location_arg) + 1) + ')')[0];
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

                this.ajaxcall("/sevenwondersduelagora/sevenwondersduelagora/actionChooseProgressTokenFromBox.html", {
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
                if (this.debug) console.log('scientificSupremacyAnimation', playersSituation);
                dojo.addClass(dojo.query('.player' + playersSituation.winner + ' .player_building_column.Green')[0], 'endgame_highlight');
                var progressTokenNode = $('progress_token_4');
                if (progressTokenNode) {
                    dojo.addClass(progressTokenNode, 'endgame_highlight');
                }

                // Unset endGameCondition to prevent an infinite loop.
                playersSituation.endGameCondition = undefined;
                this.updatePlayersSituation(playersSituation);
                return 500;
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
                var categoryNode = dojo.query('#end_game_container .end_game_' + notif.args.category + '.player' + notif.args.playerId)[0];
                var totalNode = dojo.query('#end_game_container .end_game_total.player' + notif.args.playerId + ' span')[0];
                dojo.addClass(categoryNode, 'endgame_highlight');
                var zIndex = 1;
                if (notif.args.highlightId) {
                    zIndex = dojo.style(notif.args.highlightId, 'z-index');
                    dojo.style(notif.args.highlightId, 'z-index', 100);
                    dojo.addClass(notif.args.highlightId, 'endgame_highlight');
                }
                var anim = dojo.fx.chain([
                    dojo.animateProperty({
                        node: 'swd',
                        duration: 400
                    }),
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
                    }),
                    dojo.animateProperty({
                        node: 'swd',
                        duration: 400,
                        onEnd: function (node) {
                            dojo.removeClass(categoryNode, 'endgame_highlight');
                            if (notif.args.highlightId) {
                                dojo.style(notif.args.highlightId, 'z-index', zIndex);
                                dojo.removeClass(notif.args.highlightId, 'endgame_highlight');
                            }
                        }
                    }),
                    dojo.animateProperty({
                        node: 'swd',
                        duration: 200
                    }),
                ]);

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
                $('setting_layout').value = layout;
            },

            setScale: function (scale) {
                this.setCssVariable('--scale', scale);
                $('setting_scale').value = parseInt(scale * 100);
            },

            getCssVariable: function (name) {
                return getComputedStyle(document.documentElement).getPropertyValue(name);
            },

            setCssVariable: function (name, value) {
                document.documentElement.style.setProperty(name, value);
            },

            onScreenWidthChange: function () {
                this.viewportChange();
            },

            onWindowUpdate: function (e) {
                this.viewportChange();
            },

            viewportChange: function (e) {
                clearTimeout(this.windowResizeTimeoutId);
                // Set up the callback
                this.windowResizeTimeoutId = setTimeout(dojo.hitch(this, "updateLayout"), 50);
            },

            updateLayout: function () {
                // TODO: When BGA stops using the "zoom" css attribute for page-content and other elements, this function should be rewritten / simplified making it much more precise.
                var titlePosition = dojo.position('page-title', false);
                var titleMarginBottom = 5;

                var pageZoom = dojo.style($('page-content'), "zoom");
                if (pageZoom == undefined) pageZoom = 1;

                // At the end of the game there's more room taken up by bars up top but no idea how to reliably calculate that.
                var endGameScaleCompensation = ($('maingameview_menuheader') && dojo.style($('maingameview_menuheader'), 'display') != 'none') ? 0.93 : 1.0;

                var width = titlePosition.w; // - 5
                var height = (window.innerHeight / pageZoom * endGameScaleCompensation - titlePosition.y - titlePosition.h - 2 * titleMarginBottom);

                var debugPlayArea = $('debugPlayArea');
                dojo.style(debugPlayArea, "width", width + 'px');
                dojo.style(debugPlayArea, "height", height + 'px');

                var ratio = width / height;

                if (this.debug) console.log('titlePosition: ', titlePosition);
                if (this.debug) console.log('available play area: ', width, height);
                if (this.debug) console.log('ratio', ratio);
                if (this.debug) console.log('pageZoom', pageZoom);

                // Measured in 75% view, without any player buildings (meaning the height can become heigher:
                var portrait = 0.78;//747 / 987; // 0.76
                // var square = 947 / 897; // 1.056
                var landscape = 1.6; //1131/ 756; // 1.60

                if (this.autoLayout) {
                    if (ratio >= landscape) {
                        if (this.debug) console.log('ratio: ', ratio, 'choosing landscape');
                        this.layout = this.LAYOUT_LANDSCAPE;
                    } else if (ratio < landscape && ratio > portrait) {
                        if (this.debug) console.log('ratio: ', ratio, 'choosing square');
                        this.layout = this.LAYOUT_SQUARE;
                    } else { // ratio <= portrait
                        if (this.debug) console.log('ratio: ', ratio, 'choosing portrait');
                        this.layout = this.LAYOUT_PORTRAIT;
                    }
                }

                switch(this.layout) {
                    case this.LAYOUT_LANDSCAPE:
                        Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                            dojo.place('player_wonders_' + playerId, 'player_wonders_container_' + playerId);
                        }));

                        this.setLayout(this.LAYOUT_LANDSCAPE);
                        if (this.autoScale && !this.freezeLayout) {
                            this.setScale(1);
                            this.scale = height / dojo.style($('swd_wrap'), 'height');
                        }
                        this.setScale(this.scale);
                        break;
                    case this.LAYOUT_SQUARE:
                        Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                            dojo.place('player_wonders_' + playerId, 'player_wonders_container_' + playerId);
                        }));

                        this.setLayout(this.LAYOUT_SQUARE);
                        if (this.autoScale && !this.freezeLayout) {
                            if (width > height) {
                                this.setScale(1);
                                this.scale = height / dojo.style($('swd_wrap'), 'height');
                            } else {
                                this.setScale(1);
                                this.scale = width / dojo.style($('layout_flexbox'), 'width');
                            }
                        }
                        this.setScale(this.scale);
                        break;
                    case this.LAYOUT_PORTRAIT:
                        Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                            dojo.place('player_wonders_' + playerId, 'player_wonders_mobile_container_' + playerId);
                        }));

                        this.setLayout(this.LAYOUT_PORTRAIT);
                        if (this.autoScale && !this.freezeLayout) {
                            this.setScale(1);
                            if (ratio <= portrait) {
                                this.scale = width / dojo.style($('layout_flexbox'), 'width');
                            }
                            else {
                                this.scale = height / dojo.style($('swd_wrap'), 'height');
                            }
                        }
                        this.setScale(this.scale);
                        break;
                }

                dojo.style($('discarded_cards_whiteblock'), 'width', $('layout_flexbox').offsetWidth + 'px');
                dojo.style($('settings_whiteblock'), 'width', $('layout_flexbox').offsetWidth + 'px');
            },

            onSettingAutoScaleChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingAutoScaleChange');

                this.autoScale = 1 - parseInt(this.autoScale);
                this.updateLayout();
                this.updateSettings();

                dojo.cookie('swd_autoScale', this.autoScale, { expires: this.cookieExpireDays });
            },

            onSettingScaleChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingScaleChange');

                if (!this.autoScale) {
                    this.scale = Math.min(200, Math.max(50, parseInt(e.target.value))) / 100;
                    this.updateLayout();

                    dojo.cookie('swd_scale', this.scale, { expires: this.cookieExpireDays });
                }
                else {
                    dojo.cookie("swd_scale", null, {expires: -1});
                }
                this.updateSettings();
            },

            onSettingAutoLayoutChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingAutoLayoutChange');

                this.autoLayout = 1 - parseInt(this.autoLayout);
                this.updateLayout();
                this.updateSettings();

                dojo.cookie('swd_autoLayout', this.autoLayout, { expires: this.cookieExpireDays });
            },

            onSettingLayoutChange: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);
                if (this.debug) console.log('onSettingLayoutChange');

                if (!this.autoLayout) {
                    this.layout = e.target.value;
                    this.updateLayout();
                    dojo.cookie('swd_layout', this.layout, { expires: this.cookieExpireDays });
                }
                else {
                    dojo.cookie("swd_layout", null, {expires: -1});
                }
                this.updateSettings();
            },

            updateSettings: function() {
                if (this.autoScale) {
                    dojo.attr('setting_auto_scale', 'checked', 'checked');
                    dojo.attr('setting_scale', 'disabled', '');
                }
                else {
                    dojo.removeAttr('setting_auto_scale', 'checked');
                    dojo.removeAttr('setting_scale', 'disabled');
                }
                dojo.style('setting_scale_description', 'display', this.autoScale ? 'none' : 'inline-block');

                if (this.autoLayout) {
                    dojo.attr('setting_auto_layout', 'checked', 'checked');
                    dojo.attr('setting_layout', 'disabled', '');
                }
                else {
                    dojo.removeAttr('setting_auto_layout', 'checked');
                    dojo.removeAttr('setting_layout', 'disabled');
                }

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
                return playerId == this.me_id ? this.opponent_id : this.me_id;
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
            }

        });
    });
