<?php

namespace SWD;

use SevenWondersDuelPantheon;

class Divinity extends Item {

    public const TYPE_GREEN = 1;
    public const TYPE_YELLOW = 2;
    public const TYPE_BLUE = 3;
    public const TYPE_GREY = 4;
    public const TYPE_RED = 5;
    public const TYPE_GATE = 6;

    public $type;
    public $typeName;
    public $neptuneMilitaryTokenNumber = null;

    /**
     * @param $id
     * @return Divinity
     */
    public static function get($id) {
        return Material::get()->divinities[$id];
    }

    public function __construct($id, $name, $type) {
        $this->type = $type;
        $this->typeName = self::getTypeName($type);

        parent::__construct($id, $name);
    }

    public static function getTypeName($type) {
        switch ($type) {
            case Divinity::TYPE_GREEN:
                return clienttranslate('Mesopotamian');
            case Divinity::TYPE_YELLOW:
                return clienttranslate('Phoenician');
            case Divinity::TYPE_BLUE:
                return clienttranslate('Greek');
            case Divinity::TYPE_GREY:
                return clienttranslate('Egyptian');
            case Divinity::TYPE_RED:
                return clienttranslate('Roman');
            case Divinity::TYPE_GATE:
                return clienttranslate('Gate');
        }
    }

    public function place($space) {
        SevenWondersDuelPantheon::get()->divinityDeck->moveCard($this->id, "space{$space}");

        $remainingCard = SevenWondersDuelPantheon::get()->divinityDeck->getCardOnTop('selection');
        SevenWondersDuelPantheon::get()->divinityDeck->insertCardOnExtremePosition($remainingCard['id'], "mythology{$this->type}", true);

        $player = Player::getActive();

        // Text notification to all
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'message',
            clienttranslate('${player_name} chose a Divinity (${divinityType}) and placed it in the Pantheon'),
            [
                'i18n' => ['divinityType'],
                'player_name' => $player->name,
                'playerId' => $player->id,
                'divinityType' => self::getTypeName($this->type),
            ]
        );

        // Send divinity id to active player
        SevenWondersDuelPantheon::get()->notifyPlayer($player->id,
            'placeDivinity',
            '',
            [
                'playerId' => $player->id,
                'divinityId' => $this->id,
                'divinityType' => $this->type,
                'space' => $space,
            ]
        );

        // Don't send divinity id to the other player / spectators, only the picked divinity's position in the deck's player location
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'placeDivinity',
            '',
            [
                'playerId' => $player->id,
                'divinityType' => $this->type,
                'space' => $space,
            ]
        );
    }

    /**
     * @param Player $player
     * @param $token
     * @return PaymentPlan
     */
    public function neptuneApplyMilitaryToken(Player $player, $token) {
        $this->neptuneMilitaryTokenNumber = $token;

        $payment = parent::construct($player);

        $this->constructEffects($player, $payment);

        return $payment;
    }

    /**
     * @param Divinity $building
     * @param bool $free
     * @param OfferingTokens $offeringTokens
     * @return PaymentPlan
     */
    public function activate(Player $player, $free = false, $offeringTokens = null) {
        $payment = parent::construct($player, null, $free, $offeringTokens);

        if ($this->scientificSymbol) {
            if ($player->hasProgressToken(4) && $this->gatheredSciencePairNotification($player)) {
                $payment->selectProgressToken = true;
            }
        }

        SevenWondersDuelPantheon::get()->divinityDeck->insertCardOnExtremePosition($this->id, $player->id, true);

        $message = '';
        $offeringTokenString = '';
        $offeringTokenIds = [];
        if ($offeringTokens && count($offeringTokens->array) > 0) {
            $message = clienttranslate('${player_name} activated Divinity “${divinityName}” for ${cost} ${costUnit}, using Offering token(s) ${offeringTokens}');
            $parts = [];
            foreach($offeringTokens as $offeringToken) {
                $parts[] = "“-{$offeringToken->discount}”";
                $offeringTokenIds[] = $offeringToken->id;
            }
            $offeringTokenString = implode(' and ', $parts);

        }
        else {
            $message = clienttranslate('${player_name} activated Divinity “${divinityName}” for ${cost} ${costUnit}');
        }

        // Text notification to all
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'activateDivinity',
            $message,
            [
                'i18n' => ['divinityName', 'costUnit'],
                'player_name' => $player->name,
                'playerId' => $player->id,
                'divinityId' => $this->id,
                'divinityType' => $this->type,
                'divinityName' => $this->name,
                'payment' => $payment,
                'offeringTokens' => $offeringTokenString,
                'offeringTokenIds' => $offeringTokenIds,
                'cost' => $payment->totalCost() > 0 ? $payment->totalCost() : "",
                'costUnit' => $payment->totalCost() > 0 ? RESOURCES[COINS] : clienttranslate('free'),
            ]
        );

        if ($this->id != 15) { // Skip for Neptune
            parent::constructEffects($player, $payment);
        }

        switch($this->id) {
            case 4: // Astarte
                SevenWondersDuelPantheon::get()->setGameStateValue(SevenWondersDuelPantheon::VALUE_ASTARTE_COINS, 7);

                SevenWondersDuelPantheon::get()->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} places 7 coins from the bank on Divinity “${divinityName}”'),
                    [
                        'i18n' => ['divinityName'],
                        'player_name' => $player->name,
                        'divinityName' => $this->name,
                    ]
                );
                break;
        }

        SevenWondersDuelPantheon::get()->incStat(1, SevenWondersDuelPantheon::STAT_DIVINITIES_ACTIVATED, $player->id);

        if ($payment->selectProgressToken) {
            SevenWondersDuelPantheon::get()->prependStateStackAndContinue([SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_NAME]);
        }
        else {
            SevenWondersDuelPantheon::get()->setStateStack(array_merge($payment->militarySenateActions, $this->actionStates, [SevenWondersDuelPantheon::STATE_NEXT_PLAYER_TURN_NAME]));
            SevenWondersDuelPantheon::get()->stateStackNextState();
        }

        return $payment;
    }

    public function getSpace() {
        $location = SevenWondersDuelPantheon::get()->divinityDeck->getCard($this->id)['location'];
        if (strstr($location, 'space')) {
            return (int)substr($location, strlen('space'));
        }
        return -1;
    }

    public function getPosition(Player $player) {
        return (int)SevenWondersDuelPantheon::get()->divinityDeck->getCard($this->id)['location_arg'];
    }

    protected function getScoreCategory() {
        return SevenWondersDuelPantheon::SCORE_DIVINITIES;
    }

}