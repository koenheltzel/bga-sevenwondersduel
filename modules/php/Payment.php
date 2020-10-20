<?php


namespace SWD;


class Payment extends PaymentPlan
{

    public $militaryOldPosition = 0;
    public $militaryNewPosition = 0;
    public $militarySteps = 0;
    public $militaryTokens = [];
    public $militaryOpponentPays = 0;

    public $selectProgressToken = false;
    public $urbanismAward = 0;
    public $coinReward = 0;
    public $opponentCoinLoss = 0;
    public $cost = 0;
    public $economyProgressTokenCoins = 0;
    public $discardedCard = false;
    public $eightWonderId = null;

    // Agora
    public $decreeCoinReward = null;
    public $decreeCoinRewardDecreeId = null;
    public $decreeCoinRewardPlayerId = null;
    public $militarySenateActions = [];
    public $coinsFromOpponent = 0;

}