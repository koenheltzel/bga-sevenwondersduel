/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * MilitaryTokenAnimationBuilder.js
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
        var classDefinition = declare("bgagame.MilitaryTokenAnimationBuilder", null, {

            game: null,

            militaryTokenAnimationDuration: 1600, // Excluding the coin animation

            /**
             * Get singleton instance
             * @returns {bgagame.MilitaryTokenAnimationBuilder}
             */
            get: function() {
                if (typeof bgagame.MilitaryTokenAnimationBuilder.prototype.instance == "undefined") {
                    bgagame.MilitaryTokenAnimationBuilder.prototype.instance = new bgagame.MilitaryTokenAnimationBuilder();
                }
                return bgagame.MilitaryTokenAnimationBuilder.prototype.instance;
            },

            constructor: function () {
                this.game = bgagame.sevenwondersduel.instance;
            },

            getAnimation: function (active_player_id, payment) {
                console.log('getMilitaryTokenAnimation', payment);
                // The military token animation always concerns the opponent of the active player.
                player_id = this.game.getOppositePlayerId(active_player_id);

                if (payment.militaryTokenNumber > 0) {
                    var offset = 100 * this.game.getCssVariable('--scale');
                    var inverter = player_id == this.game.me_id ? -1 : 1;
                    var playerAlias = player_id == this.game.me_id ? 'me' : 'opponent';
                    var tokenNumber = this.game.invertMilitaryTrack() ? (5 - payment.militaryTokenNumber) : payment.militaryTokenNumber;
                    var tokenNode = dojo.query('#military_tokens>div:nth-of-type(' + tokenNumber + ')>.military_token')[0];
                    var playerCoinsNode = dojo.query('.player_info.' + playerAlias + ' .player_area_coins')[0];
                    var coinAnimation = bgagame.CoinAnimationBuilder.get().getAnimation(
                        playerCoinsNode,
                        playerCoinsNode,
                        -payment.militaryTokenValue,
                        player_id,
                        [0, 0],
                        [0, offset * inverter]
                    );

                    var anim = dojo.fx.chain([
                        this.game.slideToObjectPos(tokenNode, playerCoinsNode, 0, offset * inverter, this.militaryTokenAnimationDuration * 0.6),
                        coinAnimation,
                        dojo.fadeOut({
                            node: tokenNode,
                            duration: this.militaryTokenAnimationDuration * 0.4,
                            onEnd: dojo.hitch(this, function (node) {
                                dojo.destroy(node);
                            })
                        }),
                    ]);
                    return anim;
                } else {
                    return dojo.fx.combine([]);
                }
            },
            precalculateDuration: function (amount) {
                //TODO Duration isn't calculated/used for dynamically setting notification delay (main question is, where to get "amount" from?).
                if (amount != 0) {
                    return this.militaryTokenAnimationDuration + bgagame.CoinAnimationBuilder.get().precalculateDuration(amount);
                }
                return 0;
            },
        });

        // Assign static properties / functions (these functions shouldn't make use of "this"):
        classDefinition.get = classDefinition.prototype.get;

        return classDefinition;
    });
