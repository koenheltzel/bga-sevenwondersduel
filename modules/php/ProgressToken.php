<?php

namespace SWD;

use SevenWondersDuelAgora;

class ProgressToken extends Item
{

    /**
     * @param $id
     * @return ProgressToken
     */
    public static function get($id) {
        return Material::get()->progressTokens[$id];
    }

    /**
     * @param Player $player
     * @param $cardId
     * @return PaymentPlan
     */
    public function construct(Player $player, $building = null, $discardedBuilding = false) {
        $payment = parent::construct($player, $building, $discardedBuilding);

        SevenWondersDuelAgora::get()->progressTokenDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        SevenWondersDuelAgora::get()->incStat(1, SevenWondersDuelAgora::STAT_PROGRESS_TOKENS, $player->id);

        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'progressTokenChosen',
            clienttranslate('${player_name} chose Progress token “${progressTokenName}”'),
            [
                'i18n' => ['progressTokenName'],
                'progressTokenName' => $this->name,
                'player_name' => $player->name,
                'playerId' => $player->id,
                'progressTokenId' => $this->id,
                'progressTokenPosition' => count($player->getProgressTokenIds()),
                'payment' => $payment,
            ]
        );

        $this->constructEffects($player, $payment);

        return $payment;
    }

    protected function getScoreCategory() {
        return SevenWondersDuelAgora::SCORE_PROGRESSTOKENS;
    }

}