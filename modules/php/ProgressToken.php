<?php

namespace SWD;

use SevenWondersDuelPantheon;

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
    public function construct(Player $player, $building = null, $discardedBuilding = false, $offeringTokens = null) {
        $payment = parent::construct($player, $building, $discardedBuilding);

        SevenWondersDuelPantheon::get()->progressTokenDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        SevenWondersDuelPantheon::get()->incStat(1, SevenWondersDuelPantheon::STAT_PROGRESS_TOKENS, $player->id);

        $source = 'board';
        if (SevenWondersDuelPantheon::get()->gamestate->state()['name'] == SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME) {
            if (SevenWondersDuelPantheon::get()->progressTokenDeck->countCardInLocation('selection') == 2) {
                $source = 'wonder';
            }
            else {
                $source = 'conspiracy';
            }
        }
        elseif (SevenWondersDuelPantheon::get()->gamestate->state()['name'] == SevenWondersDuelPantheon::STATE_CHOOSE_ENKI_PROGRESS_TOKEN_NAME) {
            $source = 'divinity';
        }

        SevenWondersDuelPantheon::get()->notifyAllPlayers(
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
                'source' => $source,
            ]
        );

        if ($this->id == 4 && $player->hasDivinity(2) && $this->gatheredSciencePairNotification($player)) {
            $payment->selectProgressToken = true;
        }

        $this->constructEffects($player, $payment);

        return $payment;
    }

    protected function getScoreCategory() {
        return SevenWondersDuelPantheon::SCORE_PROGRESSTOKENS;
    }

}