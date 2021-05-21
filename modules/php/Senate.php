<?php


namespace SWD;


use Deck;
use SevenWondersDuelPantheon;

class Senate extends Base
{

    public static function getSituation() {
        /** @var Deck $deck */
        $deck = SevenWondersDuelPantheon::get()->influenceCubeDeck;

        $chambers = [];
        /** @var Player $player */
        foreach (array_values(Players::get()) as $index => $player) {
            $cubes = $deck->getCardsOfType($player->id);
            for ($chamber = 1; $chamber <= 6; $chamber++) {
                if (!isset($chambers[$chamber])) $chambers[$chamber] = [];
                $chambers[$chamber][$player->id] = self::countCubes($cubes, "chamber{$chamber}");
                if ($index == 1) {
                    if ($chambers[$chamber][$player->id] == $chambers[$chamber][$player->getOpponent()->id]) {
                        $chambers[$chamber]['controller'] = null;
                    }
                    else {
                        $chambers[$chamber]['controller'] = $chambers[$chamber][$player->id] > $chambers[$chamber][$player->getOpponent()->id] ? $player->id : $player->getOpponent()->id;
                    }
                }
            }
        }
        $data = [
            'chambers' => $chambers,
        ];
        return $data;
    }

    private static function countCubes(Array $playerDeckCards, $location) {
        return count(array_filter($playerDeckCards,
            function($card) use($location) {
                return $card['location'] == $location;
            }
        ));
    }

    public static function placeInfluence($chamber) {
        $player = Player::getActive();

        $oldController = self::getControllingPlayer($chamber);

        /** @var Deck $deck */
        $deck = SevenWondersDuelPantheon::get()->influenceCubeDeck;
        $cubes = $deck->getCardsInLocation($player->id);
        if (count($cubes) == 0) {
            throw new \BgaUserException( clienttranslate("You have no more Influence cubes available") );
        }
        $cubeId = (array_keys($cubes))[0];
        $deck->moveCard($cubeId, "chamber{$chamber}");

        $senateAction = new SenateAction(SenateAction::ACTION_PLACE);

        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'placeInfluence',
            clienttranslate('${player_name} placed an Influence cube in Senate chamber ${chamber}'),
            [
                'chamber' => $chamber,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'senateAction' => $senateAction, // Reference, so will be updated after this.
            ]
        );

