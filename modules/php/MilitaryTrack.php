<?php


namespace SWD;


use SevenWondersDuelPantheon;

class MilitaryTrack extends Base
{

    public static function movePawn(Player $player, $shields, Payment $payment) {
        $divinityNeptune = $payment->getItem() instanceof Divinity && $payment->getItem()->id == 15;
        if ($divinityNeptune) {
            $payment->militarySteps = 1; // Not actually but this set off the animation
            $payment->militaryOldPosition = SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CONFLICT_PAWN_POSITION);
            $payment->militaryNewPosition = $payment->militaryOldPosition;
            $number = $payment->getItem()->neptuneMilitaryTokenNumber;
            $value = SevenWondersDuelPantheon::get()->takeMilitaryToken($number);
            $payment->militaryTokens[$payment->militaryOldPosition] = [
                'number' => $number,
                'value' => $value,
                'tokenToPlayerId' => SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::OPTION_AGORA) ? $player->id : $player->getOpponent()->id,
            ];
        }
        else {
            // If player has progress token military, an additional shield is counted.
            if($player->hasProgressToken(8) && $payment->getItem() instanceof Building) {
                $shields += 1;
            }

            SevenWondersDuelPantheon::get()->incStat($shields, SevenWondersDuelPantheon::STAT_SHIELDS, $player->id);

            $direction = $player->id == SevenWondersDuelPantheon::get()->getGameStartPlayerId() ? 1 : -1;

            $currentPosition = SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CONFLICT_PAWN_POSITION);
            $newPosition = max(-9, min(9, $currentPosition + $shields * $direction));

            $i = $currentPosition;
            while ($i != $newPosition) {
                $i += $direction;
                SevenWondersDuelPantheon::get()->setGameStateValue(SevenWondersDuelPantheon::VALUE_CONFLICT_PAWN_POSITION, $i);
                list($militaryTokenNumber, $militaryTokenValue) = MilitaryTrack::getMilitaryToken();
                if ($militaryTokenValue > 0) {
                    $payment->militaryTokens[$i] = [
                        'number' => $militaryTokenNumber,
                        'value' => $militaryTokenValue,
                        'tokenToPlayerId' => SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::OPTION_AGORA) ? $player->id : $player->getOpponent()->id,
                    ];
                }
            }

            $payment->militarySteps = abs($newPosition - $currentPosition);
            $payment->militaryOldPosition = $currentPosition;
            $payment->militaryNewPosition = $newPosition;
        }
    }
    
    public static function getVictoryPoints(Player $player) {
        $points = 0;
        $currentPosition = SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CONFLICT_PAWN_POSITION);
        if ($player->id <> SevenWondersDuelPantheon::get()->getGameStartPlayerId()) $currentPosition *= -1;
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
        $position = SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CONFLICT_PAWN_POSITION);
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
            $value = SevenWondersDuelPantheon::get()->takeMilitaryToken($number);
            if ($value == 0) {
                $number = 0;
            }
        }
        return [$number, $value];
    }

    public static function getData() {
        return [
            'tokens' => [
                1 => SevenWondersDuelPantheon::get()->getMilitaryTokenValue(1),
                2 => SevenWondersDuelPantheon::get()->getMilitaryTokenValue(2),
                3 => SevenWondersDuelPantheon::get()->getMilitaryTokenValue(3),
                4 => SevenWondersDuelPantheon::get()->getMilitaryTokenValue(4),
            ],
            'conflictPawn' => SevenWondersDuelPantheon::get()->getGameStateValue(SevenWondersDuelPantheon::VALUE_CONFLICT_PAWN_POSITION)
        ];
    }

}