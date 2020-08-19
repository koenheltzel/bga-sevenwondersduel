<?php

namespace SWD;

use SevenWondersDuel;

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

        SevenWondersDuel::get()->progressTokenDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        SevenWondersDuel::get()->incStat(1, SevenWondersDuel::STAT_PROGRESS_TOKENS, Player::me()->id);

        SevenWondersDuel::get()->notifyAllPlayers(
            'progressTokenChosen',
            clienttranslate('${player_name} chose Progress token “${progressTokenName}”'),
            [
                'i18n' => ['progressTokenName'],
                'progressTokenName' => $this->name,
                'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'progressTokenId' => $this->id,
                'progressTokenPosition' => count($player->getProgressTokenIds()),
                'payment' => $payment,
            ]
        );

        $this->constructEffects($player, $payment);

        return $payment;
    }

    protected function getScoreCategory() {
        return SevenWondersDuel::SCORE_PROGRESSTOKENS;
    }

}