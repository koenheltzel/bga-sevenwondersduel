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

        $newController = self::getControllingPlayer($chamber);
        self::handlePossibleControlChange($oldController, $newController, $chamber);
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

        self::updateControl($chamber);

        $newController = self::getControllingPlayer($chamber);
        self::handlePossibleControlChange($oldController, $newController, $chamber);
    }

    public static function moveInfluence($chamberFrom, $chamberTo, $player) {
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

        $newControllerFrom = self::getControllingPlayer($chamberFrom);
        $newControllerTo = self::getControllingPlayer($chamberTo);

        self::handlePossibleControlChange($oldControllerFrom, $newControllerFrom, $chamberFrom);
        self::handlePossibleControlChange($oldControllerTo, $newControllerTo, $chamberTo);
    }

    public static function handlePossibleControlChange(?Player $oldController, ?Player $newController, $chamber) {
        if ($oldController == $newController) {
            // Nothing changed, skip chamber
            return;
        }
        elseif(is_null($oldController)) {
            // Someone gained control
        }
        elseif(is_null($newController)) {
            // Someone lost control
        }
        else {
            throw new BgaVisibleSystemException ( "A Senate chamber control changed hands within 1 Influence cube move, which should be impossible.");
        }
    }

    public static function getControllingPlayer($chamber) {
        /** @var Deck $deck */
        $deck = SevenWondersDuelAgora::get()->influenceCubeDeck;
        $me = Player::me();
        $opponent = Player::opponent();

        $meCubes = $deck->getCardsOfTypeInLocation($me->id, null, "chamber{$chamber}");
        $opponentCubes = $deck->getCardsOfTypeInLocation($opponent->id, null, "chamber{$chamber}");
        if (count($meCubes) == count($opponentCubes)) {
            return null;
        }
        return count($meCubes) > count($opponentCubes) ? $me : $opponent;
    }

}