        $newController = self::getControllingPlayer($chamber, $senateAction);
        return self::handlePossibleControlChange($oldController, $newController, $chamber, $senateAction);
    }

    public static function removeInfluence($chamber) {
        $player = Player::getActive();
        $opponent = $player->getOpponent();

        $oldController = self::getControllingPlayer($chamber);

        /** @var Deck $deck */
        $deck = SevenWondersDuelPantheon::get()->influenceCubeDeck;
        $cubes = $deck->getCardsOfTypeInLocation($opponent->id, null, "chamber{$chamber}");
        if (count($cubes) == 0) {
            throw new \BgaUserException( clienttranslate("The opponent has no Influence cubes in that Senate chamber") );
        }
        $cubeId = (array_keys($cubes))[0];
        $deck->moveCard($cubeId, $opponent->id);

        $senateAction = new SenateAction(SenateAction::ACTION_REMOVE);

        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'removeInfluence',
            clienttranslate('${player_name} removed one of ${opponent_name}\'s Influence cubes from Senate chamber ${chamber}'),
            [
                'chamber' => $chamber,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'opponentId' => $player->getOpponent()->id,
                'opponent_name' => $player->getOpponent()->name,
                'senateAction' => $senateAction, // Reference, so will be updated after this.
            ]
        );

        $newController = self::getControllingPlayer($chamber, $senateAction);
        return self::handlePossibleControlChange($oldController, $newController, $chamber, $senateAction);
    }

    public static function moveInfluence($chamberFrom, $chamberTo) {
        $player = Player::getActive();

        $difference = abs($chamberFrom - $chamberTo);
        if ($difference != 1) {
            throw new \BgaUserException( clienttranslate("The two selected chambers are not next to each other") );
        }

        $oldControllerFrom = self::getControllingPlayer($chamberFrom);
        $oldControllerTo = self::getControllingPlayer($chamberTo);

        /** @var Deck $deck */
        $deck = SevenWondersDuelPantheon::get()->influenceCubeDeck;
        $cubes = $deck->getCardsOfTypeInLocation($player->id, null, "chamber{$chamberFrom}");
        if (count($cubes) == 0) {
            throw new \BgaUserException( clienttranslate("There is no Influence cube from that player in the chamber") );
        }
        $cubeId = (array_keys($cubes))[0];
        $deck->moveCard($cubeId, "chamber{$chamberTo}");

        $senateAction = new SenateAction(SenateAction::ACTION_MOVE);
        $senateAction->moveFrom = $chamberFrom;
        $senateAction->moveTo = $chamberTo;

        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'moveInfluence',
            clienttranslate('${player_name} moves an Influence cube from Senate chamber ${chamberFrom} to chamber ${chamberTo}'),
            [
                'chamberFrom' => $chamberFrom,
                'chamberTo' => $chamberTo,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'senateAction' => $senateAction, // Reference, so will be updated after this.
            ]
        );

        $newControllerFrom = self::getControllingPlayer($chamberFrom, $senateAction);
        $newControllerTo = self::getControllingPlayer($chamberTo, $senateAction);

        $payment1 = self::handlePossibleControlChange($oldControllerFrom, $newControllerFrom, $chamberFrom, $senateAction);
        $payment2 = self::handlePossibleControlChange($oldControllerTo, $newControllerTo, $chamberTo, $senateAction);
        if ($payment1 && count($payment1->militarySenateActions) > 0) return $payment1;
        if ($payment2 && count($payment2->militarySenateActions) > 0) return $payment2;
        return null;
    }

    public static function moveDecree($chamberFrom, $chamberTo) {
        $player = Player::getActive();

        $cards = SevenWondersDuelPantheon::get()->decreeDeck->getCardsInLocation('board', "{$chamberFrom}1");
        $card = array_shift($cards);
        $decreeId = $card['id'];

        SevenWondersDuelPantheon::get()->decreeDeck->moveCard($decreeId, 'board', "{$chamberTo}2");

        $senateAction = new SenateAction(SenateAction::ACTION_MOVE_DECREE);
        $controllerFrom = self::getControllingPlayer($chamberFrom, $senateAction);
        $controllerTo = self::getControllingPlayer($chamberTo, $senateAction);

        // This notification updates the decrees position through decreesSituation
        SevenWondersDuelPantheon::get()->notifyAllPlayers(
            'moveDecree',
            clienttranslate('${player_name} moved the Decree in Chamber ${chamberFrom} to Chamber ${chamberTo}'),
            [
                'chamberFrom' => $chamberFrom,
                'chamberTo' => $chamberTo,
                'chamber' => $chamberTo,
                'playerId' => $player->id,
                'player_name' => $player->name,
                'senateAction' => $senateAction, // Reference, so will be updated after this.
                'decreesSituation' => Decrees::getSituation(),
            ]
        );

        $militarySenateActions = [];
        // The start and end controller is not the same
        if ($controllerFrom <> $controllerTo) {
            if ($controllerFrom) {
                // Force loss of military position by setting the opposite player as new controller, even if there is no player a new controller (aka $controllerTo == null).
                $fakeControllerTo = $controllerFrom == $player ? $player->getOpponent() : $player;

                // Controller "from" lost control of Decree 9 and has to take a step back.
                $payment = self::handleDecreeControlChange($decreeId, $fakeControllerTo, $chamberFrom, $senateAction);
                if ($payment && count($payment->militarySenateActions) > 0) {
                    $militarySenateActions = array_merge($militarySenateActions, $payment->militarySenateActions);
                }

                // This notification handles military pawn movement of the losing controller
                SevenWondersDuelPantheon::get()->notifyAllPlayers(
                    'decreeControlChanged',
                    clienttranslate(''),
                    [
                        'chamber' => $chamberFrom,
                        'player_name' => $controllerFrom->name,
                        'playerId' => $controllerFrom->id,
                        'payment' => $payment,
                    ]
                );
            }
            if ($controllerTo) {
                // Controller "to" gained control of Decree 9 and has to take a step forward.
                $payment = self::handleDecreeControlChange($decreeId, $controllerTo, $chamberTo, $senateAction);
                if ($payment && count($payment->militarySenateActions) > 0) {
                    $militarySenateActions = array_merge($militarySenateActions, $payment->militarySenateActions);
                }

                // This notification handles the decree reveal if neccesary, along with the military pawn movement of the winning controller
                SevenWondersDuelPantheon::get()->notifyAllPlayers(
                    'decreeControlChanged',
                    clienttranslate(''),
                    [
                        'chamber' => $chamberTo,
                        'player_name' => $controllerTo->name,
                        'playerId' => $controllerTo->id,
                        'payment' => $payment,
                    ]
                );
            }
        }
        return $militarySenateActions;
    }

    public static function handleDecreeControlChange($decreeId, ?Player $newController, $chamber, SenateAction &$senateAction) {
        $card = SevenWondersDuelPantheon::get()->decreeDeck->getCard($decreeId);
        if ($card['type_arg'] == 0) {
            self::DbQuery( "UPDATE decree SET card_type_arg = 1 WHERE card_id = {$decreeId}" );

            $senateAction->addDecreeReveal($chamber, $card['location_arg'], $decreeId);

            SevenWondersDuelPantheon::get()->notifyAllPlayers(
                'message',
                clienttranslate('A Decree is revealed in Senate chamber ${chamber}'),
                [
                    'chamber' => $chamber,
                ]
            );
        }

        // Decree 9 is the only one with a direct action (conflict pawn movement)
        $payment = null;
        if ($decreeId == 9) {
            $decree = Decree::get(9);
            $decree->setMilitary(1);
            $payment = $decree->controlChanged($newController == Player::getActive());
        }
        return $payment;
    }

    public static function handlePossibleControlChange(?Player $oldController, ?Player $newController, $chamber, SenateAction &$senateAction) {
        if ($oldController == $newController) {
            // Nothing changed, skip chamber
            return;
        }
        elseif(is_null($oldController)) {
            // Someone gained control

            $payment = null;
            $decrees = Decrees::getChamberDecrees($chamber);
            foreach ($decrees as $id => $card) {
                $tmpPayment = self::handleDecreeControlChange($id, $newController, $chamber, $senateAction);
                if ($tmpPayment) { // In case there are 2 decrees in 1 chamber, we need the payment of Decree 9 (only one returning a payment).
                    $payment = $tmpPayment;
                }
            }

            SevenWondersDuelPantheon::get()->notifyAllPlayers(
                'decreeControlChanged',
                clienttranslate('${player_name} gained control of Senate chamber ${chamber}'),
                [
                    'chamber' => $chamber,
                    'player_name' => $newController->name,
                    'playerId' => $newController->id,
                    'payment' => $payment,
                ]
            );
            return $payment;
        }
        elseif(is_null($newController)) {
            // Someone lost control

            $payment = null;
            $decrees = Decrees::getChamberDecrees($chamber);
            foreach ($decrees as $id => $card) {
                // Decree is the only one with a direct action (conflict pawn movement)
                if ($id == 9) {
                    $decree = Decree::get(9);
                    $decree->setMilitary(1);
                    $payment = $decree->controlChanged($oldController == Player::opponent());
                }
            }

            SevenWondersDuelPantheon::get()->notifyAllPlayers(
                'decreeControlChanged',
                clienttranslate('${player_name} lost control of Senate chamber ${chamber}'),
                [
                    'chamber' => $chamber,
                    'player_name' => $oldController->name,
                    'playerId' => $oldController->id,
                    'payment' => $payment,
                ]
            );
            return $payment;
        }
        else {
            throw new BgaVisibleSystemException ( "A Senate chamber control changed hands within 1 Influence cube move, which should be impossible.");
        }
    }

    public static function getControllingPlayer($chamber, SenateAction &$senateAction=null) {
        $controllingPlayer = null;
        if ($chamber) {
            /** @var Deck $deck */
            $deck = SevenWondersDuelPantheon::get()->influenceCubeDeck;
            $me = Player::me();
            $opponent = Player::opponent();

            $meCubes = $deck->getCardsOfTypeInLocation($me->id, null, "chamber{$chamber}");
            $opponentCubes = $deck->getCardsOfTypeInLocation($opponent->id, null, "chamber{$chamber}");

            if (count($meCubes) != count($opponentCubes)) {
                $controllingPlayer = count($meCubes) > count($opponentCubes) ? $me : $opponent;
            }

            if ($senateAction) {
                $senateAction->addChamber($chamber, count($meCubes), count($opponentCubes), $controllingPlayer ? $controllingPlayer->id : null);
            }
        }
        return $controllingPlayer;
    }

}