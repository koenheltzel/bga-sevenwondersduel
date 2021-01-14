<?php

namespace SWD;

use SevenWondersDuelPantheon;

class OfferingToken extends Item {

    public $discount;

    /**
     * @param $id
     * @return OfferingToken
     */
    public static function get($id) {
        return Material::get()->offeringTokens[$id];
    }

    public function __construct($id, $discount) {
        $this->discount = $discount;
        $this->addText(clienttranslate('If you must, at the end of your turn, flip over one or more face-down cards from the structure which hold an Offering token, you must first place that token in front of yourself.'), false);
        $this->addText(clienttranslate('The Offering token is a single-use discount used when activating a card from the Pantheon. Its value is defined by the number printed on it. The token is discarded after use.'), false);

        parent::__construct($id, $discount);
    }

    public function take($player, $building) {
        SevenWondersDuelPantheon::get()->offeringTokenDeck->insertCardOnExtremePosition($this->id, $player->id, true);
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'takeToken',
            clienttranslate('${player_name} takes a Offering token (-${discount}) before flipping over “${buildingName}”'),
            [
                'player_name' => $player->name,
                'i18n' => ['buildingName'],
                'buildingName' => $building->name,
                'discount' => OfferingToken::get($this->id)->discount,
                'type' => 'offering',
                'tokenId' => $this->id,
                'playerId' => $player->id,
            ]
        );
    }

//    public function getPosition(Player $player) {
//        return (int)SevenWondersDuelPantheon::get()->mythologyTokenDeck->getCard($this->id)['location_arg'];
//    }

}