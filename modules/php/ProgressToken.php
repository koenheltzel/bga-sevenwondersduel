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
     * @return Payment
     */
    public function construct(Player $player, $building = null) {
        $payment = parent::construct($player);

        SevenWondersDuel::get()->progressTokenDeck->moveCard($this->id, $player->id);

        SevenWondersDuel::get()->notifyAllPlayers(
            'progressTokenChosen',
            clienttranslate('${player_name} chose progress token ${progressTokenName}.'),
            [
                'progressTokenName' => $this->name,
                'player_name' => SevenWondersDuel::get()->getCurrentPlayerName(),
                'playerId' => Player::me()->id,
                'progressTokenId' => $this->id,
                'payment' => $payment,
            ]
        );

        $this->constructEffects($player, $payment);

        return $payment;
    }

}