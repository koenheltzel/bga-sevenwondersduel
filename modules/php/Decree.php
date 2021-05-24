<?php

namespace SWD;

use SevenWondersDuelPantheon;

class Decree extends Item {

    /**
     * @param $id
     * @return Decree
     */
    public static function get($id) {
        return Material::get()->decrees[$id];
    }

    public function __construct($id, $name) {
        $this->addText(clienttranslate('Benefit from the Decree\'s effect by gaining control of the corresponding Senate chamber.'), false);
        if ($id <= 16) {
            $this->addText('&nbsp;', false);
            $this->addText(clienttranslate('Effect of this Decree:'), false);
        }
        else {
            $this->addText(clienttranslate('A face down Decree is revealed when someone first places an Influence cube in the corresponding Senate chamber.'), false);
        }
        parent::__construct($id, $name);
    }

    public function controlChanged($gained) {
        $payment = new Payment($this);
        $this->constructEffects($gained ? Player::getActive() : Player::getActive()->getOpponent(), $payment);
        return $payment;
    }

    public function getChamber() {
        if (isDevEnvironment()) {
            return 1337;
        }
        else {
            $cardInfo = SevenWondersDuelPantheon::get()->decreeDeck->getCard($this->id);
            if ($cardInfo['location'] == "board") {
                return (int)substr($cardInfo['location_arg'], 0, 1);
            }
            else {
                return null;
            }
        }
    }

}