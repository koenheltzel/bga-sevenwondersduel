<?php

namespace SWD\States;

use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\ProgressToken;
use SWD\Senate;
use SWD\Wonders;

trait SenateActionsTrait {

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argSenateActions() {
        $data = [
            'senateActionsLeft' => $this->getGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT),
            'senateActionsSection' => $this->getGameStateValue(self::VALUE_SENATE_ACTIONS_SECTION),
            'senateSituation' => Senate::getSituation(),
            // TODO: not sure what is needed below, I can image the cost of age/wonder cards can change with certain Decrees
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
        $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        return $data;
    }

    public function enterStateSenateActions() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionPlaceInfluence($chamber) {
        $this->checkAction("actionPlaceInfluence");

        $payment = Senate::placeInfluence($chamber);

        $this->senateActionNextState($payment ? $payment->militarySenateActions : []);
    }

    public function actionMoveInfluence($chamberFrom, $chamberTo) {
        $this->checkAction("actionMoveInfluence");

        $payment = Senate::moveInfluence($chamberFrom, $chamberTo);

        $this->senateActionNextState($payment ? $payment->militarySenateActions : []);
    }

    public function actionSkipMoveInfluence() {
        $this->checkAction("actionSkipMoveInfluence");

        if ($this->gamestate->state()['name'] == self::STATE_SENATE_ACTIONS_NAME) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} skips the remaining ${actionLeft} Senate Action(s)'),
                [
                    'actionLeft' => $this->getGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT),
                    'player_name' => Player::getActive()->name,
                ]
            );
        }
        else {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} chose not to move an Influence cube'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );
        }

        $this->setGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT, 0);
        $this->senateActionNextState();
    }

    public function actionRemoveInfluence($chamber) {
        $this->checkAction("actionRemoveInfluence");

        $payment = Senate::removeInfluence($chamber);

        $this->senateActionNextState($payment ? $payment->militarySenateActions : []);
    }

    public function senateActionNextState($militarySenateActions=[]) {
        if ($this->gamestate->state()['name'] == self::STATE_SENATE_ACTIONS_NAME) {
            if ((int)($this->incGameStateValue(self::VALUE_SENATE_ACTIONS_LEFT, -1)) > 0) {
                if ($this->checkImmediateVictory()) {
                    $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
                }
                else {
                    if (count($militarySenateActions) > 0) {
                        $this->prependStateStack([self::STATE_SENATE_ACTIONS_NAME]); // Continue with senate actions after..
                        $this->prependStateStackAndContinue($militarySenateActions); // First handle military token senate action(s)
                    }
                    else {
                        $this->gamestate->nextState( self::STATE_SENATE_ACTIONS_NAME);
                    }
                }
            }
            else {
                $this->prependStateStackAndContinue($militarySenateActions);
            }
        }
        else {
            $this->prependStateStackAndContinue($militarySenateActions);
        }
    }

    public function stateStackNextState($stateIfEmpty = self::STATE_NEXT_PLAYER_TURN_NAME) {
        if ($this->checkImmediateVictory()) {
            $this->gamestate->nextState( self::STATE_GAME_END_DEBUG_NAME );
        }
        else {
            $stack = json_decode($this->getGameStateValue(self::VALUE_STATE_STACK));

            while (count($stack) > 0) {
                $nextState = array_shift($stack);
                $shouldSkipMethod = "shouldSkip" . ucfirst($nextState);
                if (method_exists($this, $shouldSkipMethod) &&
                    $this->{$shouldSkipMethod}()
                ) {
                    // We now have skipped this state, continue the while loop.
                }
                else {
                    // Skip method does not exist or result is false, we can go into this state.
                    $this->setGameStateValue(self::VALUE_STATE_STACK, json_encode($stack));
                    $this->gamestate->nextState( $nextState );
                    return;
                }
            }

            $this->gamestate->nextState( $stateIfEmpty );
        }
    }

    public function prependStateStackAndContinue(Array $states) {
        $this->prependStateStack($states);
        $this->stateStackNextState();
    }

    public function prependStateStack(Array $states) {
        $stack = json_decode($this->getGameStateValue(self::VALUE_STATE_STACK));
        $stack = array_merge($states, $stack);
        $this->setGameStateValue(self::VALUE_STATE_STACK, json_encode($stack));
    }


    public function setStateStack($stack) {
        $this->setGameStateValue(self::VALUE_STATE_STACK, json_encode($stack));
    }
}