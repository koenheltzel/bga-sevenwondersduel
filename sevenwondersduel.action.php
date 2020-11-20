<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuel implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * sevenwondersduelpantheon.action.php
 *
 * SevenWondersDuel main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/sevenwondersduelpantheon/sevenwondersduelpantheon/myAction.html", ...)
 *
 */


class action_sevenwondersduelpantheon extends APP_GameAction
{
    // Constructor: please do not modify
    public function __default() {
        if (self::isArg('notifwindow')) {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
        } else {
            $this->view = "sevenwondersduelpantheon_sevenwondersduelpantheon";
            self::trace("Complete reinitialization of board game");
        }
    }

    // TODO: defines your action entry points there

    /*

    Example:

    public function myAction()
    {
        self::setAjaxMode();

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }

    */

    public function actionSelectWonder() {
        self::setAjaxMode();

        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuel::get()->actionSelectWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionSelectStartPlayer() {
        self::setAjaxMode();

        $playerId = self::getArg("playerId", AT_posint, true);
        SevenWondersDuel::get()->actionSelectStartPlayer($playerId);

        self::ajaxResponse();
    }

    public function actionConstructBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuel::get()->actionConstructBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionDiscardBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuel::get()->actionDiscardBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionConstructWonder() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuel::get()->actionConstructWonder($buildingId, $wonderId);

        self::ajaxResponse();
    }

    public function actionChooseProgressToken() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuel::get()->actionChooseProgressToken($progressTokenId);

        self::ajaxResponse();
    }

    public function actionChooseOpponentBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuel::get()->actionChooseOpponentBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionChooseProgressTokenFromBox() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuel::get()->actionChooseProgressTokenFromBox($progressTokenId);

        self::ajaxResponse();
    }

    public function actionChooseDiscardedBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuel::get()->actionChooseDiscardedBuilding($buildingId);

        self::ajaxResponse();
    }

    // Agora

    public function actionChooseConspiratorActionPlaceInfluence() {
        self::setAjaxMode();

        SevenWondersDuel::get()->actionChooseConspiratorActionPlaceInfluence();

        self::ajaxResponse();
    }

    public function actionConspire() {
        self::setAjaxMode();

        SevenWondersDuel::get()->actionConspire();

        self::ajaxResponse();
    }

    public function actionChooseConspiracy() {
        self::setAjaxMode();

        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuel::get()->actionChooseConspiracy($conspiracyId);

        self::ajaxResponse();
    }

    public function actionChooseConspireRemnantPosition() {
        self::setAjaxMode();

        $top = self::getArg("top", AT_posint, true);
        SevenWondersDuel::get()->actionChooseConspireRemnantPosition($top);

        self::ajaxResponse();
    }

    public function actionPrepareConspiracy() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuel::get()->actionPrepareConspiracy($buildingId, $conspiracyId);

        self::ajaxResponse();
    }

    public function actionTriggerConspiracy() {
        self::setAjaxMode();

        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuel::get()->actionTriggerConspiracy($conspiracyId);

        self::ajaxResponse();
    }

    public function actionPlaceInfluence() {
        self::setAjaxMode();

        $chamber = self::getArg("chamber", AT_posint, true);
        SevenWondersDuel::get()->actionPlaceInfluence($chamber);

        self::ajaxResponse();
    }

    public function actionMoveInfluence() {
        self::setAjaxMode();

        $chamberFrom = self::getArg("chamberFrom", AT_posint, true);
        $chamberTo = self::getArg("chamberTo", AT_posint, true);
        SevenWondersDuel::get()->actionMoveInfluence($chamberFrom, $chamberTo);

        self::ajaxResponse();
    }

    public function actionSkipMoveInfluence() {
        self::setAjaxMode();

        SevenWondersDuel::get()->actionSkipMoveInfluence();

        self::ajaxResponse();
    }

    public function actionRemoveInfluence() {
        self::setAjaxMode();

        $chamber = self::getArg("chamber", AT_posint, true);
        SevenWondersDuel::get()->actionRemoveInfluence($chamber);

        self::ajaxResponse();
    }

    public function actionSkipTriggerUnpreparedConspiracy() {
        self::setAjaxMode();

        SevenWondersDuel::get()->actionSkipTriggerUnpreparedConspiracy();

        self::ajaxResponse();
    }

    public function actionConstructBuildingFromBox() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuel::get()->actionConstructBuildingFromBox($buildingId);

        self::ajaxResponse();
    }

    public function actionDestroyConstructedWonder() {
        self::setAjaxMode();

        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuel::get()->actionDestroyConstructedWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionDiscardAvailableCard() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuel::get()->actionDiscardAvailableCard($buildingId);

        self::ajaxResponse();
    }

    public function actionSkipDiscardAvailableCard() {
        self::setAjaxMode();

        SevenWondersDuel::get()->actionSkipDiscardAvailableCard();

        self::ajaxResponse();
    }

    public function actionLockProgressToken() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuel::get()->actionLockProgressToken($progressTokenId);

        self::ajaxResponse();
    }

    public function actionMoveDecree() {
        self::setAjaxMode();

        $chamberFrom = self::getArg("chamberFrom", AT_posint, true);
        $chamberTo = self::getArg("chamberTo", AT_posint, true);
        SevenWondersDuel::get()->actionMoveDecree($chamberFrom, $chamberTo);

        self::ajaxResponse();
    }

    public function actionSwapBuilding() {
        self::setAjaxMode();

        $opponentBuildingId = self::getArg("opponentBuildingId", AT_posint, true);
        $meBuildingId = self::getArg("meBuildingId", AT_posint, true);
        SevenWondersDuel::get()->actionSwapBuilding($opponentBuildingId, $meBuildingId);

        self::ajaxResponse();
    }

    public function actionTakeBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuel::get()->actionTakeBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionTakeUnconstructedWonder() {
        self::setAjaxMode();

        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuel::get()->actionTakeUnconstructedWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionAdminFunction() {
        self::setAjaxMode();

        $function = self::getArg("function", AT_alphanum, true);
        SevenWondersDuel::get()->actionAdminFunction($function);

        self::ajaxResponse();
    }

}
  

