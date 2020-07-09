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
        "ebg/core/gamegui",
        "ebg/counter"
    ],
    function (dojo, declare, on, dom) {
        return declare("bgagame.sevenwondersduel", ebg.core.gamegui, {
            constructor: function () {
                // Debug settings
                this.dontScale = 0;

                // Tooltip settings
                dijit.Tooltip.defaultPosition = ["above-centered", "below-centered"];
                this.toolTipDelay = 750;

                // Game logic properties
                this.playerTurnBuildingId = null;
                this.playerTurnNode = null;
                this.currentAge = 0;

                // General properties
                // this.windowResizeTimeoutId = null;

                // Animation
                this.constructBuildingAnimationDuration = 1000;
                this.discardBuildingAnimationDuration = 400;
                this.constructWonderAnimationDuration = 1600;
                this.turnAroundCardDuration = 500;
                this.putDraftpoolCard = 250;
                this.militaryTrackStepDuration = 200;
                this.coin_slide_duration = 500;
                this.coin_slide_delay = 100;
                this.notification_safe_margin = 200;
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
                console.log("setup(gamedatas)", gamedatas);

                dojo.destroy('debug_output'); // TODO: Remove? See http://en.doc.boardgamearena.com/Tools_and_tips_of_BGA_Studio#Speed_up_game_re-loading_by_disabling_Input.2FOutput_debug_section

                this.gamedatas = gamedatas;

                // Because of spectators we can't assume everywhere that this.player_id is one of the two players.
                this.me_id = parseInt(this.gamedatas.playerorder[0]); // me = alias for the player on the bottom
                this.opponent_id = parseInt(this.gamedatas.playerorder[1]); // opponent = alias for the player on the top

                // Setup game situation.
                this.updateWondersSituation(this.gamedatas.wondersSituation);
                this.updateDraftpool(this.gamedatas.draftpool, true);
                this.updateProgressTokensBoard(this.gamedatas.progressTokensBoard);
                this.updateMilitaryTrack(this.gamedatas.militaryTrack);
                this.updateDiscardedBuildings(this.gamedatas.discardedBuildings);

                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];

                    // TODO: Setting up players boards if needed
                    this.updatePlayerWonders(player_id, this.gamedatas.wondersSituation[player_id]);
                    this.updatePlayerBuildings(player_id, this.gamedatas.playerBuildings[player_id]);
                    this.updatePlayerCoins(player_id, this.gamedatas.playersSituation[player_id].coins);
                }

                // Click handlers using event delegation:
                dojo.query('#wonder_selection_container').on(".wonder:click", dojo.hitch(this, "onWonderSelectionClick"));
                dojo.query('#draftpool').on(".building.available:click", dojo.hitch(this, "onPlayerTurnDraftpoolClick"));
                dojo.query('#player_wonders_' + this.me_id).on(".wonder_small.wonder_selectable:click", dojo.hitch(this, "onPlayerTurnConstructWonderSelectedClick"));

                // Click hide the tooltip:
                dojo.query('#swd_wrap').on("*:click", dojo.hitch(this, "hideTooltip"));

                // Click handlers without event delegation:
                dojo.query("#buttonConstructBuilding").on("click", dojo.hitch(this, "onPlayerTurnConstructBuildingClick"));
                dojo.query("#buttonDiscardBuilding").on("click", dojo.hitch(this, "onPlayerTurnDiscardBuildingClick"));
                dojo.query("#buttonConstructWonder").on("click", dojo.hitch(this, "onPlayerTurnConstructWonderClick"));

                // Resize/scroll handler to determine layout and scale factor
                window.addEventListener('resize', dojo.hitch(this, "onWindowUpdate"));
                window.addEventListener('scroll', dojo.hitch(this, "onWindowUpdate"));

                // Tool tips using event delegation:
                this.setupTooltips();

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                // Debug tooltip content by placing a tooltip at the top of the screen.
                // dojo.place( this.getBuildingTooltip( 44, true ), 'swd_wrap', 'first' );
            },

            setupTooltips: function () {
                // Add tooltips to buildings everywhere.
                new dijit.Tooltip({
                    connectId: "game_play_area",
                    selector: '.building_small, .building_header_small',
                    showDelay: this.toolTipDelay,
                    getContent: dojo.hitch(this, function (node) {
                        var id = dojo.attr(node, "data-building-id");
                        var draftpoolBuilding = dojo.query(node).closest("#draftpool")[0];
                        var meCoinHtml;
                        var opponentCoinHtml;
                        if (draftpoolBuilding) {
                            meCoinHtml = dojo.query('.me .coin', node)[0].outerHTML;
                            opponentCoinHtml = dojo.query('.opponent .coin', node)[0].outerHTML;
                        }
                        return this.getBuildingTooltip(id, draftpoolBuilding, meCoinHtml, opponentCoinHtml);
                    })
                });

                // Add tooltips to wonders everywhere.
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
                });

                // Add tooltips to progress tokens everywhere.
                new dijit.Tooltip({
                    connectId: "game_play_area",
                    selector: '.progress_token_small',
                    position: ['before'],
                    showDelay: this.toolTipDelay,
                    getContent: dojo.hitch(this, function (node) {
                        var id = dojo.attr(node, "data-progress-token-id");
                        return this.getProgressTokenTooltip(id);
                    })
                });
            },

            updateProgressTokensBoard: function (progressTokensBoard) {
                console.log('updateProgressTokensBoard: ', progressTokensBoard);
                this.progressTokensBoard = progressTokensBoard;

                for (var i = 0; i < 5; i++) {
                    var location = progressTokensBoard[i];
                    var progressToken = this.gamedatas.progressTokens[location.id];
                    var container = dojo.query('#board_progress_tokens>div:nth-of-type(' + (i + 1) + ')')[0];
                    dojo.empty(container);
                    if (typeof location != 'undefined') {
                        var data = {
                            jsId: progressToken.id,
                            jsData: 'data-progress-token-id="' + progressToken.id + '"',
                        };
                        var spritesheetColumns = 4;
                        data.jsX = (progressToken.id - 1) % spritesheetColumns;
                        data.jsY = Math.floor((progressToken.id - 1) / spritesheetColumns);
                        dojo.place(this.format_block('jstpl_board_progress_token', data), container);
                    }
                }
            },

            getPlayerAlias: function(playerId) {
                return playerId == this.me_id ? 'me' : 'opponent'
            },

            getOppositePlayerId: function(playerId) {
                return playerId == this.me_id ? this.opponent_id : this.me_id;
            },

            increasePlayerCoins: function (playerId, coins) {
                $('player_area_' + playerId + '_coins').innerHTML = parseInt($('player_area_' + playerId + '_coins').innerHTML) + coins;
            },

            updatePlayerCoins: function (playerId, coins) {
                console.log('updatePlayerCoins', playerId, coins)
                this.gamedatas.playersSituation[playerId].coins = coins;
                $('player_area_' + playerId + '_coins').innerHTML = coins;
            },

            getBuildingDivHtml: function (id) {
                var data = {
                    jsId: id,
                };
                var spritesheetColumns = 10;
                data.jsX = (id - 1) % spritesheetColumns;
                data.jsY = Math.floor((id - 1) / spritesheetColumns);

                return this.format_block('jstpl_player_building', data);
            },

            getWonderDivHtml: function (wonderId, displayCost, cost, playerCoins) {
                var data = {
                    jsId: wonderId,
                    jsDisplayCost: displayCost ? 'inline-block' : 'none',
                    jsCost: this.getCostValue(cost),
                    jsCostColor: this.getCostColor(cost, playerCoins),
                };
                var spritesheetColumns = 5;
                data.jsX = (wonderId - 1) % spritesheetColumns;
                data.jsY = Math.floor((wonderId - 1) / spritesheetColumns);
                return this.format_block('jstpl_wonder', data);
            },

            hideTooltip: function() {
                // Thanks to https://stackoverflow.com/a/35984527
                if (dijit.Tooltip._masterTT) {
                    dijit.Tooltip._masterTT.containerNode.innerHTML='';
                    dojo.removeClass(dijit.Tooltip._masterTT.id, "dijitTooltip");
                }
            },

            updatePlayerWonders: function (playerId, rows) {
                console.log('updatePlayerWonders', playerId, rows);
                var i = 1;
                Object.keys(rows).forEach(dojo.hitch(this, function (index) {
                    var container = dojo.query('.player_wonders.player' + playerId + '>div:nth-of-type(' + i + ')')[0];
                    dojo.empty(container);
                    var row = rows[index];
                    var wonderDivHtml = this.getWonderDivHtml(row.wonder, row.constructed == 0, row.cost, this.gamedatas.playersSituation[playerId].coins);
                    var wonderDiv = dojo.place(wonderDivHtml, container);
                    if (row.constructed > 0) {
                        var age = row.constructed;
                        id = 73 + age;
                        var data = {
                            jsData: '',
                            jsId: id
                        };
                        var spritesheetColumns = 10;
                        data.jsX = (id - 1) % spritesheetColumns;
                        data.jsY = Math.floor((id - 1) / spritesheetColumns);
                        dojo.place(this.format_block('jstpl_wonder_age_card', data), dojo.query('.age_card_container', wonderDiv)[0]);
                    }
                    i++;
                }));
            },

            updatePlayerBuildings: function (playerId, cards) {
                if (cards.constructor == Object) {
                    var i = 1;
                    Object.keys(cards).forEach(dojo.hitch(this, function (buildingId) {
                        var building = this.gamedatas.buildings[buildingId];
                        var container = dojo.query('.player_buildings.player' + playerId + ' .' + building.type)[0];
                        dojo.place(this.getBuildingDivHtml(buildingId), container);
                        i++;
                    }));
                }
            },

            updateWonderSelection: function (cards) {
                var block = $('wonder_selection_block');
                if (cards.length > 0) {
                    var position = 1;
                    Object.keys(cards).forEach(dojo.hitch(this, function (index) {
                        var card = cards[index];
                        var container = dojo.query('#wonder_selection_container>div:nth-of-type(' + (parseInt(card.location_arg) + 1) + ')')[0];
                        dojo.empty(container);
                        dojo.place(this.getWonderDivHtml(card.id, false), container);
                        position++;
                    }));

                    dojo.style(block, "display", "block");
                } else {
                    dojo.style(block, "display", "none");
                }
            },

            getWonderCardData: function (playerId, wonderId) {
                for (var i = 0; i < this.gamedatas.wondersSituation[playerId].length; i++) {
                    var cardData = this.gamedatas.wondersSituation[playerId][i];
                    if (cardData.wonder == wonderId) {
                        return cardData;
                    }
                }
                return null;
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

            updateDraftpool: function (draftpool, setupGame) {
                if (typeof setupGame == 'undefined') setupGame = false;
                console.log('updateDraftpool: ', draftpool, setupGame, 'age: ', draftpool.age);

                dojo.style('draftpool_container', 'display', draftpool.age > 0 ? 'block' : 'none');
                if (draftpool.age > 0 && draftpool.age >= this.currentAge) {
                    this.currentAge = draftpool.age; // This currentAge business is a bit of dirty check to prevent older notifications (due to animations finishing) arriving after newer notifications. Especially when a new age has arrived.
                    this.gamedatas.draftpool = draftpool;

                    this.setCssVariable('--draftpool-row-height-multiplier', draftpool.age == 3 ? 0.4 : 0.536);

                    var animationDelay = 300; // Have some initial delay, so this function can finish updating the DOM.
                    for (var i = 0; i < draftpool.cards.length; i++) {
                        var position = draftpool.cards[i];

                        var oldNode = $(position.row + '_' + position.column);

                        var spriteId = null;
                        var linkedBuildingId = 0;
                        var data = {
                            jsId: '',
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
                            spriteId = position.building;
                            data.jsId = position.building;
                            data.jsDisplayCostMe = position.available ? 'block' : 'none',
                            data.jsCostColorMe = this.getCostColor(position.cost[this.me_id], this.gamedatas.playersSituation[this.me_id].coins),
                            data.jsCostMe = this.getCostValue(position.cost[this.me_id]);
                            data.jsDisplayCostOpponent = position.available ? 'block' : 'none',
                            data.jsCostColorOpponent = this.getCostColor(position.cost[this.opponent_id], this.gamedatas.playersSituation[this.opponent_id].coins),
                            data.jsCostOpponent = this.getCostValue(position.cost[this.opponent_id]);

                            // Linked building symbol
                            linkedBuildingId = this.gamedatas.buildings[position.building].linkedBuilding;
                            if (linkedBuildingId > 0) {
                                var spritesheetColumns = 9;
                                var linkedBuildingSpriteId = this.gamedatas.buildingIdsToLinkIconId[linkedBuildingId];
                                data.jsLinkX = (linkedBuildingSpriteId - 1) % spritesheetColumns;
                                data.jsLinkY = Math.floor((linkedBuildingSpriteId - 1) / spritesheetColumns);
                            }
                        } else {
                            spriteId = position.back;
                        }
                        var spritesheetColumns = 10;
                        data.jsX = (spriteId - 1) % spritesheetColumns;
                        data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);

                        var newNode = dojo.place(this.format_block('jstpl_draftpool_building', data), 'draftpool');

                        // Remove linked symbols dom elements that aren't needed.
                        Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                            if (linkedBuildingId == 0 || !position.hasLinkedBuilding[playerId]) {
                                dojo.destroy(dojo.query('.' + this.getPlayerAlias(playerId) + ' .linked_building_icon', newNode)[0]);
                            }
                        }));

                        if (oldNode) {
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
                            else {
                                dojo.destroy(oldNode);
                            }
                        }
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
                }

            },

            getCoinAnimationDuration: function(amount) {
                if (amount != 0) {
                    return this.coin_slide_duration + ((Math.abs(amount) - 1) * this.coin_slide_delay);
                }
                return 0;
            },
            getCoinAnimation: function(sourceNode, targetNode, amount, playerId, sourcePosition, targetPosition) {
                // Optional source/target positions
                if (typeof sourcePosition == "undefined") sourcePosition = [0, 0];
                if (typeof targetPosition == "undefined") targetPosition = [0, 0];

                // Auto detect if coins are moving to or from certain players player areas. We use this to update their coin total during the animation.
                var sourceNodePlayerId = undefined;
                var targetNodePlayerId = undefined;
                if (dojo.hasClass(sourceNode, 'player_area_coins') && dojo.query(sourceNode).closest(".me")[0]) sourceNodePlayerId = this.me_id;
                if (dojo.hasClass(sourceNode, 'player_area_coins') && dojo.query(sourceNode).closest(".opponent")[0]) sourceNodePlayerId = this.opponent_id;
                if (dojo.hasClass(targetNode, 'player_area_coins') && dojo.query(targetNode).closest(".me")[0]) targetNodePlayerId = this.me_id;
                if (dojo.hasClass(targetNode, 'player_area_coins') && dojo.query(targetNode).closest(".opponent")[0]) targetNodePlayerId = this.opponent_id;

                var anims = [];
                if (amount != 0) {
                    var html = this.format_block('jstpl_coin_animated');
                    for (var i = 0; i < Math.abs(amount); i++) {
                        var node = dojo.place(html, 'swd_wrap');
                        if (playerId == this.opponent_id) {
                            dojo.addClass(node, 'opponent');
                        }
                        this.placeOnObjectPos(node, sourceNode, sourcePosition[0], sourcePosition[1]);

                        dojo.style(node, 'opacity', 0);
                        var fadeDurationPercentage = 0.15;
                        var anim = dojo.fx.combine([
                            dojo.fadeIn({
                                node: node,
                                duration: this.coin_slide_duration * fadeDurationPercentage,
                                delay: i * this.coin_slide_delay,
                                onPlay: dojo.hitch(this, function (node) {
                                    if (sourceNodePlayerId) {
                                        this.increasePlayerCoins(sourceNodePlayerId, -1);
                                    }
                                }),
                            }),
                            this.slideToObjectPos(node, targetNode, targetPosition[0], targetPosition[1], this.coin_slide_duration, i * this.coin_slide_delay),
                            dojo.animateProperty({ // Standard fadeOut started of at opacity 0 (?!?)
                                node: node,
                                duration: this.coin_slide_duration * fadeDurationPercentage,
                                delay: (i * this.coin_slide_delay) + ((1 - fadeDurationPercentage) * this.coin_slide_duration),
                                easing: dojo.fx.easing.linear,
                                properties: {
                                    opacity: {
                                        start: 1,
                                        end: 0
                                    }
                                },
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                    if (targetNodePlayerId) {
                                        this.increasePlayerCoins(targetNodePlayerId, 1);
                                    }
                                }),
                            }),
                        ]);
                        anims.push(anim);
                    }
                }
                var anim = dojo.fx.combine(anims);
                return anim;
            },

            getMilitaryTokenAnimation: function(active_player_id, payment) {
                console.log('getMilitaryTokenAnimation', payment);
                // The military token animation always concerns the opponent of the active player.
                player_id = this.getOppositePlayerId(active_player_id);
                var anims = [];
                if (payment.militaryTokenNumber > 0) {
                    var playerAlias = player_id == this.me_id ? 'me' : 'opponent';
                    var tokenNumber = this.invertMilitaryTrack() ? (5 - payment.militaryTokenNumber) : payment.militaryTokenNumber;
                    var tokenNode = dojo.query('#military_tokens>div:nth-of-type(' + tokenNumber + ')>.military_token')[0];
                    var coinAnimation = this.getCoinAnimation(
                        dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0],
                        tokenNode,
                        -payment.militaryTokenValue,
                        player_id
                    );
                    console.log('getCoinAnimation', dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0],
                        tokenNode,
                        -payment.militaryTokenValue,
                        player_id);
                    anims.push(coinAnimation);
                }

                var anim = dojo.fx.combine(anims);
                return anim;
            },

            getCostValue: function(cost) {
                if (typeof cost == "undefined") return false;

                if (cost == 0) {
                    return '✓';
                } else {
                    return cost;
                }
            },

            getCostColor: function(cost, playerCoins) {
                if (typeof cost == "undefined") return false;

                if (cost == 0) {
                    return '#008000';
                } else if(cost <= playerCoins) {
                    return 'black';
                } else {
                    return 'red';
                }
            },

            updateWondersSituation: function (situation) {
                this.gamedatas.wondersSituation = situation;
                this.updateWonderSelection(situation.selection);
                for (var player_id in this.gamedatas.players) {
                    this.updatePlayerWonders(player_id, situation[player_id]);
                }
            },

            updateDiscardedBuildings: function(discardedBuildings) {
                this.gamedatas.discardedBuildings = discardedBuildings;

                Object.keys(this.gamedatas.discardedBuildings).forEach(dojo.hitch(this, function (index) {
                    var building = this.gamedatas.discardedBuildings[index];

                    var spriteId = building.id;
                    var linkedBuildingId = 0;
                    var data = {
                        jsId: building.id,
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

                    var spritesheetColumns = 10;
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
                }));
            },

            invertMilitaryTrack: function() {
                return this.me_id == this.gamedatas.startPlayerId;
            },

            updateMilitaryTrack: function(militaryTrack) {
                var oldPosition = this.gamedatas.militaryTrack.conflictPawn;
                this.gamedatas.militaryTrack = militaryTrack;
                var steps = Math.abs(oldPosition - militaryTrack.conflictPawn);

                if (oldPosition != militaryTrack.conflictPawn) {
                    dojo.animateProperty({
                        node: $('conflict_pawn'),
                        duration: this.militaryTrackStepDuration * steps,
                        easing: dojo.fx.easing.linear,
                        properties: {
                            propertyConflictPawnPosition: {
                                start: this.invertMilitaryTrack() ? -oldPosition : oldPosition,
                                end: this.invertMilitaryTrack() ? -militaryTrack.conflictPawn : militaryTrack.conflictPawn
                            }
                        },
                        onAnimate: dojo.hitch(this, function (values) {
                            this.setCssVariable('--conflict-pawn-position', parseFloat(values.propertyConflictPawnPosition.replace("px", "")));
                        }),
                    }).play();
                }
                else {
                    // For the start of the game / F5
                    this.setCssVariable('--conflict-pawn-position', this.me_id == this.gamedatas.startPlayerId ? -militaryTrack.conflictPawn : militaryTrack.conflictPawn);
                }
            },

            getBuildingTooltip: function (id, draftpoolBuilding, meCoinHtml, opponentCoinHtml) {
                if (typeof this.gamedatas.buildings[id] != 'undefined') {
                    var building = this.gamedatas.buildings[id];

                    var spritesheetColumns = 10;

                    var data = {};
                    data.ageRoman = "I".repeat(building.age);
                    data.name = building.name;
                    data.backx = ((id - 1) % spritesheetColumns);
                    data.backy = Math.floor((id - 1) / spritesheetColumns);
                    data.jsCostMe = '';
                    data.jsCostOpponent = '';
                    if (draftpoolBuilding) {
                        var position = this.getDraftpoolCardData(id);

                        data.jsCostMe = this.format_block('jstpl_tooltip_cost_me', {
                            jsCoinHtml: meCoinHtml,
                            jsPayment: this.getPaymentPlan(position.payment[this.me_id])
                        });

                        data.jsCostOpponent = this.format_block('jstpl_tooltip_cost_opponent', {
                            jsCoinHtml: opponentCoinHtml,
                            jsPayment: this.getPaymentPlan(position.payment[this.opponent_id])
                        });
                    }

                    return this.format_block('jstpl_building_tooltip', data);
                }
                return false;
            },

            getPaymentPlan: function(data) {
                var output = '<ul>';
                var steps = data.steps;
                for (var i = 0; i < steps.length; i++) {
                    output += '<li>' + steps[i].string + '</li>';
                }
                output += '</ul>';
                return output;
            },

            getWonderTooltip: function (id, playerId, coinHtml) {
                if (typeof this.gamedatas.wonders[id] != 'undefined') {
                    var wonder = this.gamedatas.wonders[id];

                    var spritesheetColumns = 5;

                    var data = {};
                    data.name = wonder.name;
                    data.backx = ((id - 1) % spritesheetColumns);
                    data.backy = Math.floor((id - 1) / spritesheetColumns);
                    data.jsCost = '';
                    if (playerId) {
                        var cardData = this.getWonderCardData(playerId, id);
                        if (!cardData.constructed) {
                            data.jsCost = this.format_block(playerId == this.me_id ? 'jstpl_tooltip_cost_me' : 'jstpl_tooltip_cost_opponent', {
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
                    data.name = progressToken.name;
                    data.backx = ((id - 1) % spritesheetColumns);
                    data.backy = Math.floor((id - 1) / spritesheetColumns);
                    return this.format_block('jstpl_progress_token_tooltip', data);
                }
                return false;
            },

            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                console.log('Entering state: ' + stateName);

                switch (stateName) {

                    /* Example:

                    case 'myGameState':

                        // Show some HTML block at this game state
                        dojo.style( 'my_html_block_id', 'display', 'block' );

                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                console.log('Leaving state: ' + stateName);

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
                console.log('onUpdateActionButtons: ' + stateName);

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

            ///////////////////////////////////////////////////
            //// Utility methods

            /*

                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.

            */


            ///////////////////////////////////////////////////
            //// Player's action

            /*

                Here, you are defining methods to handle player's action (ex: results of mouse click on
                game objects).

                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server

            */

            /* Example:

            onMyMethodToCall1: function( evt )
            {
                console.log( 'onMyMethodToCall1' );

                // Preventing default browser reaction
                dojo.stopEvent( evt );

                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if( ! this.checkAction( 'myAction' ) )
                {   return; }

                this.ajaxcall( "/sevenwondersduel/sevenwondersduel/myAction.html", {
                                                                        lock: true,
                                                                        myArgument1: arg1,
                                                                        myArgument2: arg2,
                                                                        ...
                                                                     },
                             this, function( result ) {

                                // What to do after the server call if it succeeded
                                // (most of the time: nothing)

                             }, function( is_error) {

                                // What to do after the server call in anyway (success or failure)
                                // (most of the time: nothing)

                             } );
            },

            */

            onWonderSelectionClick: function (e) {
                console.log('onWonderSelectionClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                this.hideTooltip();

                if (this.isCurrentPlayerActive()) {
                    var wonder = dojo.query(e.target);

                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionSelectWonder')) {
                        return;
                    }

                    this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionSelectWonder.html", {
                            lock: true,
                            wonderId: wonder.attr('data-wonder-id')
                        },
                        this, function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                            // Hide wonder selection
                            // dojo.style('pattern_selection', 'display', 'none');

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        });
                }
            },

            onPlayerTurnDraftpoolClick: function (e) {
                console.log('onPlayerTurnDraftpoolClick');
                // Preventing default browser reaction
                dojo.stopEvent(e);

                this.hideTooltip();

                if (this.isCurrentPlayerActive()) {
                    this.clearPlayerTurnNodeGlow();
                    this.clearActionGlow();

                    var building = dojo.query(e.target);

                    dojo.addClass(e.target, 'glow');

                    this.playerTurnBuildingId = dojo.attr(e.target, 'data-building-id');
                    this.playerTurnNode = e.target;

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
                        descriptionmyturn : "${you} must choose the action for the age card, or select a different age card.",
                    });
                }
            },

            onPlayerTurnConstructBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                console.log('onPlayerTurnConstructBuildingClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionConstructBuilding')) {
                        return;
                    }

                    // Set notification delay dynamically:
                    var position = this.getDraftpoolCardData(this.playerTurnBuildingId);
                    var building = this.gamedatas.buildings[this.playerTurnBuildingId];
                    this.notifqueue.setSynchronous( 'constructBuilding',
                        this.getCoinAnimationDuration(position.cost[this.player_id]) + this.constructBuildingAnimationDuration + this.getCoinAnimationDuration(building.coins) + this.notification_safe_margin
                    );

                    this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionConstructBuilding.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                            // Hide wonder selection
                            // dojo.style('pattern_selection', 'display', 'none');

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            onPlayerTurnDiscardBuildingClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                console.log('onPlayerTurnDiscardBuildingClick');

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionDiscardBuilding')) {
                        return;
                    }

                    // Set notification delay dynamically:
                    this.notifqueue.setSynchronous( 'discardBuilding',
                        this.getCoinAnimationDuration(this.gamedatas.draftpool.discardGain[this.player_id]) + this.discardBuildingAnimationDuration + this.notification_safe_margin
                    );

                    this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionDiscardBuilding.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                            // Hide wonder selection
                            // dojo.style('pattern_selection', 'display', 'none');

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            clearPlayerTurnNodeGlow: function () {
                if (this.playerTurnNode) {
                    dojo.removeClass(this.playerTurnNode, 'glow');
                }
            },

            clearActionGlow: function () {
                Object.keys(this.gamedatas.wondersSituation[this.me_id]).forEach(dojo.hitch(this, function (index) {
                    var wonderData = this.gamedatas.wondersSituation[this.me_id][index];
                    dojo.removeClass($('wonder_' + wonderData.wonder), 'wonder_selectable');
                }));
            },

            onPlayerTurnConstructWonderClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                console.log('onPlayerTurnConstructWonderClick');

                if (this.isCurrentPlayerActive()) {
                    Object.keys(this.gamedatas.wondersSituation[this.player_id]).forEach(dojo.hitch(this, function (index) {
                        var wonderData = this.gamedatas.wondersSituation[this.player_id][index];
                        if (!wonderData.constructed) {
                            if (wonderData.cost <= this.gamedatas.playersSituation[this.player_id].coins) {
                                dojo.addClass($('wonder_' + wonderData.wonder), 'wonder_selectable');
                            }
                        }
                    }));

                    this.setClientState("client_useAgeCard", {
                        descriptionmyturn : "${you} must select a wonder to construct, or select a different card or action.",
                    });
                }
            },
            onPlayerTurnConstructWonderSelectedClick: function (e) {
                // Preventing default browser reaction
                dojo.stopEvent(e);

                console.log('onPlayerTurnConstructWonderSelectedClick', e);

                if (this.isCurrentPlayerActive()) {
                    // Check that this action is possible (see "possibleactions" in states.inc.php)
                    if (!this.checkAction('actionConstructWonder')) {
                        return;
                    }

                    var wonderId = dojo.attr(e.target, "data-wonder-id");

                    // Set notification delay dynamically:
                    var position = this.getWonderCardData(this.player_id, wonderId);
                    var wonder = this.gamedatas.wonders[wonderId];
                    this.notifqueue.setSynchronous( 'constructWonder',
                        this.getCoinAnimationDuration(position.cost) + this.constructWonderAnimationDuration + this.getCoinAnimationDuration(wonder.coins) + this.notification_safe_margin
                    );

                    this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionConstructWonder.html", {
                            lock: true,
                            buildingId: this.playerTurnBuildingId,
                            wonderId: wonderId,
                        },
                        this, function (result) {
                            dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                            // Hide wonder selection
                            // dojo.style('pattern_selection', 'display', 'none');

                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                        }
                    );
                }
            },

            getOffset: function (el) {
                var rect = el.getBoundingClientRect();
                return {
                    left: rect.left + window.scrollX,
                    top: rect.top + window.scrollY
                };
            },

            setScale: function (scale) {
                if (!this.dontScale) {
                    this.setCssVariable('--scale', scale);
                }
            },

            getCssVariable: function (name) {
                return document.documentElement.style.getPropertyValue(name);
            },

            setCssVariable: function (name, value) {
                document.documentElement.style.setProperty(name, value);
            },

            updateLayout: function () {
                var titlePosition = dojo.position('page-title', false);
                var titleMarginBottom = 5;
                var width = titlePosition.w - 5;
                var height = window.innerHeight - titlePosition.y - titlePosition.h - 2 * titleMarginBottom;

                var playarea = $('playarea');
                dojo.style(playarea, "width", width + 'px');
                dojo.style(playarea, "height", height + 'px');

                // console.log('titlePosition: ', titlePosition);
                // console.log('available play area: ', width, height);
                var ratio = window.innerWidth / window.innerHeight;

                var pageZoom = dojo.style($('page-content'), "zoom");
                // console.log('pageZoom', pageZoom);

                // Measured in 75% view, without any player buildings (meaning the height can become heigher:
                var portrait = 0.8;//747 / 987; // 0.76
                // var square = 947 / 897; // 1.056
                var landscape = 1.74; //1131/ 756; // 1.60

                var swdNode = $('swd_wrap');
                dojo.removeClass(swdNode, 'square');
                dojo.removeClass(swdNode, 'portrait');
                dojo.removeClass(swdNode, 'landscape');

                if (ratio >= landscape) {
                    // console.log('ratio: ', ratio, 'choosing landscape');
                    dojo.addClass(swdNode, 'landscape');
                    this.setScale(1);
                    this.setScale(height / dojo.style($('swd_wrap'), 'height'));
                } else if (ratio < landscape && ratio > portrait) {
                    Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                        dojo.place('player_wonders_' + playerId, 'player_wonders_container_' + playerId);
                    }));

                    // console.log('ratio: ', ratio, 'choosing square');
                    dojo.addClass(swdNode, 'square');
                    if (width > height) {
                        this.setScale(1);
                        this.setScale(height / dojo.style($('swd_wrap'), 'height'));
                    } else {
                        this.setScale(1);
                        this.setScale(width / dojo.style($('layout_flexbox'), 'width'));
                    }

                } else { // ratio <= portrait
                    Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function (playerId) {
                        dojo.place('player_wonders_' + playerId, 'player_wonders_mobile_container_' + playerId);
                    }));

                    // console.log('ratio: ', ratio, 'choosing portrait');
                    dojo.addClass(swdNode, 'portrait');
                    this.setScale(1);
                    this.setScale(width / dojo.style($('layout_flexbox'), 'width'));
                }
                // console.log('swd_wrap height: ', $('swd_wrap'), 'height');
            },

            onScreenWidthChange: function () {
                this.updateLayout();
            },

            onWindowUpdate: function (e) {
                this.updateLayout();

                // clearTimeout(this.windowResizeTimeoutId);
                // // Set up the callback
                // this.windowResizeTimeoutId = setTimeout(dojo.hitch(this, "updateLayout"), 10);
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
                console.log('notifications subscriptions setup');

                // TODO: here, associate your game notifications with local methods

                // Example 1: standard notification handling
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

                // Example 2: standard notification handling + tell the user interface to wait
                //            during 3 seconds after calling the method in order to var the players
                //            see what is happening in the game.
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
                // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
                //

                dojo.subscribe('wonderSelected', this, "notif_wonderSelected");
                dojo.subscribe('nextAge', this, "notif_nextAge");

                dojo.subscribe('constructBuilding', this, "notif_constructBuilding");
                // Notification delay is set dynamically in onPlayerTurnConstuctBuildingClick

                dojo.subscribe('discardBuilding', this, "notif_discardBuilding");
                // Notification delay is set dynamically in onPlayerTurnDiscardBuildingClick

                dojo.subscribe('constructWonder', this, "notif_constructWonder");
                // Notification delay is set dynamically in onPlayerTurnConstructWonderSelectedClick

                dojo.subscribe('updateDraftpool', this, "notif_updateDraftpool");
            },

            // TODO: from this point and below, you can write your game notifications handling methods

            notif_updateDraftpool: function (notif) {
                console.log('notif_updateDraftpool', notif);
                this.updateDraftpool(notif.args.draftpool)
            },

            notif_wonderSelected: function (notif) {
                console.log('notif_wonderSelected', notif);
                this.hideTooltip();

                var wonderContainerNodeId = 'wonder_' + notif.args.wonderId + '_container';
                var targetNode = dojo.query('.player_wonders.player' + notif.args.playerId + '>div:nth-of-type(' + notif.args.playerWonderCount + ')')[0];
                this.attachToNewParent(wonderContainerNodeId, targetNode);
                this.slideToObjectPos(wonderContainerNodeId, targetNode, 0, 0).play();

                if (notif.args.updateWonderSelection) {
                    this.updateWonderSelection(notif.args.wonderSelection);
                }
            },

            notif_nextAge: function (notif) {
                console.log('notif_nextAge', notif);
                this.updateWondersSituation(notif.args.wondersSituation);
                this.updateDraftpool(notif.args.draftpool);
            },

            notif_constructBuilding: function (notif) {
                console.log('notif_constructBuilding', notif);

                this.updateMilitaryTrack(notif.args.militaryTrack);
                this.scoreCtrl[notif.args.playerId].setValue(notif.args.playerScore);

                var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];

                var building = this.gamedatas.buildings[notif.args.buildingId];
                var container = dojo.query('.player_buildings.player' + notif.args.playerId + ' .' + building.type)[0];
                var playerBuildingContainer = dojo.place(this.getBuildingDivHtml(notif.args.buildingId, dojo.attr(buildingNode, "data-card-id")), container, notif.args.playerId == this.me_id ? "last" : "first");
                var playerBuildingId = 'player_building_' + notif.args.buildingId;

                this.placeOnObjectPos(playerBuildingId, dojo.attr(buildingNode, "id"), 0.5 * this.getCssVariable('--scale'), -59.5 * this.getCssVariable('--scale'));
                dojo.style(playerBuildingId, 'opacity', 0);
                dojo.style(playerBuildingId, 'z-index', 20);

                var playerAlias = this.getPlayerAlias(notif.args.playerId);
                var coinNode = dojo.query('.draftpool_building_cost.' + playerAlias + ' .coin', buildingNode)[0];
                var position = this.getDraftpoolCardData(notif.args.buildingId);
                var coinAnimation = this.getCoinAnimation(
                    dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0],
                    coinNode,
                    -position.cost[notif.args.playerId],
                    notif.args.playerId
                );

                var coinRewardAnimation = undefined;
                if (building.coins > 0) {
                    coinRewardAnimation = this.getCoinAnimation(
                        playerBuildingContainer,
                        dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0],
                        building.coins,
                        notif.args.playerId
                    );
                }
                else {
                    coinRewardAnimation = dojo.fx.combine([]);
                }

                var militaryTokenAnimation = this.getMilitaryTokenAnimation(notif.args.playerId, notif.args.payment);

                var anim = dojo.fx.chain([
                    coinAnimation,
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
                    this.slideToObjectPos(playerBuildingId, playerBuildingContainer, 0, 0, this.constructBuildingAnimationDuration * 0.6),
                    coinRewardAnimation,
                    militaryTokenAnimation,
                ]);

                dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                    // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayerCoins.
                    anim.stop();
                    // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                    dojo.query("#swd_wrap .coin.animated").forEach(dojo.destroy);

                    dojo.style(playerBuildingId, 'z-index', 15);
                    this.updatePlayerCoins(notif.args.playerId, notif.args.playersSituation[notif.args.playerId].coins);
                    this.updateDraftpool(notif.args.draftpool);
                    this.updateWondersSituation(notif.args.wondersSituation);
                }));

                anim.play();
            },

            notif_discardBuilding: function (notif) {
                console.log('notif_discardBuilding', notif);

                this.clearPlayerTurnNodeGlow();
                this.clearActionGlow();

                var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];

                var coinAnimation = this.getCoinAnimation(
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

                dojo.connect(moveAnim, 'onEnd', dojo.hitch(this, function (node) {
                    this.updatePlayerCoins(notif.args.playerId, notif.args.playersSituation[notif.args.playerId].coins);
                    this.updateDraftpool(notif.args.draftpool);
                    this.updateWondersSituation(notif.args.wondersSituation);
                }));

                var anim = dojo.fx.chain([
                    coinAnimation,
                    moveAnim
                ]);

                anim.play();
            },

            notif_constructWonder: function (notif) {
                console.log('notif_constructWonder', notif);

                this.clearPlayerTurnNodeGlow();
                this.clearActionGlow();

                var wonderContainer = dojo.query('#player_wonders_' + notif.args.playerId + ' #wonder_' + notif.args.wonderId + '_container')[0];
                var playerAlias = this.getPlayerAlias(notif.args.playerId);
                var coinNode = dojo.query('.player_wonder_cost', wonderContainer)[0];
                var position = this.getWonderCardData(notif.args.playerId, notif.args.wonderId);
                console.log('getCoinAnimation', dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0],
                    coinNode,
                    -position.cost,
                    notif.args.playerId);
                var coinAnimation = this.getCoinAnimation(
                    dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0],
                    coinNode,
                    -position.cost,
                    notif.args.playerId
                );

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
                        var coinRewardAnimation = undefined;
                        if (wonder.coins > 0) {
                            coinRewardAnimation = this.getCoinAnimation(
                                wonderNode,
                                dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0],
                                wonder.coins,
                                notif.args.playerId,
                                [wonder.visualCoinPosition[0] * wonderNodePosition.w, wonder.visualCoinPosition[1] * wonderNodePosition.h]
                            );
                        }
                        else {
                            coinRewardAnimation = dojo.fx.combine([]);
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
                                    }
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
                        ]);

                        dojo.connect(anim, 'beforeBegin', dojo.hitch(this, function () {
                            dojo.style(wonderNode, 'z-index', 20);
                            dojo.style(ageCardNode, 'transform', 'rotate(0deg) perspective(40em) rotateY(-90deg)'); // The rotateY(-90deg) affects the position the element will end up after the slide. Here's the place to apply it therefor, not before the animation instantiation.
                        }));
                        dojo.connect(anim, 'onEnd', dojo.hitch(this, function (node) {
                            // Stop the animation. If we don't do this, the onEnd of the last individual coin animation can trigger after this, causing the player coin total to be +1'ed after being updated by this.updatePlayerCoins.
                            anim.stop();
                            // Clean up any existing coin nodes (normally cleaned up by their onEnd)
                            dojo.query("#swd_wrap .coin.animated").forEach(dojo.destroy);

                            dojo.style(ageCardNode, 'z-index', 1);
                            dojo.style(wonderNode, 'z-index', 2);
                            this.updatePlayerCoins(notif.args.playerId, notif.args.playersSituation[notif.args.playerId].coins);
                            this.updateDraftpool(notif.args.draftpool);
                            this.scoreCtrl[notif.args.playerId].setValue(notif.args.playerScore);
                        }));

                        anim.play();
                    }

                }));
                coinAnimation.play();


            },

            /*
            Example:

            notif_cardPlayed: function( notif )
            {
                console.log( 'notif_cardPlayed' );
                console.log( notif );

                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

                // TODO: play the card in the user interface.
            },

            */
        });
    });
