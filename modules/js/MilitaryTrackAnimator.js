/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuelPantheon implementation : © Koen Heltzel <koenheltzel@gmail.com>
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
                this.game = bgagame.sevenwondersduelpantheon.instance;
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

                    let startPosition = parseInt(payment.militaryOldPosition);
                    let endPosition = parseInt(payment.militaryNewPosition);
                    let delta = this.delta(startPosition, endPosition);
                    let stepSize = delta / payment.militarySteps;

                    let position = startPosition;
                    let divinationNeptune = position == endPosition && payment.militarySteps > 0;
                    while(position != endPosition || divinationNeptune) {
                        divinationNeptune = false;

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

                        // Then check if a Poliorcetics coin should be animated.
                        if (payment.militaryPoliorceticsPositions[position]) {
                            let opponentCoinsContainer = this.game.getPlayerCoinContainer(opponent_id);

                            // Animate coins to fly from opponent coins total to conflict pawn
                            anims.push(bgagame.CoinAnimator.get().getAnimation(
                                opponentCoinsContainer,
                                dojo.query('#board_container .military_position[data-position="' + position + '"]')[0],
                                1,
                                opponent_id,
                            ));
                        }
                        // Then check if a token was encountered (before performing the next step).
                        if (payment.militaryTokens[position]) {
                            let token = payment.militaryTokens[position];
                            let tokenNumber = this.game.invertMilitaryTrack() ? (5 - token.number) : token.number;
                            let tokenNode = dojo.query('#military_tokens>div:nth-of-type(' + tokenNumber + ')>.military_token')[0];

                            if (this.game.agora) {
                                let playerNameContainer = dojo.query('.player_info.' + this.game.getPlayerAlias(token.tokenToPlayerId) + ' .player_area_name')[0];

                                // Move and rotate military token to player.
                                let yOffset = 7.5 * this.game.getCssVariable('--scale');
                                let xOffset = (playerNameContainer.offsetWidth - tokenNode.offsetWidth) / 2;
                                let rotateCompensation = (tokenNode.offsetHeight - tokenNode.offsetWidth) / 2
                                if (token.tokenToPlayerId == this.game.me_id) {
                                    yOffset = -rotateCompensation + -tokenNode.offsetWidth - yOffset;
                                }
                                else {
                                    yOffset = -rotateCompensation + playerNameContainer.offsetHeight + yOffset;
                                }
                                anims.push(
                                    dojo.fx.combine([
                                        this.game.slideToObjectPos(tokenNode, playerNameContainer, xOffset, yOffset, this.militaryTokenAnimationDuration * 0.6),
                                        // Animate coins to fly from opponent coins total to military token.
                                        dojo.animateProperty({
                                            node: tokenNode,
                                            // delay: this.constructWonderAnimationDuration / 6,
                                            duration: this.militaryTokenAnimationDuration * 0.6,
                                            properties: {
                                                propertyTransform: {start: 0, end: -90}
                                            },
                                            onAnimate: function (values) {
                                                dojo.style(this.node, 'transform', 'rotate(' + parseFloat(values.propertyTransform.replace("px", "")) + 'deg)');
                                            }
                                        })
                                    ])
                                );
                            }
                            else {
                                let opponentCoinsContainer = this.game.getPlayerCoinContainer(opponent_id);
                                let yOffset = 2 * 7.5 * this.game.getCssVariable('--scale');
                                let xOffset = (opponentCoinsContainer.offsetWidth - tokenNode.offsetWidth) / 2;
                                if (opponent_id == this.game.me_id) {
                                    yOffset = -tokenNode.offsetHeight - yOffset;
                                }
                                else {
                                    yOffset = opponentCoinsContainer.offsetHeight + yOffset;
                                }

                                // Move military token close to opponent coins total.
                                anims.push(this.game.slideToObjectPos(tokenNode, opponentCoinsContainer, xOffset, yOffset, this.militaryTokenAnimationDuration * 0.6));
                                // Animate coins to fly from opponent coins total to military token.
                                anims.push(bgagame.CoinAnimator.get().getAnimation(
                                    opponentCoinsContainer,
                                    opponentCoinsContainer,
                                    token.militaryOpponentPays, // This could differ from the token value if the opponent can't afford what's on the token.
                                    opponent_id,
                                    [0, 0],
                                    [0, yOffset + ((tokenNode.offsetHeight - opponentCoinsContainer.offsetHeight) / 2)]
                                ));
                            }

                            // Fade out military token.
                            anims.push(dojo.fadeOut({
                                node: tokenNode,
                                duration: this.militaryTokenAnimationDuration * 0.4,
                                onEnd: dojo.hitch(this, function (node) {
                                    dojo.destroy(node);
                                })
                            }));
                        }
                    }

                    if (payment.militaryRemoveMinerva) {
                        // Fade out Minerva pawn.
                        anims.push(dojo.fadeOut({
                            node: $('minerva_pawn'),
                            duration: this.militaryTokenAnimationDuration * 0.4,
                            onEnd: dojo.hitch(this, function (node) {
                                // dojo.destroy(node);
                            })
                        }));
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
