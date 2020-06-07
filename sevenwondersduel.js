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
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, domAttr, domStyle, declare) {
    return declare("bgagame.sevenwondersduel", ebg.core.gamegui, {
        constructor: function(){
            console.log('sevenwondersduel constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

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
            this.updateDraftpool(this.gamedatas.draftpool)

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
                domStyle.set(node, "height", (maxY + height + 5) + "px");
            });

            dojo.query( '.building_small' ).forEach( dojo.hitch( this, function( node ) {
                var id = domAttr.get(node, "data-building-id");
                var html = this.getBuildingTooltip( id );
                if (html) {
                    this.addTooltipHtml( node.id, html, 0 );
                }
            } ) );

            dojo.query( '.wonder_small' ).forEach( dojo.hitch( this, function( node ) {
                // console.log(node);
                var id = domAttr.get(node, "data-wonder-id");
                var html = this.getWonderTooltip( id );
                if (html) {
                    this.addTooltipHtml( node.id, html, 0 );
                }
            } ) );

            dojo.query( '.progress_token_small' ).forEach( dojo.hitch( this, function( node ) {
                // console.log(node);
                var id = domAttr.get(node, "data-progress-token-id");
                var html = this.getProgressTokenTooltip( id );
                if (html) {
                    this.addTooltipHtml( node.id, html, 0 );
                }
            } ) );
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );

            // Debug tooltip content by placing a tooltip at the top of the screen.
            //dojo.place( this.getBuildingTooltip( 22 ), 'swd_wrap', 'first' );
        },

        updateDraftpool: function (draftpool) {
            console.log('updateDraftpool: ', draftpool);
            this.draftpool = draftpool;
            dojo.empty("draftpool");

            for (var i = 0; i < draftpool.length; i++) {
                var position = draftpool[i];
                var spriteId = null;
                var data = {
                    jsData: '',
                    jsRow: position.row,
                    jsColumn: position.column,
                    jsZindex: position.row * 10
                }
                if (typeof position.building != 'undefined') {
                    spriteId = position.building;
                    data.jsData = 'data-building-id="' + position.building + '"';
                }
                else {
                    spriteId = position.back;
                }
                var spritesheetColumns = 10;
                data.jsX = (spriteId - 1) % spritesheetColumns;
                data.jsY = Math.floor((spriteId - 1) / spritesheetColumns);

                dojo.place(this.format_block('jstpl_draftpool_building', data), 'draftpool');
            }
            // dojo.style('draftpool-container', 'display', draftpool.length > 0 ? 'inline-block' : 'none');
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
