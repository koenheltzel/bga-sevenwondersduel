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
    "dojo/on",
    "dojo/dom",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare, on, dom) {
    return declare("bgagame.sevenwondersduel", ebg.core.gamegui, {
        constructor: function(){
            // Tooltip settings
            this.toolTipDelay = 500;
            this.windowResizeTimeoutId = null;
            this.playerTurnCardId = null;
            this.playerTurnBuildingId = null;
            this.playerTurnNode = null;
            dijit.Tooltip.defaultPosition = ["above-centered", "below-centered"];
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
        
        setup: function( gamedatas )
        {
            console.log( "setup(gamedatas)", gamedatas );

            this.gamedatas = gamedatas;

            // Setup game situation.
            this.updateWondersSituation(this.gamedatas.wondersSituation);
            this.updateDraftpool(this.gamedatas.draftpool);
            this.updateProgressTokensBoard(this.gamedatas.progressTokensBoard);

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];

                // TODO: Setting up players boards if needed
                this.updatePlayerWonders(player_id, this.gamedatas.wondersSituation[player_id]);
                this.updatePlayerBuildings(player_id, this.gamedatas.playerBuildings[player_id]);
                this.updatePlayerCoins(player_id, this.gamedatas.playerCoins[player_id]);
            }

            // Click handlers using event delegation:
            dojo.query('#wonder_selection_container').on(".wonder:click", dojo.hitch(this, "onWonderSelectionClick"));
            dojo.query('#draftpool').on(".building.available:click", dojo.hitch(this, "onPlayerTurnDraftpoolClick"));
            dojo.query('#player_wonders_' + this.player_id).on(".wonder_small.wonder_selectable:click", dojo.hitch(this, "onPlayerTurnConstructWonderSelectedClick"));
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
            //dojo.place( this.getBuildingTooltip( 22 ), 'swd_wrap', 'first' );
        },

        setupTooltips: function () {
            // Add tooltips to buildings everywhere.
            new dijit.Tooltip({
                connectId: "game_play_area",
                selector: '.building_small, .building_header_small',
                showDelay: this.toolTipDelay,
                getContent: dojo.hitch( this, function(node) {
                    var id = dojo.attr(node, "data-building-id");
                    return this.getBuildingTooltip(id);
                })
            });

            // Add tooltips to wonders everywhere.
            new dijit.Tooltip({
                connectId: "game_play_area",
                selector: '.wonder_small',
                showDelay: this.toolTipDelay,
                getContent: dojo.hitch( this, function(node) {
                    var id = dojo.attr(node, "data-wonder-id");
                    return this.getWonderTooltip(id);
                })
            });

            // Add tooltips to progress tokens everywhere.
            new dijit.Tooltip({
                connectId: "game_play_area",
                selector: '.progress_token_small',
                position: ['before'],
                showDelay: this.toolTipDelay,
                getContent: dojo.hitch( this, function(node) {
                    var id = dojo.attr(node, "data-progress-token-id");
                    return this.getProgressTokenTooltip( id );
                })
            });
        },

        updateProgressTokensBoard: function (progressTokensBoard) {
            console.log('updateProgressTokensBoard: ', progressTokensBoard);
            this.progressTokensBoard = progressTokensBoard;

            for (var i = 0; i < 5; i++) {
                var location = progressTokensBoard[i];
                var progressToken = this.gamedatas.progressTokens[location.type_arg];
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

        updatePlayerCoins: function (playerId, coins) {
            this.gamedatas.playerCoins[playerId] = coins;
            var node = dojo.query('#player_area_' + playerId + '_coins')[0];
            node.innerHTML = coins;
        },

        getBuildingDivHtml: function (id, cardId) {
            var data = {
                jsCardId: cardId,
                jsId: id,
            };
            var spritesheetColumns = 10;
            data.jsX = (id - 1) % spritesheetColumns;
            data.jsY = Math.floor((id - 1) / spritesheetColumns);

            return this.format_block('jstpl_player_building', data);
        },

        getWonderDivHtml: function (cardId, wonderId, displayCost, cost, canAfford) {
            if (typeof cost == "undefined") {
                cost = -1;
            }
            var data = {
                jsCardId: cardId,
                jsId: wonderId,
                jsDisplayCost: displayCost && cost > -1 ? 'inline-block' : 'none',
                jsCost: cost,
                jsCostColor: canAfford ? 'black' : 'red',
            };
            var spritesheetColumns = 5;
            data.jsX = (wonderId - 1) % spritesheetColumns;
            data.jsY = Math.floor((wonderId - 1) / spritesheetColumns);
            return this.format_block('jstpl_wonder', data);
        },

        updatePlayerWonders: function (playerId, rows) {
            console.log('updatePlayerWonders', playerId, rows);
            var i = 1;
            Object.keys(rows).forEach(dojo.hitch(this, function(index) {
                var container = dojo.query('.player_wonders.player' + playerId + '>div:nth-of-type(' + i + ')')[0];
                dojo.empty(container);
                var row = rows[index];
                var displayCost = playerId == this.player_id && row.cost;

                var wonderDivHtml = this.getWonderDivHtml(row.card, row.wonder, displayCost, row.cost, row.cost <= this.gamedatas.playerCoins[playerId]);
                var wonderDiv = dojo.place(wonderDivHtml, container);
                console.log(wonderDiv);
                if(row.constructed > 0) {
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
                Object.keys(cards).forEach(dojo.hitch(this, function(cardId) {
                    var building = this.gamedatas.buildings[cards[cardId].type_arg];
                    var container = dojo.query('.player_buildings.player' + playerId + ' .' + building.type)[0];
                    dojo.place(this.getBuildingDivHtml(cards[cardId].type_arg, cardId), container);
                    i++;
                }));
            }
        },

        updateWonderSelection: function (cards) {
            var block = dojo.query('#wonder_selection_block')[0];
            if (cards.length > 0) {
                var position = 1;
                Object.keys(cards).forEach(dojo.hitch(this, function(index) {
                    var card = cards[index];
                    var container = dojo.query('#wonder_selection_container>div:nth-of-type(' + (parseInt(card.location_arg) + 1) + ')')[0];
                    dojo.empty(container);
                    dojo.place(this.getWonderDivHtml(card.id, card.type_arg, false), container);
                    position++;
                }));

                dojo.style(block, "display", "block");
            }
            else {
                dojo.style(block, "display", "none");
            }
        },

        getDraftpoolCardData: function (cardId) {
            for (var i = 0; i < this.gamedatas.draftpool.cards.length; i++) {
                var position = this.gamedatas.draftpool.cards[i];
                if (typeof position.card != 'undefined' && position.card == cardId) {
                    return position;
                }
            }
            return null;
        },

        updateDraftpool: function (draftpool) {
            this.gamedatas.draftpool = draftpool;
            console.log('updateDraftpool: ', draftpool);

            dojo.style('draftpool_container', 'display', draftpool.age > 0 ? 'block' : 'none');
            if (draftpool.age > 0) {
                dojo.empty("draftpool");

                document.documentElement.style.setProperty('--draftpool-row-height-multiplier', draftpool.age == 3 ? 0.4 : 0.536);

                for (var i = 0; i < draftpool.cards.length; i++) {
                    var position = draftpool.cards[i];
                    var spriteId = null;
                    var data = {
                        jsId: '',
                        jsCardId: '',
                        jsRow: position.row,
                        jsColumn: position.column,
                        jsZindex: position.row,
                        jsAvailable: position.available ? 'available' : '',
                        jsDisplayCost: 'none',
                        jsCostColor: 'black',
                        jsCost: -1,
                    };
                    if (typeof position.building != 'undefined') {
                        spriteId = position.building;
                        data.jsId = position.building;
                        data.jsCardId = position.card;
                        data.jsDisplayCost = position.available && position.cost[this.player_id] > 0 ? 'inline-block' : 'none',
                        data.jsCostColor = position.cost[this.player_id] <= this.gamedatas.playerCoins[this.player_id] ? 'black' : 'red';
                        data.jsCost = position.cost[this.player_id];
                    } else {
                        spriteId = position.back;
                    }
                    var spritesheetColumns = 10;
                    data.jsX = (spriteId - 1) % spritesheetColumns;
                    data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);

                    dojo.place(this.format_block('jstpl_draftpool_building', data), 'draftpool');
                }

                // Adjust the height of the age divs based on the age cards absolutely positioned within.
                var rows = draftpool.age == 3 ? 7 : 5;
                dojo.query('.draftpool').style("height", "calc(var(--building-height) * var(--building-small-scale) + " + (rows - 1) + " * var(--draftpool-row-height))");

                this.updateLayout();
            }

        },

        updateWondersSituation: function(situation) {
            this.gamedatas.wondersSituation = situation;
            this.updateWonderSelection(situation.selection);
            for( var player_id in this.gamedatas.players )
            {
                this.updatePlayerWonders(player_id, situation[player_id]);
            }
        },
        
        getBuildingTooltip: function( id )
        {
            if (typeof this.gamedatas.buildings[id] != 'undefined') {
                var building = this.gamedatas.buildings[id];

                var spritesheetColumns = 10;

                var data = {};
                data.name = building.name;
                data.backx = ((id - 1) % spritesheetColumns);
                data.backy = Math.floor((id - 1) / spritesheetColumns);
                return this.format_block( 'jstpl_building_tooltip', data );
            }
            return false;
        },

        getWonderTooltip: function( id )
        {
            if (typeof this.gamedatas.wonders[id] != 'undefined') {
                var wonder = this.gamedatas.wonders[id];

                var spritesheetColumns = 5;

                var data = {};
                data.name = wonder.name;
                data.backx = ((id - 1) % spritesheetColumns);
                data.backy = Math.floor((id - 1) / spritesheetColumns);
                return this.format_block( 'jstpl_wonder_tooltip', data );
            }
            return false;
        },

        getProgressTokenTooltip: function( id )
        {
            if (typeof this.gamedatas.progressTokens[id] != 'undefined') {
                var progressToken = this.gamedatas.progressTokens[id];

                var spritesheetColumns = 4;

                var data = {};
                data.name = progressToken.name;
                data.backx = ((id - 1) % spritesheetColumns);
                data.backy = Math.floor((id - 1) / spritesheetColumns);
                return this.format_block( 'jstpl_progress_token_tooltip', data );
            }
            return false;
        },

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
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
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
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
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
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

            if (this.isCurrentPlayerActive()) {
                var wonder = dojo.query(e.target);
                console.log('wonder ', wonder);
                console.log('data-card-id ', wonder.attr('data-card-id').pop());

                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if (!this.checkAction('actionSelectWonder')) {
                    return;
                }

                this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionSelectWonder.html", {
                    cardId: wonder.attr('data-card-id')
                },
                this, function (result) {
                    console.log('success result: ', result);
                    // What to do after the server call if it succeeded
                    // (most of the time: nothing)

                    // Hide wonder selection
                    // dojo.style('pattern_selection', 'display', 'none');

                }, function (is_error) {
                    console.log('error result: ', is_error);
                    // What to do after the server call in anyway (success or failure)
                    // (most of the time: nothing)

                });
            }
        },

        onPlayerTurnDraftpoolClick: function (e) {
            console.log('onPlayerTurnDraftpoolClick');
            // Preventing default browser reaction
            dojo.stopEvent(e);

            if (this.isCurrentPlayerActive()) {
                if (this.playerTurnNode) {
                    dojo.removeClass(this.playerTurnNode, 'glow');
                }
                this.clearActionGlow();

                var building = dojo.query(e.target);

                dojo.addClass(e.target, 'glow');

                this.playerTurnCardId = dojo.attr(e.target, 'data-card-id');
                this.playerTurnBuildingId = dojo.attr(e.target, 'data-building-id');
                this.playerTurnNode = e.target;

                var cardData = this.getDraftpoolCardData(this.playerTurnCardId);
                dojo.query('#buttonDiscardBuilding .coin>span')[0].innerHTML = '+' + this.gamedatas.draftpool.discardGain[this.player_id];

                var playerCoins = this.gamedatas.playerCoins[this.player_id];

                var canAffordBuilding = cardData.cost[this.player_id] <= playerCoins;
                dojo.removeClass(dojo.query('#buttonConstructBuilding')[0], 'bgabutton_blue');
                dojo.removeClass(dojo.query('#buttonConstructBuilding')[0], 'bgabutton_darkgray');
                dojo.addClass(dojo.query('#buttonConstructBuilding')[0], canAffordBuilding ? 'bgabutton_blue' : 'bgabutton_darkgray');

                var canAffordWonder = false;
                Object.keys(this.gamedatas.wondersSituation[this.player_id]).forEach(dojo.hitch(this, function(index) {
                    var wonderData = this.gamedatas.wondersSituation[this.player_id][index];
                    if (!wonderData.constructed) {
                        if (wonderData.cost <= playerCoins) {
                            canAffordWonder = true;
                        }
                    }
                }));
                dojo.removeClass(dojo.query('#buttonConstructWonder')[0], 'bgabutton_blue');
                dojo.removeClass(dojo.query('#buttonConstructWonder')[0], 'bgabutton_darkgray');
                dojo.addClass(dojo.query('#buttonConstructWonder')[0], canAffordWonder ? 'bgabutton_blue' : 'bgabutton_darkgray');

                dojo.setStyle('draftpool_actions', 'visibility', 'visible');
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

                this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionConstructBuilding.html", {
                        cardId: this.playerTurnCardId
                    },
                    this, function (result) {
                        console.log('success result: ', result);
                        dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
                        // What to do after the server call if it succeeded
                        // (most of the time: nothing)

                        // Hide wonder selection
                        // dojo.style('pattern_selection', 'display', 'none');

                    }, function (is_error) {
                        console.log('error result: ', is_error);
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

                this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionDiscardBuilding.html", {
                        cardId: this.playerTurnCardId
                    },
                    this, function (result) {
                        console.log('success result: ', result);
                        dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
                        // What to do after the server call if it succeeded
                        // (most of the time: nothing)

                        // Hide wonder selection
                        // dojo.style('pattern_selection', 'display', 'none');

                    }, function (is_error) {
                        console.log('error result: ', is_error);
                        // What to do after the server call in anyway (success or failure)
                        // (most of the time: nothing)

                    }
                );
            }
        },

        clearActionGlow: function() {
            Object.keys(this.gamedatas.wondersSituation[this.player_id]).forEach(dojo.hitch(this, function(index) {
                var wonderData = this.gamedatas.wondersSituation[this.player_id][index];
                dojo.removeClass(dojo.query('#wonder_' + wonderData.wonder)[0], 'wonder_selectable');
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
                        if (wonderData.cost <= this.gamedatas.playerCoins[this.player_id]) {
                            dojo.addClass(dojo.query('#wonder_' + wonderData.wonder)[0], 'wonder_selectable');
                        }
                    }
                }));
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

                this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionConstructWonder.html", {
                        cardId: this.playerTurnCardId,
                        wonderId: dojo.attr(e.target, "data-wonder-id"),
                    },
                    this, function (result) {
                        console.log('success result: ', result);
                        dojo.setStyle('draftpool_actions', 'visibility', 'hidden');
                        // What to do after the server call if it succeeded
                        // (most of the time: nothing)

                        // Hide wonder selection
                        // dojo.style('pattern_selection', 'display', 'none');

                    }, function (is_error) {
                        console.log('error result: ', is_error);
                        // What to do after the server call in anyway (success or failure)
                        // (most of the time: nothing)

                    }
                );
            }
        },

        getOffset: function(el) {
            var rect = el.getBoundingClientRect();
            return {
                left: rect.left + window.scrollX,
                top: rect.top + window.scrollY
            };
        },

        setScale: function(scale) {
            console.log('setScale', scale);
            // scale = 0.5;
            this.setCssVariable('--scale', scale);

            // dojo.style(swdNode, "zoom", scale);
        },

        getCssVariable: function(name) {
            return document.documentElement.style.getPropertyValue(name);
        },

        setCssVariable: function(name, value) {
            document.documentElement.style.setProperty(name, value);
        },

        updateLayout: function() {
            var titlePosition = dojo.position('page-title', false);
            var titleMarginBottom = 5;
            var width = titlePosition.w - 5;
            var height = window.innerHeight - titlePosition.y - titlePosition.h - 2 * titleMarginBottom;

            var playarea = dojo.query('#playarea')[0];
            dojo.style(playarea, "width", width + 'px');
            dojo.style(playarea, "height", height + 'px');

            console.log('titlePosition: ', titlePosition);
            console.log('available play area: ', width, height);
            var ratio = window.innerWidth / window.innerHeight;

            var pageZoom = dojo.style(dojo.query('#page-content')[0], "zoom");
            console.log('pageZoom', pageZoom);

            // Measured in 75% view, without any player buildings (meaning the height can become heigher:
            var portrait = 0.8;//747 / 987; // 0.76
            // var square = 947 / 897; // 1.056
            var landscape = 1.74; //1131/ 756; // 1.60

            var swdNode = dojo.query('#swd_wrap')[0];
            dojo.removeClass(swdNode, 'square');
            dojo.removeClass(swdNode, 'portrait');
            dojo.removeClass(swdNode, 'landscape');

            if(ratio >= landscape){
                console.log('ratio: ', ratio, 'choosing landscape');
                dojo.addClass(swdNode, 'landscape');
                this.setScale(1);
                this.setScale(height / dojo.style(dojo.query('#swd_wrap')[0], 'height'));
            }
            else if(ratio < landscape && ratio > portrait){
                Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function(playerId) {
                    dojo.place('player_wonders_' + playerId, 'player_wonders_container_' + playerId);
                }));

                console.log('ratio: ', ratio, 'choosing square');
                dojo.addClass(swdNode, 'square');
                if (width > height) {
                    this.setScale(1);
                    this.setScale(height / dojo.style(dojo.query('#swd_wrap')[0], 'height'));
                }
                else {
                    this.setScale(1);
                    this.setScale(width / dojo.style(dojo.query('#layout_flexbox')[0], 'width'));
                }

            }
            else { // ratio <= portrait
                Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function(playerId) {
                    dojo.place('player_wonders_' + playerId, 'player_wonders_mobile_container_' + playerId);
                }));

                console.log('ratio: ', ratio, 'choosing portrait');
                dojo.addClass(swdNode, 'portrait');
                this.setScale(1);
                this.setScale(width / dojo.style(dojo.query('#layout_flexbox')[0], 'width'));
            }
            console.log('swd_wrap height: ', dojo.query('#swd_wrap')[0], 'height');
        },

        onScreenWidthChange: function () {
            console.log('onScreenWidthChange');
            this.updateLayout();
        },

        onWindowUpdate: function (e) {
            console.log('onWindowUpdate', e);

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
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to var the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //

            dojo.subscribe( 'wonderSelected', this, "notif_wonderSelected" );
            dojo.subscribe( 'nextAge', this, "notif_nextAge" );
            dojo.subscribe( 'constructBuilding', this, "notif_constructBuilding" );
            dojo.subscribe( 'discardBuilding', this, "notif_discardBuilding" );
            dojo.subscribe( 'constructWonder', this, "notif_constructWonder" );
        },
        
        // TODO: from this point and below, you can write your game notifications handling methods

        notif_wonderSelected: function( notif )
        {
            console.log( 'notif_wonderSelected' );
            console.log( notif );
            var wonderNode = dojo.query('#wonder_' + notif.args.wonderId)[0];
            dijit.Tooltip.hide(wonderNode);

            var wonderContainerNodeId = 'wonder_' + notif.args.wonderId + '_container';
            var targetNode = dojo.query('.player_wonders.player' + notif.args.playerId + '>div:nth-of-type(' + notif.args.playerWonderCount + ')')[0];
            this.attachToNewParent(wonderContainerNodeId, targetNode);
            this.slideToObjectPos(wonderContainerNodeId, targetNode, 0, 0).play();

            if (notif.args.updateWonderSelection) {
                this.updateWonderSelection(notif.args.wonderSelection);
            }
        },

        notif_nextAge: function(notif) {
            this.updateWondersSituation(notif.args.wondersSituation);
            this.updateDraftpool(notif.args.draftpool);
        },

        notif_constructBuilding: function(notif) {
            console.log( 'notif_constructBuilding' );
            console.log( notif );

            this.updatePlayerCoins(notif.args.playerId, notif.args.playerCoins);
            this.scoreCtrl[notif.args.playerId].setValue( notif.args.playerScore );

            var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];
            dijit.Tooltip.hide(buildingNode);

            var building = this.gamedatas.buildings[notif.args.buildingId];
            var container = dojo.query('.player_buildings.player' + notif.args.playerId + ' .' + building.type)[0];
            var playerBuildingContainer = dojo.place(this.getBuildingDivHtml(notif.args.buildingId, dojo.attr(buildingNode, "data-card-id")), container, notif.args.playerId == this.player_id ? "last" : "first");
            var playerBuildingId = 'player_building_' + notif.args.buildingId;

            this.placeOnObjectPos(playerBuildingId, dojo.attr(buildingNode, "id"), 0.5 * this.getCssVariable('--scale'),  -59.5 * this.getCssVariable('--scale'));
            dojo.style(playerBuildingId, 'opacity', 0);
            dojo.style(playerBuildingId, 'z-index', 20);

            var anim = dojo.fx.chain( [
                dojo.fx.combine( [
                    dojo.fadeIn( {node:playerBuildingId, duration: 400} ),
                    dojo.fadeOut( {node:buildingNode, duration: 400} ),
                ] ),
                this.slideToObjectPos(playerBuildingId, playerBuildingContainer, 0, 0, 600),
            ] );

            dojo.connect( anim, 'onEnd', dojo.hitch( this, function() {
                dojo.style(playerBuildingId, 'z-index', 15);
                dojo.destroy(buildingNode);
                this.updateDraftpool(notif.args.draftpool);
                this.updateWondersSituation(notif.args.wondersSituation);
            }));

            anim.play();
        },

        notif_discardBuilding: function(notif) {
            console.log( 'notif_discardBuilding' );
            console.log( notif );

            this.updatePlayerCoins(notif.args.playerId, notif.args.playerCoins);

            // var buildingNodeId = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0].attr('id');
            var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];
            dijit.Tooltip.hide(buildingNode);

            var anim = dojo.fadeOut( {node:buildingNode, duration: 400} );

            dojo.connect( anim, 'onEnd', dojo.hitch( this, function() {
                dojo.destroy(buildingNode);
                this.updateDraftpool(notif.args.draftpool);
                this.updateWondersSituation(notif.args.wondersSituation);
            }));

            anim.play();
        },

        notif_constructWonder: function(notif) {
            console.log( 'notif_constructWonder' );
            console.log( notif );

            this.updatePlayerCoins(notif.args.playerId, notif.args.playerCoins);
            this.scoreCtrl[notif.args.playerId].setValue( notif.args.playerScore );

            // var buildingNodeId = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0].attr('id');
            var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];
            dijit.Tooltip.hide(buildingNode);

            this.updateWondersSituation(notif.args.wondersSituation);

            this.clearActionGlow();

            // Animate age card towards wonder:
            if (1) {
                var wonderContainer = dojo.query('#player_wonders_' + notif.args.playerId + ' #wonder_' + notif.args.wonderId + '_container')[0];
                var ageCardContainer = dojo.query('.age_card_container', wonderContainer)[0];
                var ageCardNode = dojo.query('.building_small', ageCardContainer)[0];
                var wonder = dojo.query('.wonder_small', wonderContainer)[0];

                // Move age card to start position and set starting properties.
                this.placeOnObjectPos(ageCardNode, buildingNode, 0, 0);
                dojo.style(ageCardNode, 'z-index', 15);
                dojo.style(ageCardNode, 'transform', 'rotate(0deg) perspective(40em) rotateY(-90deg)');

                var animDuration = 5000;
                var anim = dojo.fx.chain( [
                    dojo.animateProperty({
                        node: buildingNode,
                        duration: animDuration / 6,
                        properties: {
                            propertyTransform: { start: 0, end: 90 }
                        },
                        onAnimate: function(values) {
                            // fired for every step of the animation, passing a value from a dojo._Line for this animation
                            // dojo.style(ageCardNode, 'z-index', parseInt(values.propertyZIndex.replace("px", "")));
                            dojo.style(buildingNode, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                        }
                    }),
                    dojo.animateProperty({
                        node: ageCardNode,
                        duration: animDuration / 6,
                        properties: {
                            propertyTransform: { start: -90, end: 0 }
                        },
                        onAnimate: function(values) {
                            // fired for every step of the animation, passing a value from a dojo._Line for this animation
                            // dojo.style(ageCardNode, 'z-index', parseInt(values.propertyZIndex.replace("px", "")));
                            dojo.style(ageCardNode, 'transform', 'perspective(40em) rotateY(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                        }
                    }),
                    dojo.fx.combine( [
                        dojo.animateProperty({
                            node: ageCardNode,
                            delay: animDuration / 3,
                            duration: animDuration / 3,
                            properties: {
                                propertyTransform: { start: 0, end: -90 }
                            },
                            onAnimate: function(values) {
                                // fired for every step of the animation, passing a value from a dojo._Line for this animation
                                // dojo.style(ageCardNode, 'z-index', parseInt(values.propertyZIndex.replace("px", "")));
                                dojo.style(ageCardNode, 'transform', 'rotate(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                            }
                        }),
                        this.slideToObjectPos(ageCardNode, ageCardContainer, 0, 0, animDuration),
                    ] ),
                ] );

                dojo.connect( anim, 'beforeBegin', dojo.hitch( this, function() {
                    dojo.style(wonder, 'z-index', 20);
                }));
                dojo.connect( anim, 'onEnd', dojo.hitch( this, function() {
                    dojo.destroy(buildingNode);
                    dojo.style(ageCardNode, 'z-index', 1);
                    dojo.style(wonder, 'z-index', 2);
                    this.updateDraftpool(notif.args.draftpool);
                }));

                anim.play();
            }
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
