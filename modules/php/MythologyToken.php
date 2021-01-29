<?php

namespace SWD;

use SevenWondersDuelPantheon;

class MythologyToken extends Item {

    public $type;

    /**
     * @param $id
     * @return MythologyToken
     */
    public static function get($id) {
        return Material::get()->mythologyTokens[$id];
    }

    public function __construct($id, $type) {
        $this->type = $type;
        $this->addText(clienttranslate('If you must, at the end of your turn, flip over one or more face-down cards from the structure which hold a Mythology token, you must first place that token in front of yourself.'), false);
        $this->addText(clienttranslate('When you place a Mythology token in front of yourself:'), false);
        $this->addText(clienttranslate('You immediately draw the first two Divinity cards from the corresponding Mythology deck.'), true);
        $this->addText(clienttranslate('You then choose one of the two Divinity cards and place it face-down on an empty space of your choice in the Pantheon.'), true);
        $this->addText(clienttranslate('Finally, you place the remaining Divinity card face-down on top of the corresponding Mythology deck.'), true);

        parent::__construct($id, $type);
    }

    public function take($player, $building) {
        SevenWondersDuelPantheon::get()->mythologyTokenDeck->insertCardOnExtremePosition($this->id, $player->id, true);
        SevenWondersDuelPantheon::get()->divinityDeck->pickCardForLocation("mythology{$this->type}", "selection", 0);
        SevenWondersDuelPantheon::get()->divinityDeck->pickCardForLocation("mythology{$this->type}", "selection", 1);
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'takeToken',
            clienttranslate('${player_name} takes a Mythology token (${mythologyType})”'),
//            clienttranslate('${player_name} takes a Mythology token (${mythologyType}) before flipping over “${buildingName}”'),
            [
                'player_name' => $player->name,
                'i18n' => ['buildingName', 'mythologyType'],
//                'buildingName' => $building->name,
                'mythologyType' => Divinity::getTypeName($this->type),
                'type' => 'mythology',
                'tokenId' => $this->id,
                'playerId' => $player->id,
            ]
        );
    }

//    public function getPosition(Player $player) {
//        return (int)SevenWondersDuelPantheon::get()->mythologyTokenDeck->getCard($this->id)['location_arg'];
//    }

}