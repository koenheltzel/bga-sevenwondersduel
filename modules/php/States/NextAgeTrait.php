<?php

namespace SWD\States;

use SevenWondersDuel;
use SWD\Divinities;
use SWD\Draftpool;
use SWD\OfferingTokens;
use SWD\Player;
use SWD\Players;
use SWD\Wonders;

trait NextAgeTrait
{

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argNextAge() {
        // If this function gets called before enterState, we have to increment the age (virtually) ourselves.
        $age = $this->getGameStateValue(self::VALUE_CURRENT_AGE);
        if ($age == 0 || count(Draftpool::get()['cards']) == 0) {
            $age++;
        }

        return [
            'ageRoman' => ageRoman($age),
        ];
    }

    public function enterStateNextAge() {
        $age = $this->incGameStateValue(self::VALUE_CURRENT_AGE, 1);

        // "Reveal" cards in the new age.
        Draftpool::revealCards();

        if ($age == 1) {
            if (!$this->expansionActive()) {
                SevenWondersDuel::get()->notifyAllPlayers(
                    'nextAgeDraftpoolReveal',
                    '',
                    [
                        'ageRoman' => ageRoman($age),
                        'player_name' => Player::getActive()->name,
                        'draftpool' => Draftpool::get(),
                        'playersSituation' => Players::getSituation(), // Mostly so the science symbol count is updated.
                    ]
                );
            }
            $this->gamestate->nextState(self::STATE_PLAYER_TURN_NAME);
        } else {
            $conflictPawnPosition = $this->getGameStateValue(self::VALUE_CONFLICT_PAWN_POSITION);
            if ($conflictPawnPosition == 0) {
                // In case the pawn is in the middle (position 0), the player that did the last action may decide the start player, so no need to change the active player.
                $message = clienttranslate('${player_name} must choose who begins Age ${ageRoman} (because military is equal, the last active player chooses).');
            }
            else{
                $message = clienttranslate('${player_name} must choose who begins Age ${ageRoman} (because of weaker military position)');
                // In case the pawn is NOT in the middle, the player that has the pawn on his side becomes the player to decide the start player.
                $gameStartPlayerId = SevenWondersDuel::get()->getGameStartPlayerId();
                $decisionPlayerId = $conflictPawnPosition < 0 ? $gameStartPlayerId : Player::opponent($gameStartPlayerId)->id;
                $this->gamestate->changeActivePlayer($decisionPlayerId);
            }

            $data = [
                'ageRoman' => ageRoman($age),
                'player_name' => Player::getActive()->name,
                'draftpool' => Draftpool::get(),
                'playersSituation' => Players::getSituation(), // Mostly so the science symbol count is updated.
            ];
            if (SevenWondersDuel::get()->getGameStateValue(SevenWondersDuel::OPTION_PANTHEON) && $age == 2) {
                // Add the gate to the Pantheon
                $doorSpace = -1;
                for ($space = 1; $space <= 6; $space++) {
                    $cards = Divinities::getDeckCardsSorted("space{$space}");
                    if (count($cards) == 0) {
                        $doorSpace = $space;
                        $this->divinityDeck->moveCard(16, "space{$doorSpace}");
                        break;
                    }
                }

                $enkiCard = $this->divinityDeck->getCard(1);
                if (strstr($enkiCard['location'], 'space')) {
                    // Enki when revealed in the Pantheon, select Progress Tokens
                    Divinities::setEnkiProgressTokens();
                }

                // Divinities reveal
                $data['divinitiesSituation'] = Divinities::getSituation();
                $data['doorSpace'] = $doorSpace;
            }

            SevenWondersDuel::get()->notifyAllPlayers(
                'nextAgeDraftpoolReveal',
                $message,
                $data
            );
            $this->gamestate->nextState(self::STATE_SELECT_START_PLAYER_NAME);
        }
    }
}