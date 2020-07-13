/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * MilitaryTokenAnimator.js
 *
 * Class to generate military token animations.
 *
 */

define([
        "dojo",
        "dojo/_base/declare",
        "dojo/query",
        "dojo/NodeList-traverse",
    ],
    function (dojo, declare) {
        var classDefinition = declare("bgagame.MilitaryTokenAnimator", null, {

            game: null,

            pawnStepDuration: 500,
            militaryTokenAnimationDuration: 1600, // Excluding the coin animation

            /**
             * Get singleton instance
             * @returns {bgagame.MilitaryTokenAnimator}
             */
            get: function() {
                if (typeof bgagame.MilitaryTokenAnimator.prototype.instance == "undefined") {
                    bgagame.MilitaryTokenAnimator.prototype.instance = new bgagame.MilitaryTokenAnimator();
                }
                return bgagame.MilitaryTokenAnimator.prototype.instance;
            },

            constructor: function () {
                this.game = bgagame.sevenwondersduel.instance;
            },

            getAnimation: function (active_player_id, militaryTrack, payment) {
                console.log('getMilitaryTokenAnimation', payment);
                // The military token animation always concerns the opponent of the active player.
                player_id = this.game.getOppositePlayerId(active_player_id);

                var oldPosition = this.game.gamedatas.militaryTrack.conflictPawn;
                var steps = Math.abs(oldPosition - militaryTrack.conflictPawn);

                if (oldPosition != militaryTrack.conflictPawn) {
                    var anims = [];
                    anims.push(dojo.animateProperty({
                        node: $('conflict_pawn'),
                        duration: this.pawnStepDuration * steps,
                        easing: dojo.fx.easing.linear,
                        properties: {
                            propertyConflictPawnPosition: {
                                start: this.game.invertMilitaryTrack() ? -oldPosition : oldPosition,
                                end: this.game.invertMilitaryTrack() ? -militaryTrack.conflictPawn : militaryTrack.conflictPawn
                            }
                        },
                        onAnimate: dojo.hitch(this, function (values) {
                            this.game.setCssVariable('--conflict-pawn-position', parseFloat(values.propertyConflictPawnPosition.replace("px", "")));
                        }),
                    }));

                    if (payment.militaryTokenNumber > 0) {
                        var offset = 100 * this.game.getCssVariable('--scale');
                        var inverter = player_id == this.game.me_id ? -1 : 1;
                        var playerAlias = player_id == this.game.me_id ? 'me' : 'opponent';
                        var tokenNumber = this.game.invertMilitaryTrack() ? (5 - payment.militaryTokenNumber) : payment.militaryTokenNumber;
                        var tokenNode = dojo.query('#military_tokens>div:nth-of-type(' + tokenNumber + ')>.military_token')[0];
                        var playerCoinsNode = dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0];
                        var tokenCoins = payment.militaryOpponentPays;
                        var coinAnimation = bgagame.CoinAnimator.get().getAnimation(
                            playerCoinsNode,
                            playerCoinsNode,
                            payment.militaryOpponentPays, // This could differ from the token value if the opponent can't afford what's on the token.
                            player_id,
                            [0, 0],
                            [0, offset * inverter]
                        );

                        anims.push(dojo.fx.chain([
                            this.game.slideToObjectPos(tokenNode, playerCoinsNode, 0, offset * inverter, this.militaryTokenAnimationDuration * 0.6),
                            coinAnimation,
                            dojo.fadeOut({
                                node: tokenNode,
                                duration: this.militaryTokenAnimationDuration * 0.4,
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                })
                            }),
                        ]));
                    }
                    return dojo.fx.chain(anims);
                }
                return dojo.fx.combine([]);
            },
            precalculateDuration: function (steps, tokenAmount) {
                //TODO Duration isn't calculated/used for dynamically setting notification delay (main question is, where to get "amount" from?).
                if (steps > 0) {
                    return this.militaryTokenAnimationDuration + steps * this.pawn_step_duration + bgagame.CoinAnimator.get().precalculateDuration(tokenAmount);
                }
                return 0;
            },
        });

        // Assign static properties / functions (these functions shouldn't make use of "this"):
        classDefinition.get = classDefinition.prototype.get;

        return classDefinition;
    });
