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

            getAnimation: function (sourceNode, targetNode, amount, playerId, sourcePosition, targetPosition) {
                // Optional source/target positions
                if (typeof sourcePosition == "undefined") sourcePosition = [0, 0];
                if (typeof targetPosition == "undefined") targetPosition = [0, 0];

                // Auto detect if coins are moving to or from certain players player areas. We use this to update their coin total during the animation.
                var sourceNodePlayerId = undefined;
                var targetNodePlayerId = undefined;
                if (dojo.hasClass(sourceNode, 'player_area_coins') && dojo.query(sourceNode).closest(".me")[0]) sourceNodePlayerId = this.game.me_id;
                if (dojo.hasClass(sourceNode, 'player_area_coins') && dojo.query(sourceNode).closest(".opponent")[0]) sourceNodePlayerId = this.game.opponent_id;
                if (dojo.hasClass(targetNode, 'player_area_coins') && dojo.query(targetNode).closest(".me")[0]) targetNodePlayerId = this.game.me_id;
                if (dojo.hasClass(targetNode, 'player_area_coins') && dojo.query(targetNode).closest(".opponent")[0]) targetNodePlayerId = this.game.opponent_id;

                var anims = [];
                if (amount != 0) {
                    var html = this.game.format_block('jstpl_coin_animated');
                    for (var i = 0; i < Math.abs(amount); i++) {
                        var node = dojo.place(html, 'swd_wrap');
                        if (playerId == this.game.opponent_id) {
                            dojo.addClass(node, 'opponent');
                        }
                        this.game.placeOnObjectPos(node, sourceNode, sourcePosition[0], sourcePosition[1]);

                        dojo.style(node, 'opacity', 0);
                        var fadeDurationPercentage = 0.15;
                        var anim = dojo.fx.combine([
                            dojo.fadeIn({
                                node: node,
                                duration: this.coin_slide_duration * fadeDurationPercentage,
                                delay: i * this.coin_slide_delay,
                                onPlay: dojo.hitch(this, function (node) {
                                    if (sourceNodePlayerId && sourcePosition[0] == 0 && sourcePosition[1] == 0) {
                                        this.game.increasePlayerCoins(sourceNodePlayerId, -1);
                                    }
                                }),
                            }),
                            this.game.slideToObjectPos(node, targetNode, targetPosition[0], targetPosition[1], this.coin_slide_duration, i * this.coin_slide_delay),
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
                                    if (targetNodePlayerId && targetPosition[0] == 0 && targetPosition[1] == 0) {
                                        this.game.increasePlayerCoins(targetNodePlayerId, 1);
                                    }
                                }),
                            }),
                        ]);
                        anims.push(anim);
                    }
                }
                return dojo.fx.combine(anims);
            },

            precalculateDuration: function (amount) {
                this.game = bgagame.sevenwondersduel.instance;
                if (amount != 0) {
                    return this.coin_slide_duration + ((Math.abs(amount) - 1) * this.coin_slide_delay);
                }
                return 0;
            },
        });

        // Assign static properties / functions (these functions shouldn't make use of "this"):
        classDefinition.get = classDefinition.prototype.get;

        return classDefinition;
    });