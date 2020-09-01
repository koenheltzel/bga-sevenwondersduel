/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * MilitaryTrackAnimator.js
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
        var classDefinition = declare("bgagame.MilitaryTrackAnimator", null, {

            game: null,

            pawnStepDuration: 400,
            militaryTokenAnimationDuration: 1600, // Excluding the coin animation

            /**
             * Get singleton instance
             * @returns {bgagame.MilitaryTrackAnimator}
             */
            get: function() {
                if (typeof bgagame.MilitaryTrackAnimator.prototype.instance == "undefined") {
                    bgagame.MilitaryTrackAnimator.prototype.instance = new bgagame.MilitaryTrackAnimator();
                }
                return bgagame.MilitaryTrackAnimator.prototype.instance;
            },

            constructor: function () {
                this.game = bgagame.sevenwondersduelagora.instance;
            },

            delta: function(num1, num2) {
                var absolute = Math.abs(num1 - num2);
                return num2 > num1 ? absolute : -absolute;
            },

            getAnimation: function (active_player_id, payment) {
                // The military token animation always concerns the opponent of the active player.
                var opponent_id = this.game.getOppositePlayerId(active_player_id);

                if (payment.militarySteps > 0) {
                    var anims = [];

                    // Conflict Pawn stepping animation. We do this step for step with easing so each step is clear.
                    var startPosition = parseInt(this.game.getCssVariable('--conflict-pawn-position'));
                    var endPosition = parseInt(this.game.invertMilitaryTrack() ? -payment.militaryNewPosition : payment.militaryNewPosition);
                    var delta = this.delta(startPosition, endPosition);
                    var stepSize = delta / payment.militarySteps;
                    var stepAnims = [];
                    for(var i = 0; i < payment.militarySteps; i++) {
                        var stepEndPosition = startPosition + stepSize;
                        stepAnims.push(dojo.animateProperty({
                            node: $('conflict_pawn'),
                            duration: this.pawnStepDuration,
                            properties: {
                                propertyConflictPawnPosition: {
                                    start: startPosition,
                                    end: (i < payment.militarySteps -1) ? stepEndPosition : endPosition
                                }
                            },
                            onAnimate: dojo.hitch(this, function (values) {
                                this.game.setCssVariable('--conflict-pawn-position', parseFloat(values.propertyConflictPawnPosition.replace("px", "")));
                            }),
                        }));
                        startPosition = stepEndPosition;
                    }
                    anims.push(dojo.fx.chain(stepAnims));

                    if (payment.militaryTokenNumber > 0) {
                        var tokenNumber = this.game.invertMilitaryTrack() ? (5 - payment.militaryTokenNumber) : payment.militaryTokenNumber;
                        var tokenNode = dojo.query('#military_tokens>div:nth-of-type(' + tokenNumber + ')>.military_token')[0];
                        var opponentCoinsContainer = this.game.getPlayerCoinContainer(opponent_id);
                        var yOffset = 2 * 7.5 * this.game.getCssVariable('--scale');
                        var xOffset = (opponentCoinsContainer.offsetWidth - tokenNode.offsetWidth) / 2;
                        if (opponent_id == this.game.me_id) {
                            yOffset = -tokenNode.offsetHeight - yOffset;
                        }
                        else {
                            yOffset = opponentCoinsContainer.offsetHeight + yOffset;
                        }

                        anims.push(dojo.fx.chain([
                            // Move military token close to opponent coins total.
                            this.game.slideToObjectPos(tokenNode, opponentCoinsContainer, xOffset, yOffset, this.militaryTokenAnimationDuration * 0.6),
                            // Animate coins to fly from opponent coins total to military token.
                            bgagame.CoinAnimator.get().getAnimation(
                                opponentCoinsContainer,
                                opponentCoinsContainer,
                                payment.militaryOpponentPays, // This could differ from the token value if the opponent can't afford what's on the token.
                                opponent_id,
                                [0, 0],
                                [0, yOffset + ((tokenNode.offsetHeight - opponentCoinsContainer.offsetHeight) / 2)]
                            ),
                            // Fade out military token.
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
        });

        // Assign static properties / functions (these functions shouldn't make use of "this"):
        classDefinition.get = classDefinition.prototype.get;

        return classDefinition;
    });
