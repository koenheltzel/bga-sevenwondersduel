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
        $this->addText(clienttranslate('If you must, at the end of your turn, flip over one or more face-down cards from the structure which hold a Mythology or Offering token, you must first place that token in front of yourself.'), false);
        $this->addText(clienttranslate('The Offering is a single-use discount used when activating a card from the Pantheon. Its value is defined by the number printed on it. The token is discarded after use.'), false);

        parent::__construct($id, $discount);
    }

//    public function getPosition(Player $player) {
//        return (int)SevenWondersDuelPantheon::get()->mythologyTokenDeck->getCard($this->id)['location_arg'];
//    }

}