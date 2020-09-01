<?php


namespace SWD;


use SevenWondersDuelAgora;

class MilitaryTrack extends Base
{

    public static function movePawn(Player $player, $shields, PaymentPlan $payment) {
        // If player has progress token military, an additional shield is counted.
        if($player->hasProgressToken(8) && $payment->getItem() instanceof Building) {
            $shields += 1;
        }
        SevenWondersDuelAgora::get()->incStat($shields, SevenWondersDuelAgora::STAT_SHIELDS, $player->id);

        if ($player->id <> SevenWondersDuelAgora::get()->getGameStartPlayerId()) {
            $shields *= -1;
        }

        $currentPosition = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CONFLICT_PAWN_POSITION);
        $newPosition = max(-9, min(9, $currentPosition + $shields));
        SevenWondersDuelAgora::get()->setGameStateValue(SevenWondersDuelAgora::VALUE_CONFLICT_PAWN_POSITION, $newPosition);

        $payment->militarySteps = abs($newPosition - $currentPosition);
        $payment->militaryOldPosition = $currentPosition;
        $payment->militaryNewPosition = $newPosition;
    }
    
    public static function getVictoryPoints(Player $player) {
        $points = 0;
        $currentPosition = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CONFLICT_PAWN_POSITION);
        if ($player->id <> SevenWondersDuelAgora::get()->getGameStartPlayerId()) $currentPosition *= -1;
        if ($currentPosition > 0) {
            switch (abs($currentPosition)) {
                case 1:
                case 2:
                    $points = 2;
                    break;
                case 3:
                case 4:
                case 5:
                    $points = 5;
                    break;
                case 6:
                case 7:
                case 8:
                    $points = 10;
                    break;
            }
        }
        return $points;
    }

    public static function getMilitaryToken() {
        $position = SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CONFLICT_PAWN_POSITION);
        $number = 0;
        if ($position >= -8 && $position <= -6) {
            $number = 1;
        }
        else if ($position >= -5 && $position <= -3) {
            $number = 2;
        }
        else if ($position >= 3 && $position <= 5) {
            $number = 3;
        }
        else if ($position >= 6 && $position <= 8) {
            $number = 4;
        }
        $value = 0;
        if ($number > 0) {
            $value = SevenWondersDuelAgora::get()->takeMilitaryToken($number);
            if ($value == 0) {
                $number = 0;
            }
        }
        return [$number, $value];
    }

    public static function getData() {
        return [
            'tokens' => [
                1 => SevenWondersDuelAgora::get()->getMilitaryTokenValue(1),
                2 => SevenWondersDuelAgora::get()->getMilitaryTokenValue(2),
                3 => SevenWondersDuelAgora::get()->getMilitaryTokenValue(3),
                4 => SevenWondersDuelAgora::get()->getMilitaryTokenValue(4),
            ],
            'conflictPawn' => SevenWondersDuelAgora::get()->getGameStateValue(SevenWondersDuelAgora::VALUE_CONFLICT_PAWN_POSITION)
        ];
    }

}