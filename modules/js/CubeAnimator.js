/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * CubeAnimator.js
 *
 * Class to generate cube animations (certain amount of cubes flying from and to nodes, auto updating player score counters).
 *
 */

define([
        "dojo",
        "dojo/_base/declare",
        "dojo/query",
        "dojo/NodeList-traverse",
    ],
    function (dojo, declare) {
        var classDefinition = declare("bgagame.CubeAnimator", null, {

            game: null,

            cube_slide_duration: 500,
            cube_slide_delay: 100,

            /**
             * Get singleton instance
             * @returns {bgagame.CubeAnimator}
             */
            get: function() {
                if (typeof bgagame.CubeAnimator.prototype.instance == "undefined") {
                    bgagame.CubeAnimator.prototype.instance = new bgagame.CubeAnimator();
                }
                return bgagame.CubeAnimator.prototype.instance;
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
                    // Auto detect if cubes are moving to or from certain players player areas. We use this to update their cube total during the animation.
                    var sourceNodePlayerId = undefined;
                    var targetNodePlayerId = undefined;
                    if (dojo.hasClass(sourceNode, 'player_area_cubes') && dojo.query(sourceNode).closest(".me")[0]) sourceNodePlayerId = this.game.me_id;
                    if (dojo.hasClass(sourceNode, 'player_area_cubes') && dojo.query(sourceNode).closest(".opponent")[0]) sourceNodePlayerId = this.game.opponent_id;
                    if (dojo.hasClass(targetNode, 'player_area_cubes') && dojo.query(targetNode).closest(".me")[0]) targetNodePlayerId = this.game.me_id;
                    if (dojo.hasClass(targetNode, 'player_area_cubes') && dojo.query(targetNode).closest(".opponent")[0]) targetNodePlayerId = this.game.opponent_id;

                    console.log('sourceNodePlayerId', sourceNodePlayerId);
                    console.log('targetNodePlayerId', targetNodePlayerId);
                    console.log('sourcePosition', sourcePosition);
                    console.log('targetPosition', targetPosition);
                    if (amount != 0) {
                        var html = this.game.format_block('jstpl_cube_animated');
                        for (var i = 0; i < Math.abs(amount); i++) {
                            var node = dojo.place(html, 'swd_wrap');
                            if (playerId == this.game.opponent_id) {
                                dojo.addClass(node, 'agora_cube_opponent');
                            }
                            else {
                                dojo.addClass(node, 'agora_cube_me');
                            }
                            this.game.placeOnObjectPos(node, sourceNode, sourcePosition[0], sourcePosition[1]);

                            dojo.style(node, 'opacity', 0);
                            var fadeDurationPercentage = 0.15;
                            var anim = dojo.fx.combine([
                                dojo.fadeIn({
                                    node: node,
                                    duration: this.cube_slide_duration * fadeDurationPercentage,
                                    delay: i * this.cube_slide_delay,
                                    onPlay: dojo.hitch(this, function (node) {
                                        if (sourceNodePlayerId && sourcePosition[0] == 0 && sourcePosition[1] == 0) {
                                            this.game.increasePlayerCubes(sourceNodePlayerId, -1);
                                        }
                                        else {
                                            let span = dojo.query('span', sourceNode)[0];
                                            let count = parseInt(span.innerHTML) - 1;
                                            span.innerHTML = count;
                                            dojo.style(sourceNode, 'opacity', count > 0 ? '1' : '0');
                                        }
                                    }),
                                }),
                                this.game.slideToObjectPos(node, targetNode, targetPosition[0], targetPosition[1], this.cube_slide_duration, i * this.cube_slide_delay),
                                dojo.animateProperty({ // Standard fadeOut started of at opacity 0 (?!?)
                                    node: node,
                                    duration: this.cube_slide_duration * fadeDurationPercentage,
                                    delay: (i * this.cube_slide_delay) + ((1 - fadeDurationPercentage) * this.cube_slide_duration),
                                    easing: dojo.fx.easing.linear,
                                    properties: {
                                        opacity: {
                                            start: 1,
                                            // Don't fade out the cube if the target node has value 0 and is invisible until the onEnd listener.
                                            end: (targetNodePlayerId && targetPosition[0] == 0 && targetPosition[1] == 0) ? 0 : (this.getCubeValue(targetNode) > 0 ? 0 : 1)
                                        }
                                    },
                                    onEnd: dojo.hitch(this, function (node) {
                                        if (targetNodePlayerId && targetPosition[0] == 0 && targetPosition[1] == 0) {
                                            this.game.increasePlayerCubes(targetNodePlayerId, 1);
                                        }
                                        else {
                                            let span = dojo.query('span', targetNode)[0];
                                            let count = parseInt(span.innerHTML) + 1;
                                            span.innerHTML = count;
                                            dojo.style(targetNode, 'opacity', count > 0 ? '1' : '0');
                                        }
                                        dojo.destroy(node);
                                    }),
                                }),
                            ]);
                            anims.push(anim);
                        }
                    }
                }
                return dojo.fx.chain([
                    dojo.fx.combine(anims),
                    this.game.getDummyAnimation(250) // End with a dummy animation to make sure the onEnd of the last coin is also executed.
                ]);
            },

            getCubeValue: function (node) {
                let span = dojo.query('span', node)[0];
                return parseInt(span.innerHTML);
            },
        });

        // Assign static properties / functions (these functions shouldn't make use of "this"):
        classDefinition.get = classDefinition.prototype.get;

        return classDefinition;
    });
