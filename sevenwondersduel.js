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
    "dojo/dom-attr",
    "dojo/dom-style",
    "dojo/dom-geometry",
    "dojo/_base/declare",
    "dojo/query",
    "dojo/on",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, domAttr, domStyle, domGeom, declare, on) {
    return declare("bgagame.sevenwondersduel", ebg.core.gamegui, {
        constructor: function(){
            // Tooltip settings
            this.toolTipDelay = 100;
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
            dojo.query('#draftpool').on(".building.available:click", dojo.hitch(this, "onDraftpoolBuildingClick"));

            // Resize handler
            window.addEventListener('resize', dojo.hitch(this, "onWindowResize"));

            // Tool tips using event delegation:
            this.setupTooltips();

            this.updateWonderSelection(this.gamedatas.wondersSituation.selection);
            this.updateDraftpool(this.gamedatas.draftpool);
            this.updateProgressTokensBoard(this.gamedatas.progressTokensBoard);

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
                    var id = domAttr.get(node, "data-building-id");
                    return this.getBuildingTooltip(id);
                })
            });

            // Add tooltips to wonders everywhere.
            new dijit.Tooltip({
                connectId: "game_play_area",
                selector: '.wonder_small',
                showDelay: this.toolTipDelay,
                getContent: dojo.hitch( this, function(node) {
                    var id = domAttr.get(node, "data-wonder-id");
                    return this.getWonderTooltip(id);
                })
            });

            // Add tooltips to progress tokens everywhere.
            new dijit.Tooltip({
                connectId: "game_play_area",
                selector: '.progress_token_small',
                showDelay: this.toolTipDelay,
                getContent: dojo.hitch( this, function(node) {
                    var id = domAttr.get(node, "data-progress-token-id");
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
                var container = dojo.query('#board_progress_token_container' + i)[0];
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
            var node = dojo.query('#player_area_' + playerId + '_coins')[0];
            node.innerHTML = coins;
        },

        getBuildingDiv: function (id, cardId) {
            var data = {
                jsCardId: cardId,
                jsId: id,
            };
            var spritesheetColumns = 10;
            data.jsX = (id - 1) % spritesheetColumns;
            data.jsY = Math.floor((id - 1) / spritesheetColumns);

            return this.format_block('jstpl_player_building', data);
        },

        getWonderDiv: function (card) {
            var id = card.type_arg;
            var data = {
                jsCardId: card.id,
                jsId: id,
            };
            var spritesheetColumns = 5;
            data.jsX = (id - 1) % spritesheetColumns;
            data.jsY = Math.floor((id - 1) / spritesheetColumns);

            return this.format_block('jstpl_wonder', data);
        },

        updatePlayerWonders: function (playerId, cards) {
            if (cards.constructor == Object) {
                var i = 1;
                Object.keys(cards).forEach(dojo.hitch(this, function(cardId) {
                    var container = dojo.query('#player_area_content_wonder_position_' + i + '_' + playerId)[0];
                    dojo.place(this.getWonderDiv(cards[cardId]), container);
                    i++;
                }));
            }
        },

        updatePlayerBuildings: function (playerId, cards) {
            if (cards.constructor == Object) {
                var i = 1;
                Object.keys(cards).forEach(dojo.hitch(this, function(cardId) {
                    var building = this.gamedatas.buildings[cards[cardId].type_arg];
                    var container = dojo.query('#player_area_content_' + playerId + ' .' + building.type)[0];
                    dojo.place(this.getBuildingDiv(cards[cardId].type_arg, cardId), container);
                    i++;
                }));
            }
        },

        updateWonderSelection: function (cards) {
            var block = dojo.query('#wonder_selection_block')[0];
            if (cards.constructor == Object) {
                var position = 1;
                Object.keys(cards).forEach(dojo.hitch(this, function(cardId) {
                    var container = dojo.query('#wonder_selection_position_' + cards[cardId]['location_arg'])[0];
                    dojo.empty(container);
                    dojo.place(this.getWonderDiv(cards[cardId]), container);
                    position++;
                }));

                dojo.style(block, "display", "block");
            }
            else {
                dojo.style(block, "display", "none");
            }
        },

        updateDraftpool: function (draftpool) {
            console.log('updateDraftpool: ', draftpool);

            dojo.style('draftpool_container', 'display', draftpool.age > 0 ? 'block' : 'none');
            if (draftpool.age > 0) {
                dojo.empty("draftpool");

                for (var i = 0; i < draftpool.cards.length; i++) {
                    var position = draftpool.cards[i];
                    var spriteId = null;
                    var data = {
                        jsId: '',
                        jsCardId: '',
                        jsRow: position.row,
                        jsColumn: position.column,
                        jsZindex: position.row * 10,
                        jsAvailable: position.available ? 'available' : '',
                        jsDisplayCost: 'none',
                        jsCost: -1,
                    };
                    if (typeof position.building != 'undefined') {
                        spriteId = position.building;
                        data.jsId = position.building;
                        data.jsCardId = position.card;
                        data.jsDisplayCost = position.cost[this.player_id] > 0 ? 'inline-block' : 'none',
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
                domStyle.set(dojo.query('.draftpool')[0], "height", "calc(var(--building-height) * var(--building-small-scale) + " + (rows - 1) + " * var(--draftpool-row-height))");
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
        },

        onDraftpoolBuildingClick: function (e) {
            console.log('onDraftpoolBuildingClick');
            // Preventing default browser reaction
            dojo.stopEvent(e);

            var building = dojo.query(e.target);
            console.log('building ', building);
            console.log('data-card-id ', building.attr('data-card-id').pop());

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if (!this.checkAction('actionConstructBuilding')) {
                return;
            }

            this.ajaxcall("/sevenwondersduel/sevenwondersduel/actionConstructBuilding.html", {
                    cardId: building.attr('data-card-id')
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

                }
            );
        },

        getOffset: function(el) {
            var rect = el.getBoundingClientRect();
            return {
                left: rect.left + window.scrollX,
                top: rect.top + window.scrollY
            };
        },

        onWindowResize: function (e) {
            console.log('onWindowResize', e);
            var titlePosition = domGeom.position(dojo.query('#page-title')[0], false);
            var titleMarginBottom = 5;
            var width = titlePosition.w;
            var height = window.innerHeight - titlePosition.y - titlePosition.h - titleMarginBottom;

            console.log('titlePosition: ', titlePosition);
            console.log('available play area: ', width, height);
            var ratio = width / height;

            // Measured in 75% view, without any player buildings (meaning the height can become heigher:
            var portrait = 747 / 987; // 0.76
            var square = 947 / 897; // 1.056
            var landscape = 1131/ 756; // 1.50

            var swdNode = dojo.query('#swd_wrap')[0];
            if(ratio >= landscape){
                console.log('ratio: ', ratio, 'choosing landscape');
                domAttr.set(swdNode, 'data-wonder-columns', 2);
                dojo.style(swdNode, "zoom", height / dojo.style(dojo.query('#swd_wrap')[0], 'height'));
            }
            else if(ratio < landscape && ratio > portrait){
                Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function(playerId) {
                    dojo.place('player_wonders_' + playerId, 'player_wonders_container_' + playerId);
                }));

                console.log('ratio: ', ratio, 'choosing square');
                domAttr.set(swdNode, 'data-wonder-columns', 1);
            }
            else { // ratio <= portrait
                Object.keys(this.gamedatas.players).forEach(dojo.hitch(this, function(playerId) {
                    dojo.place('player_wonders_' + playerId, 'player_wonders_mobile_container_' + playerId);
                }));

                console.log('ratio: ', ratio, 'choosing portrait');
                domAttr.set(swdNode, 'data-wonder-columns', 4);
                dojo.style(swdNode, "zoom", width / dojo.style(dojo.query('#layout_flexbox')[0], 'width'));
            }
            console.log('swd_wrap height: ', dojo.query('#swd_wrap')[0], 'height');
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
        },
        
        // TODO: from this point and below, you can write your game notifications handling methods

        notif_wonderSelected: function( notif )
        {
            console.log( 'notif_wonderSelected' );
            console.log( notif );
            var wonderNode = dojo.query('#wonder_' + notif.args.wonderId)[0];
            dijit.Tooltip.hide(wonderNode);

            var wonderNodeId = 'wonder_' + notif.args.wonderId;
            var targetNodeId = 'player_area_content_wonder_position_' + notif.args.playerWonderCount + '_' + notif.args.playerId;
            this.attachToNewParent(wonderNodeId, targetNodeId);
            this.slideToObjectPos(wonderNodeId, targetNodeId, 0, 0).play();

            if (notif.args.updateWonderSelection) {
                this.updateWonderSelection(notif.args.wonderSelection);
            }
        },

        notif_nextAge: function(notif) {
            this.updateDraftpool(notif.args.draftpool);
        },

        notif_constructBuilding: function(notif) {
            console.log( 'notif_constructBuilding' );
            console.log( notif );

            this.updatePlayerCoins(notif.args.playerId, notif.args.playerCoins);

            // var buildingNodeId = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0].attr('id');
            var buildingNode = dojo.query("[data-building-id=" + notif.args.buildingId + "]")[0];
            dijit.Tooltip.hide(buildingNode);

            var building = this.gamedatas.buildings[notif.args.buildingId];
            var container = dojo.query('#player_area_content_' + notif.args.playerId + ' .' + building.type)[0];
            dojo.place(this.getBuildingDiv(notif.args.buildingId, dojo.attr(buildingNode, "data-card-id")), container);
            this.fadeOutAndDestroy(buildingNode);

            this.updateDraftpool(notif.args.draftpool);
        }

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
