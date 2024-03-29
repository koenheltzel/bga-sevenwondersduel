<?php

namespace SWD\States;

use SWD\Building;
use SWD\Divinities;
use SWD\Draftpool;
use SWD\Player;
use SWD\Players;
use SWD\Wonder;
use SWD\Wonders;

trait ChooseDiscardedBuildingTrait
{

    /**
     * Which data to pass with the state change to onEnteringState() in JavaScript.
     * Warning: this "arg" method can be called before the "enterState" method so don't expect data modifications by the "enterState" method to be available in the "arg" method!
     * @return array
     */
    public function argChooseDiscardedBuilding() {
        $data = [
            'draftpool' => Draftpool::get(),
            'wondersSituation' => Wonders::getSituation(),
            'playersSituation' => Players::getSituation(),
        ];
        if ($this->getGameStateValue(self::OPTION_PANTHEON)) {
            $data['divinitiesSituation'] = Divinities::getSituation();
        }
        if ($this->getGameStateValue(self::OPTION_AGORA)) {
            $this->addConspiraciesSituation($data); // When refreshing the page in this state, the private information should be passed.
        }
        return $data;
    }

    public function enterStateChooseDiscardedBuilding() {
        $this->giveExtraTime($this->getActivePlayerId());
    }

    public function actionChooseDiscardedBuilding($buildingId) {
        $this->checkAction("actionChooseDiscardedBuilding");

        $cardInfo = $this->buildingDeck->getCard($buildingId);
        if ($cardInfo['location'] != 'discard') {
            throw new \BgaUserException( clienttranslate("The building you selected is not available.") );
        }

        $building = Building::get($buildingId);
        $payment = $building->construct(Player::getActive(), null, true);

        $this->transitionAfterConstructBuilding($building, $payment);
    }

    public function shouldSkipChooseDiscardedBuilding() {
        if (count($this->buildingDeck->getCardsInLocation('discard')) == 0) {
            $this->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} can\'t choose a discarded card'),
                [
                    'player_name' => Player::getActive()->name,
                ]
            );
            return true;
        }
        return false;
    }
}