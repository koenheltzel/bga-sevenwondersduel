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
    public function choose(Player $player) {
        SevenWondersDuelAgora::get()->conspiracyDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        // Text notification to all
        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose a Conspiracy and placed it face down'),
            [
                'player_name' => $player->name,
                'playerId' => $player->id,
            ]
        );

        $position = SevenWondersDuelAgora::get()->conspiracyDeck->getExtremePosition(true, $player->id);

        // Send conspiracy id to active player
        SevenWondersDuelAgora::get()->notifyPlayer($player->id,
            'constructConspiracy',
            '',
            [
                'playerId' => $player->id,
                'conspiracyId' => $this->id,
                'conspiracyPosition' => $position,
            ]
        );

        // Don't send conspiracy id to the other player / spectators, only the picked conspiracy's position in the deck's player location
        SevenWondersDuelAgora::get()->notifyPlayer($player->getOpponent()->id,
            'constructConspiracy',
            '',
            [
                'playerId' => $player->id,
                'conspiracyPosition' => $position,
            ]
        );
    }

    public function prepare(Player $player, $building) {
        SevenWondersDuelAgora::get()->buildingDeck->moveCard($building->id, 'conspiracy' . $this->id);

        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'prepareConspiracy',
            clienttranslate('${player_name} prepared a Conspiracy using building “${buildingName}”'),
            [
                'i18n' => ['buildingName'],
                'position' => $this->getPosition($player),
                'buildingId' => $building->id,
                'buildingName' => $building->name,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'conspiraciesSituation' => Conspiracies::getSituation(),
            ]
        );
    }

    /**
     * Handle any effects the item has (victory points, gain coins, military) and send notifications about them.
     * @param Player $player
     */
    public function trigger(Player $player) {
        // Set this conspiracy's "type_arg" to 1, which we use to indicate if the conspiracy is triggered.
        $sql = "UPDATE conspiracy SET card_type_arg = 1 WHERE card_id='{$this->id}'";
        self::DbQuery( $sql );

        $payment = new Payment(); // Triggering a conspiracy is free.
        parent::constructEffects($player, $payment);

        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'triggerConspiracy',
            clienttranslate('${player_name} triggered Conspiracy “${conspiracyName}”'),
            [
                'i18n' => ['conspiracyName'],
                'conspiracyPosition' => $this->getPosition($player),
                'conspiracyId' => $this->id,
                'conspiracyName' => $this->name,
                'payment' => $payment,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'conspiraciesSituation' => Conspiracies::getSituation(),
            ]
        );
        return $payment;
    }

    /**
     * Returns 0 if not prepared, else returns the age number of the building card that was used to prepare the conspiracy.
     * @return int
     */
    public function isPrepared() {
        if (!strstr($_SERVER['HTTP_HOST'], 'boardgamearena.com')) {
            // Asume we are testing cost calculation
            return true;
        }
        else {
            $cards = SevenWondersDuelAgora::get()->buildingDeck->getCardsInLocation('conspiracy' . $this->id);
            if (count($cards) > 0) {
                $card = array_shift($cards);
                return Building::get($card['id'])->age;
            }
            return 0;
        }
    }

    public function isTriggered() {
        if (!strstr($_SERVER['HTTP_HOST'], 'boardgamearena.com')) {
            // Asume we are testing cost calculation
            return true;
        }
        else {
            $card = SevenWondersDuelAgora::get()->conspiracyDeck->getCard($this->id);
            return (int)$card['type_arg'];
        }
    }

    public function getPosition(Player $player) {
        return (int)SevenWondersDuelAgora::get()->conspiracyDeck->getCard($this->id)['location_arg'];
    }

    protected function getScoreCategory() {
        return SevenWondersDuelAgora::SCORE_WONDERS;
    }

}