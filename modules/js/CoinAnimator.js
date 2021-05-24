/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * CoinAnimator.js
 *
 * Class to generate coin animations (certain amount of coins flying from and to nodes, auto updating player score counters).
 *
 */

define([
        "dojo",
        "dojo/_base/declare",
        "dojo/query",
        "dojo/NodeList-traverse",
    ],
    function (dojo, declare) {
        var classDefinition = declare("bgagame.CoinAnimator", null, {

            game: null,

            coin_slide_duration: 500,
            coin_slide_delay: 100,

            /**
             * Get singleton instance
             * @returns {bgagame.CoinAnimator}
             */
            get: function() {
                if (typeof bgagame.CoinAnimator.prototype.instance == "undefined") {
                    bgagame.CoinAnimator.prototype.instance = new bgagame.CoinAnimator();
                }
                return bgagame.CoinAnimator.prototype.instance;
            },

            constructor: function () {
                this.game = bgagame.sevenwondersduel.instance;
            },

            /**
             * @param sourceNode
             * @param targetNode
             * @param amount
             * @param playerId
             * @param sourcePosition Calculated from the middle of the source node to the middle of the the moving object.
             * @param targetPosition Calculated from the top left of the target node to the top left of the moving object!
             * @returns {*}
             */
            getAnimation: function (sourceNode, targetNode, amount, playerId, sourcePosition = [0, 0], targetPosition = [0, 0]) {
                var anims = [];
                if (sourceNode && targetNode) { // It can be convenient to pass a non-existing node here, with the expectance to get an empty animation in return.
                    // Auto detect if coins are moving to or from certain players player areas. We use this to update their coin total during the animation.
                    var sourceNodePlayerId = undefined;
                    var targetNodePlayerId = undefined;
                    if (dojo.hasClass(sourceNode, 'player_area_coins') && dojo.query(sourceNode).closest(".me")[0]) sourceNodePlayerId = this.game.me_id;
                    if (dojo.hasClass(sourceNode, 'player_area_coins') && dojo.query(sourceNode).closest(".opponent")[0]) sourceNodePlayerId = this.game.opponent_id;
                    if (dojo.hasClass(targetNode, 'player_area_coins') && dojo.query(targetNode).closest(".me")[0]) targetNodePlayerId = this.game.me_id;
                    if (dojo.hasClass(targetNode, 'player_area_coins') && dojo.query(targetNode).closest(".opponent")[0]) targetNodePlayerId = this.game.opponent_id;
                    let astarteCoinNode = dojo.query('#player_conspiracies_' + playerId + ' .divinity_small[data-divinity-id=4] .coin')[0];

                    if (amount != 0) {
                        var html = this.game.format_block('jstpl_coin_animated');
                        for (var i = 0; i < Math.abs(amount); i++) {
                            var node = dojo.place(html, 'swd_wrap');
                            if (playerId == this.game.opponent_id) {
                                dojo.addClass(node, 'opponent');
                            }

                            let astarteCoin = sourceNodePlayerId == playerId && (i + 1) > this.game.getPlayerCoins(playerId);
                            let astarteDelay = astarteCoin ? this.coin_slide_duration : 0;
                            this.game.placeOnObjectPos(node, astarteCoin ? astarteCoinNode : sourceNode, sourcePosition[0], sourcePosition[1]);

                            dojo.style(node, 'opacity', 0);
                            var fadeDurationPercentage = 0.15;
                            var anim = dojo.fx.combine([
                                dojo.fadeIn({
                                    node: node,
                                    duration: this.coin_slide_duration * fadeDurationPercentage,
                                    delay: astarteDelay + i * this.coin_slide_delay,
                                    onPlay: dojo.hitch(this, function () {
                                        if (sourceNodePlayerId && sourcePosition[0] == 0 && sourcePosition[1] == 0) {
                                            if(this.game.getPlayerNodeCoins(playerId) > 0) {
                                                this.game.increasePlayerCoins(sourceNodePlayerId, -1);
                                            }
                                            else{
                                                this.game.increaseAstarteCoins(sourceNodePlayerId, -1);
                                            }
                                        }
                                    }),
                                }),
                                this.game.slideToObjectPos(node, targetNode, targetPosition[0], targetPosition[1], this.coin_slide_duration, astarteDelay + i * this.coin_slide_delay),
                                dojo.animateProperty({ // Standard fadeOut started of at opacity 0 (?!?)
                                    node: node,
                                    duration: this.coin_slide_duration * fadeDurationPercentage,
                                    delay: astarteDelay + (i * this.coin_slide_delay) + ((1 - fadeDurationPercentage) * this.coin_slide_duration),
                                    easing: dojo.fx.easing.linear,
                                    properties: {
                                        opacity: {
                                            start: 1,
                                            end: 0
                                        }
                                    },
                                    onEnd: dojo.hitch(this, function (node) {
                                        dojo.destroy(node);
                                        if (targetNodePlayerId && targetPosition[0] == 0 && targetPosition[1] == 0) {
                                            this.game.increasePlayerCoins(targetNodePlayerId, 1);
                                        }
                                    }),
                                }),
                            ]);
                            anims.push(anim);
                        }
                    }
                }
                return dojo.fx.chain([
                    dojo.fx.combine(anims),
                    this.game.getDummyAnimation(100) // End with a dummy animation to make sure the onEnd of the last coin is also executed.
                ]);
            },
        });

        // Assign static properties / functions (these functions shouldn't make use of "this"):
        classDefinition.get = classDefinition.prototype.get;

        return classDefinition;
    });
