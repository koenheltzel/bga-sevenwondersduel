<?php

namespace SWD;

use SevenWondersDuelAgora;

class Conspiracy extends Item {

    /**
     * @param $id
     * @return Conspiracy
     */
    public static function get($id) {
        return Material::get()->conspiracies[$id];
    }

    /**
     * @param Conspiracy $building
     * @return PaymentPlan
     */
    public function construct(Player $player, $building = null, $discardedCard = false) {
        $payment = parent::construct($player);

        SevenWondersDuelAgora::get()->conspiracyDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'constructConspiracy',
            clienttranslate('${player_name} chose a Conspiracy and placed it face down'),
            [
                'player_name' => $player->name,
                'playerId' => $player->id,
            ]
        );


        $this->constructEffects($player, $payment);

        return $payment;
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     * @param PaymentPlan $payment
     */
    protected function constructEffects(Player $player, PaymentPlan $payment) {
        parent::constructEffects($player, $payment);
    }

    /**
     * Returns 0 if not constructed, else returns the age number of the building card that was used to construct the wonder.
     * @return int
     */
//    public function isConstructed() {
//        if (!strstr($_SERVER['HTTP_HOST'], 'boardgamearena.com')) {
//            // Asume we are testing cost calculation
//            return true;
//        }
//        else {
//            $cards = SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation('wonder' . $this->id);
//            if (count($cards) > 0) {
//                $card = array_shift($cards);
//                return Building::get($card['id'])->age;
//            }
//            return 0;
//        }
//    }

    protected function getScoreCategory() {
        return SevenWondersDuelAgora::SCORE_WONDERS;
    }

}