<?php


namespace SWD;


use Deck;
use SevenWondersDuelAgora;

class Senate extends Base
{

    public static function getSituation() {
        /** @var Deck $deck */
        $deck = SevenWondersDuelAgora::get()->influenceCubeDeck;

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
        $deck = SevenWondersDuelAgora::get()->influenceCubeDeck;
        $cubes = $deck->getCardsInLocation($player->id);
        if (count($cubes) == 0) {
            throw new \BgaUserException( clienttranslate("You have no more Influence cubes available") );
        }
        $cubeId = (array_keys($cubes))[0];
        $deck->moveCard($cubeId, "chamber{$chamber}");

        $senateAction = new SenateAction(SenateAction::ACTION_PLACE);

        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'placeInfluence',
            clienttranslate('${player_name} placed an Influence cube in Senate chamber ${chamber}'),
            [
                'chamber' => $chamber,
                'player_name' => $player->name,
                'senateAction' => $senateAction, // Reference, so will be updated after this.
            ]
        );

        $newController = self::getControllingPlayer($chamber, $senateAction);
        self::handlePossibleControlChange($oldController, $newController, $chamber, $senateAction);
    }

    public static function removeInfluence($chamber) {
        $player = Player::getActive();
        $opponent = $player->getOpponent();

        $oldController = self::getControllingPlayer($chamber);

        /** @var Deck $deck */
        $deck = SevenWondersDuelAgora::get()->influenceCubeDeck;
        $cubes = $deck->getCardsOfTypeInLocation($opponent->id, null, "chamber{$chamber}");
        if (count($cubes) == 0) {
            throw new \BgaUserException( clienttranslate("The opponent has no Influence cubes in that Senate chamber") );
        }
        $cubeId = (array_keys($cubes))[0];
        $deck->moveCard($cubeId, $opponent->id);

        $senateAction = new SenateAction(SenateAction::ACTION_REMOVE);

        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'removeInfluence',
            clienttranslate('${player_name} removed one of ${opponent_name}\'s Influence cubes from Senate chamber ${chamber}'),
            [
                'chamber' => $chamber,
                'player_name' => $player->name,
                'opponent_name' => $player->getOpponent()->name,
                'senateAction' => $senateAction, // Reference, so will be updated after this.
            ]
        );

        $newController = self::getControllingPlayer($chamber, $senateAction);
        self::handlePossibleControlChange($oldController, $newController, $chamber, $senateAction);
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
        $deck = SevenWondersDuelAgora::get()->influenceCubeDeck;
        $cubes = $deck->getCardsOfTypeInLocation($player->id, null, "chamber{$chamberFrom}");
        if (count($cubes) == 0) {
            throw new \BgaUserException( clienttranslate("There is no Influence cube from that player in the chamber") );
        }
        $cubeId = (array_keys($cubes))[0];
        $deck->moveCard($cubeId, "chamber{$chamberTo}");

        $senateAction = new SenateAction(SenateAction::ACTION_MOVE);
        $senateAction->moveFrom = $chamberFrom;
        $senateAction->moveTo = $chamberTo;

        SevenWondersDuelAgora::get()->notifyAllPlayers(
            'moveInfluence',
            clienttranslate('${player_name} moves an Influence cube from Senate chamber ${chamberFrom} to chamber ${chamberTo}'),
            [
                'chamberFrom' => $chamberFrom,
                'chamberTo' => $chamberTo,
                'player_name' => $player->name,
                'senateAction' => $senateAction, // Reference, so will be updated after this.
            ]
        );

        $newControllerFrom = self::getControllingPlayer($chamberFrom, $senateAction);
        $newControllerTo = self::getControllingPlayer($chamberTo, $senateAction);

        self::handlePossibleControlChange($oldControllerFrom, $newControllerFrom, $chamberFrom, $senateAction);
        self::handlePossibleControlChange($oldControllerTo, $newControllerTo, $chamberTo, $senateAction);
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
                if ($card['card_type_arg'] == 0) {
                    self::DbQuery( "UPDATE decree SET card_type_arg = 1 WHERE card_id = {$id}" ); // Reveal 3 out of 6 decrees.

                    $senateAction->addDecreeReveal($chamber, $card['card_location_arg'], $id);

                    SevenWondersDuelAgora::get()->notifyAllPlayers(
                        'message',
                        clienttranslate('A Decree is revealed in Senate chamber ${chamber}'),
                        [
                            'chamber' => $chamber,
                        ]
                    );
                }

                // Decree is the only one with a direct action (conflict pawn movement)
                if ($id == 9) {
                    $decree = Decree::get(9);
                    $decree->setMilitary(1);
                    $payment = $decree->controlChanged($newController == Player::getActive());
                }
            }

            SevenWondersDuelAgora::get()->notifyAllPlayers(
                'decreeControlChanged',
                clienttranslate('${player_name} gained control of Senate chamber ${chamber}'),
                [
                    'chamber' => $chamber,
                    'player_name' => $newController->name,
                    'playerId' => $newController->id,
                    'payment' => $payment,
                ]
            );
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

            SevenWondersDuelAgora::get()->notifyAllPlayers(
                'decreeControlChanged',
                clienttranslate('${player_name} lost control of Senate chamber ${chamber}'),
                [
                    'chamber' => $chamber,
                    'player_name' => $oldController->name,
                    'playerId' => $oldController->id,
                    'payment' => $payment,
                ]
            );
        }
        else {
            throw new BgaVisibleSystemException ( "A Senate chamber control changed hands within 1 Influence cube move, which should be impossible.");
        }
    }

    public static function getControllingPlayer($chamber, SenateAction &$senateAction=null) {
        /** @var Deck $deck */
        $deck = SevenWondersDuelAgora::get()->influenceCubeDeck;
        $me = Player::me();
        $opponent = Player::opponent();

        $meCubes = $deck->getCardsOfTypeInLocation($me->id, null, "chamber{$chamber}");
        $opponentCubes = $deck->getCardsOfTypeInLocation($opponent->id, null, "chamber{$chamber}");

        $controllingPlayer = null;
        if (count($meCubes) != count($opponentCubes)) {
            $controllingPlayer = count($meCubes) > count($opponentCubes) ? $me : $opponent;
        }

        if ($senateAction) {
            $senateAction->addChamber($chamber, count($meCubes), count($opponentCubes), $controllingPlayer ? $controllingPlayer->id : null);
        }

        return $controllingPlayer;
    }

}