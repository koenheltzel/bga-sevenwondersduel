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
    "dojo/_base/declare",
    "dojo/query",
    "dojo/on",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, domAttr, domStyle, declare, on) {
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
            console.log( "Starting game setup" );
            console.log( "gamedatas", gamedatas );

            this.gamedatas = gamedatas;

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
            }
            
            // TODO: Set up your game interface here, according to "gamedatas"
            console.log('laat', dojo.query('#wonder_selection_container'));
            this.updateWonderSelection(this.gamedatas.wonderSelection);
            on(dojo.query('#wonder_selection_container'), ".wonder:click", dojo.hitch(this, "onWonderSelectionClick"));

            this.updateDraftpool(this.gamedatas.draftpool);
            this.updateProgressTokensBoard(this.gamedatas.progressTokensBoard);

            // Dummy divide cards over both players
            var playerFlag = 0;
            Object.keys(this.gamedatas.buildings).forEach(dojo.hitch(this, function(id) {
                var building = this.gamedatas.buildings[id];
                var playerId = gamedatas.playerIds[playerFlag % 2];
                var spriteId = null;
                var data = {
                    jsData: 'data-building-id=' + id + '',
                    jsId: id
                };
                var spritesheetColumns = 10;
                data.jsX = (id - 1) % spritesheetColumns;
                data.jsY = Math.floor((id - 1) / spritesheetColumns);

                if (id <= 73){
                    dojo.place(this.format_block('jstpl_player_building', data), dojo.query('#player_area_content_' + playerId + ' .' + building.type)[0]);
                }
                playerFlag++;
            }));

            // Dummy divide wonders over both players
            var playerFlag = 0;
            var containerNumber = 0;
            Object.keys(this.gamedatas.wonders).forEach(dojo.hitch(this, function(id) {
                var wonder = this.gamedatas.wonders[id];
                var playerId = gamedatas.playerIds[playerFlag % 2];
                var spriteId = null;
                var data = {
                    jsData: 'data-wonder-id=' + id + '',
                    jsId: id
                };
                var spritesheetColumns = 5;
                data.jsX = (id - 1) % spritesheetColumns;
                data.jsY = Math.floor((id - 1) / spritesheetColumns);

                if (id <= 8){
                    var wonderContainerNode = dojo.place(this.format_block('jstpl_player_wonder', data), dojo.query('#player_area_content_' + playerId + ' .player_area_wonder_container' + Math.floor(containerNumber))[0]);
                    if(Math.random() > 0.3) {
                        var randomAge = Math.floor(Math.random() * (3 - 1 + 1)) + 1;
                        id = 73 + randomAge;
                        var data = {
                            jsData: '',
                            jsId: id
                        };
                        var spritesheetColumns = 10;
                        data.jsX = (id - 1) % spritesheetColumns;
                        data.jsY = Math.floor((id - 1) / spritesheetColumns);
                        dojo.place(this.format_block('jstpl_player_wonder_age_card', data), dojo.query('.age_card_container', wonderContainerNode)[0]);
                    }
                }
                playerFlag++;
                containerNumber += 0.5;
            }));

            // Dummy divide progress tokens over both players
            var playerFlag = 0;
            var containerNumber = 0;
            Object.keys(this.gamedatas.progressTokens).forEach(dojo.hitch(this, function(id) {
                // var wonder = this.gamedatas.progressTokens[id];
                var playerId = gamedatas.playerIds[playerFlag % 2];
                var data = {
                    jsData: 'data-progress-token-id=' + id + '',
                    jsId: id
                };
                var spritesheetColumns = 4;
                data.jsX = (id - 1) % spritesheetColumns;
                data.jsY = Math.floor((id - 1) / spritesheetColumns);
                if (id <= 10 && Math.random() > 0.3) {
                    dojo.place(this.format_block('jstpl_player_progress_token', data), dojo.query('#player_area_content_' + playerId + ' .player_area_progress_tokens')[0]);
                }
                playerFlag++;
                containerNumber += 0.5;
            }));

            // Adjust the height of the age divs based on the age cards absolutely positioned within.
            dojo.query(".age").forEach(function(node){
                var maxY = 0;
                var height = 0;
                dojo.query(".building",node).forEach(function(building){
                    var y = domStyle.get(building, "top");
                    if (y > maxY) {
                        maxY = y;
                        height = domStyle.get(building, "height");
                    }
                });
                domStyle.set(node, "height", (maxY + height + 15) + "px");
            });

            // Add tooltips to buildings (draftpool, player boards).
            dojo.query( '.building_small, .building_header_small' ).forEach( dojo.hitch( this, function( node ) {
                var id = domAttr.get(node, "data-building-id");
                var html = this.getBuildingTooltip( id );
                if (html) {
                    // We could use addTooltipHtml here, but the downside is the tooltip will often point from an
                    // invisible part of the card where another card overlaps it. The top and bottom of the draftpool
                    // cards are always visible, so the pointer will point correctly.
                    // So instead we manually instantiate a dijit tooltip and also take advantage of the getContent
                    // functionality so the tooltip's html gets generated when it is needed.
                    new dijit.Tooltip({
                        connectId: [node.id],
                        getContent: dojo.hitch( this, function(node) {
                            return this.getTooltipHtml(node);
                        } ),
                        showDelay: this.toolTipDelay
                    });
                    // Default tooltip implementation:
                    // this.addTooltipHtml( node.id, this.getTooltipHtml(node), this.toolTipDelay );
                }
            } ) );

            // Add tooltips to wonders (player boards)
            dojo.query( '.wonder_small' ).forEach( dojo.hitch( this, function( node ) {
                // console.log(node);
                var id = domAttr.get(node, "data-wonder-id");
                var html = this.getWonderTooltip( id );
                if (html) {
                    this.addTooltipHtml( node.id, html, this.toolTipDelay );
                }
            } ) );

            dojo.query( '.progress_token_small' ).forEach( dojo.hitch( this, function( node ) {
                // console.log(node);
                var id = domAttr.get(node, "data-progress-token-id");
                var html = this.getProgressTokenTooltip( id );
                if (html) {
                    this.addTooltipHtml( node.id, html, this.toolTipDelay );
                }
            } ) );
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );

            // Debug tooltip content by placing a tooltip at the top of the screen.
            //dojo.place( this.getBuildingTooltip( 22 ), 'swd_wrap', 'first' );
        },

        getTooltipHtml: function (node) {
            var id = domAttr.get(node, "data-building-id");
            return this.getBuildingTooltip(id);
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

        updateWonderSelection: function (wonderSelection) {
            for (var i = 0; i < wonderSelection.length; i++) {
                var id = wonderSelection[i];
                var data = {
                    jsData: 'data-wonder-id=' + id + '',
                    jsId: id
                };
                var spritesheetColumns = 5;
                data.jsX = (id - 1) % spritesheetColumns;
                data.jsY = Math.floor((id - 1) / spritesheetColumns);

                var wonderNode = dojo.place(this.format_block('jstpl_wonder_selection', data), dojo.query('#wonder_selection_container')[0]);
                var node = dojo.query('#wonder_selection_' + id)[0]
                console.log('node', node);
                dojo.connect(node, 'onclick', this, this.onWonderSelectionClick);
            }

        },

        updateDraftpool: function (draftpool) {
            console.log('updateDraftpool: ', draftpool);
            this.draftpool = draftpool;
            dojo.empty("draftpool");

            for (var i = 0; i < draftpool.length; i++) {
                var position = draftpool[i];
                var spriteId = null;
                var cost = Math.floor(Math.random() * 5);
                var data = {
                    jsData: '',
                    jsRow: position.row,
                    jsColumn: position.column,
                    jsZindex: position.row * 10,
                    jsDisplayCost: position.row == 5 && cost > 0 ? 'inline-block' : 'none',
                    jsCost: cost
                };
                if (typeof position.building != 'undefined') {
                    spriteId = position.building;
                    data.jsData = 'data-building-id="' + position.building + '"';
                } else {
                    spriteId = position.back;
                }
                var spritesheetColumns = 10;
                data.jsX = (spriteId - 1) % spritesheetColumns;
                data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);

                dojo.place(this.format_block('jstpl_draftpool_building', data), 'draftpool');
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
            console.log('data-wonder-id ', wonder.attr('data-wonder-id').pop());

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if (!this.checkAction('wonderSelected')) {
                return;
            }

            this.ajaxcall("/sevenwondersduel/sevenwondersduel/wonderSelected.html", {
                wonderId: wonder.attr('data-wonder-id')
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
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
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
