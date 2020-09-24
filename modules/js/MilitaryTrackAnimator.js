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
                this.game = bgagame.sevenwondersduel.instance;
            },

            delta: function(num1, num2) {
                let absolute = Math.abs(num1 - num2);
                return num2 > num1 ? absolute : -absolute;
            },

            getAnimation: function (active_player_id, payment) {
                let anims = [];
                if (payment.militarySteps > 0) {
                    // The military token animation always concerns the opponent of the active player.
                    let opponent_id = this.game.getOppositePlayerId(active_player_id);

                    let startPosition = this.game.invertMilitaryTrack() ? -parseInt(payment.militaryOldPosition) : parseInt(payment.militaryOldPosition);
                    let endPosition = this.game.invertMilitaryTrack() ? -parseInt(payment.militaryNewPosition) : parseInt(payment.militaryNewPosition);
                    let delta = this.delta(startPosition, endPosition);
                    let stepSize = delta / payment.militarySteps;

                    let position = startPosition;
                    while(position != endPosition) {
                        // First animate a step
                        anims.push(dojo.animateProperty({
                            node: $('conflict_pawn'),
                            duration: this.pawnStepDuration,
                            properties: {
                                propertyConflictPawnPosition: {
                                    start: position,
                                    end: position + stepSize
                                }
                            },
                            onAnimate: dojo.hitch(this, function (values) {
                                this.game.setCssVariable('--conflict-pawn-position', parseFloat(values.propertyConflictPawnPosition.replace("px", "")));
                            }),
                        }));

                        position += stepSize;

                        let realPosition = this.game.invertMilitaryTrack()? -position : position;
                        // Then check if a token was encountered (before performing the next step).
                        if (payment.militaryTokens[realPosition]) {
                            let token = payment.militaryTokens[realPosition];
                            let tokenNumber = this.game.invertMilitaryTrack() ? (5 - token.number) : token.number;
                            let tokenNode = dojo.query('#military_tokens>div:nth-of-type(' + tokenNumber + ')>.military_token')[0];
                            let opponentCoinsContainer = this.game.getPlayerCoinContainer(opponent_id);
                            let yOffset = 2 * 7.5 * this.game.getCssVariable('--scale');
                            let xOffset = (opponentCoinsContainer.offsetWidth - tokenNode.offsetWidth) / 2;
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
                                    token.militaryOpponentPays, // This could differ from the token value if the opponent can't afford what's on the token.
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
                    }
                }
                return dojo.fx.chain(anims);
            },
        });

        // Assign static properties / functions (these functions shouldn't make use of "this"):
        classDefinition.get = classDefinition.prototype.get;

        return classDefinition;
    });
