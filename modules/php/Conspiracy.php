<?php

namespace SWD;

use SevenWondersDuelAgora;

class Conspiracy extends Item {

    /**
     * @param $id
     * @return Wonder
     */
    public static function get($id) {
        return Material::get()->conspiracies[$id];
    }

    /**
     * @param Building $building
     * @return PaymentPlan
     */
//    public function construct(Player $player, $building = null, $discardedBuilding = false) {
//        $payment = parent::construct($player, $building, $discardedBuilding);
//
//        SevenWondersDuelAgora::get()->buildingDeck->moveCard($building->id, 'wonder' . $this->id);
//
//        SevenWondersDuelAgora::get()->notifyAllPlayers(
//            'constructWonder',
//            clienttranslate('${player_name} constructed wonder “${wonderName}” for ${cost} using building “${buildingName}”'),
//            [
//                'i18n' => ['wonderName', 'cost', 'buildingName'],
//                'wonderId' => $this->id,
//                'wonderName' => $this->name,
//                'buildingId' => $building->id,
//                'buildingName' => $building->name,
//                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() . " " . COINS : clienttranslate('free'),
//                'player_name' => $player->name,
//                'playerId' => $player->id,
//                'payment' => $payment,
//                'wondersSituation' => Wonders::getSituation(),
//            ]
//        );
//
//
//        $this->constructEffects($player, $payment);
//
//        return $payment;
//    }

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