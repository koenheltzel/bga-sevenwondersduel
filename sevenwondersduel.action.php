<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SevenWondersDuelPantheon implementation : © Koen Heltzel <koenheltzel@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * sevenwondersduelpantheon.action.php
 *
 * SevenWondersDuelPantheon main action entry point
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
        SevenWondersDuelPantheon::get()->actionSelectWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionSelectStartPlayer() {
        self::setAjaxMode();

        $playerId = self::getArg("playerId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionSelectStartPlayer($playerId);

        self::ajaxResponse();
    }

    public function actionConstructBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionConstructBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionDiscardBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionDiscardBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionConstructWonder() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionConstructWonder($buildingId, $wonderId);

        self::ajaxResponse();
    }

    public function actionChooseProgressToken() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseProgressToken($progressTokenId);

        self::ajaxResponse();
    }

    public function actionChooseOpponentBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseOpponentBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionChooseProgressTokenFromBox() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseProgressTokenFromBox($progressTokenId);

        self::ajaxResponse();
    }

    public function actionChooseDiscardedBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseDiscardedBuilding($buildingId);

        self::ajaxResponse();
    }

    // Pantheon

    public function actionChooseAndPlaceDivinity() {
        self::setAjaxMode();

        $divinityId = self::getArg("divinityId", AT_posint, true);
        $space = self::getArg("space", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseAndPlaceDivinity($divinityId, $space);

        self::ajaxResponse();
    }

    public function actionActivateDivinity() {
        self::setAjaxMode();

        $divinityId = self::getArg("divinityId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionActivateDivinity($divinityId);

        self::ajaxResponse();
    }

    public function actionDeconstructWonder() {
        self::setAjaxMode();

        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionDeconstructWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionChooseEnkiProgressToken() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseEnkiProgressToken($progressTokenId);

        self::ajaxResponse();
    }

    public function actionPlaceSnakeToken() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionPlaceSnakeToken($buildingId);

        self::ajaxResponse();
    }

    public function actionDiscardAgeCard() {
        self::setAjaxMode();

        $row = self::getArg("row", AT_posint, true);
        $column = self::getArg("column", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionDiscardAgeCard($row, $column);

        self::ajaxResponse();
    }

    public function actionPlaceMinervaToken() {
        self::setAjaxMode();

        $position = self::getArg("position", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionPlaceMinervaToken($position);

        self::ajaxResponse();
    }

    public function actionDiscardMilitaryToken() {
        self::setAjaxMode();

        $token = self::getArg("token", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionDiscardMilitaryToken($token);

        self::ajaxResponse();
    }

    public function actionApplyMilitaryToken() {
        self::setAjaxMode();

        $token = self::getArg("token", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionApplyMilitaryToken($token);

        self::ajaxResponse();
    }

    // Agora

    public function actionChooseConspiratorActionPlaceInfluence() {
        self::setAjaxMode();

        SevenWondersDuelPantheon::get()->actionChooseConspiratorActionPlaceInfluence();

        self::ajaxResponse();
    }

    public function actionConspire() {
        self::setAjaxMode();

        SevenWondersDuelPantheon::get()->actionConspire();

        self::ajaxResponse();
    }

    public function actionChooseConspiracy() {
        self::setAjaxMode();

        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseConspiracy($conspiracyId);

        self::ajaxResponse();
    }

    public function actionChooseConspireRemnantPosition() {
        self::setAjaxMode();

        $top = self::getArg("top", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionChooseConspireRemnantPosition($top);

        self::ajaxResponse();
    }

    public function actionPrepareConspiracy() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionPrepareConspiracy($buildingId, $conspiracyId);

        self::ajaxResponse();
    }

    public function actionTriggerConspiracy() {
        self::setAjaxMode();

        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionTriggerConspiracy($conspiracyId);

        self::ajaxResponse();
    }

    public function actionPlaceInfluence() {
        self::setAjaxMode();

        $chamber = self::getArg("chamber", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionPlaceInfluence($chamber);

        self::ajaxResponse();
    }

    public function actionMoveInfluence() {
        self::setAjaxMode();

        $chamberFrom = self::getArg("chamberFrom", AT_posint, true);
        $chamberTo = self::getArg("chamberTo", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionMoveInfluence($chamberFrom, $chamberTo);

        self::ajaxResponse();
    }

    public function actionSkipMoveInfluence() {
        self::setAjaxMode();

        SevenWondersDuelPantheon::get()->actionSkipMoveInfluence();

        self::ajaxResponse();
    }

    public function actionRemoveInfluence() {
        self::setAjaxMode();

        $chamber = self::getArg("chamber", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionRemoveInfluence($chamber);

        self::ajaxResponse();
    }

    public function actionSkipTriggerUnpreparedConspiracy() {
        self::setAjaxMode();

        SevenWondersDuelPantheon::get()->actionSkipTriggerUnpreparedConspiracy();

        self::ajaxResponse();
    }

    public function actionConstructBuildingFromBox() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionConstructBuildingFromBox($buildingId);

        self::ajaxResponse();
    }

    public function actionDestroyConstructedWonder() {
        self::setAjaxMode();

        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionDestroyConstructedWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionDiscardAvailableCard() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionDiscardAvailableCard($buildingId);

        self::ajaxResponse();
    }

    public function actionSkipDiscardAvailableCard() {
        self::setAjaxMode();

        SevenWondersDuelPantheon::get()->actionSkipDiscardAvailableCard();

        self::ajaxResponse();
    }

    public function actionLockProgressToken() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionLockProgressToken($progressTokenId);

        self::ajaxResponse();
    }

    public function actionMoveDecree() {
        self::setAjaxMode();

        $chamberFrom = self::getArg("chamberFrom", AT_posint, true);
        $chamberTo = self::getArg("chamberTo", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionMoveDecree($chamberFrom, $chamberTo);

        self::ajaxResponse();
    }

    public function actionSwapBuilding() {
        self::setAjaxMode();

        $opponentBuildingId = self::getArg("opponentBuildingId", AT_posint, true);
        $meBuildingId = self::getArg("meBuildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionSwapBuilding($opponentBuildingId, $meBuildingId);

        self::ajaxResponse();
    }

    public function actionTakeBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionTakeBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionTakeUnconstructedWonder() {
        self::setAjaxMode();

        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuelPantheon::get()->actionTakeUnconstructedWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionAdminFunction() {
        self::setAjaxMode();

        $function = self::getArg("function", AT_alphanum, true);
        SevenWondersDuelPantheon::get()->actionAdminFunction($function);

        self::ajaxResponse();
    }

}
  

