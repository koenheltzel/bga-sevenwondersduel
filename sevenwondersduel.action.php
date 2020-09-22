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
 * sevenwondersduel.action.php
 *
 * SevenWondersDuel main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/sevenwondersduel/sevenwondersduel/myAction.html", ...)
 *
 */


class action_sevenwondersduelagora extends APP_GameAction
{
    // Constructor: please do not modify
    public function __default() {
        if (self::isArg('notifwindow')) {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
        } else {
            $this->view = "sevenwondersduelagora_sevenwondersduelagora";
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
        SevenWondersDuelAgora::get()->actionSelectWonder($wonderId);

        self::ajaxResponse();
    }

    public function actionSelectStartPlayer() {
        self::setAjaxMode();

        $playerId = self::getArg("playerId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionSelectStartPlayer($playerId);

        self::ajaxResponse();
    }

    public function actionConstructBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionConstructBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionDiscardBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionDiscardBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionConstructWonder() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        $wonderId = self::getArg("wonderId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionConstructWonder($buildingId, $wonderId);

        self::ajaxResponse();
    }

    public function actionChooseProgressToken() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionChooseProgressToken($progressTokenId);

        self::ajaxResponse();
    }

    public function actionChooseOpponentBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionChooseOpponentBuilding($buildingId);

        self::ajaxResponse();
    }

    public function actionChooseProgressTokenFromBox() {
        self::setAjaxMode();

        $progressTokenId = self::getArg("progressTokenId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionChooseProgressTokenFromBox($progressTokenId);

        self::ajaxResponse();
    }

    public function actionChooseDiscardedBuilding() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionChooseDiscardedBuilding($buildingId);

        self::ajaxResponse();
    }

    // Agora

    public function actionConspire() {
        self::setAjaxMode();

        SevenWondersDuelAgora::get()->actionConspire();

        self::ajaxResponse();
    }

    public function actionChooseConspiracy() {
        self::setAjaxMode();

        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionChooseConspiracy($conspiracyId);

        self::ajaxResponse();
    }

    public function actionChooseConspireRemnantPosition() {
        self::setAjaxMode();

        $top = self::getArg("top", AT_posint, true);
        SevenWondersDuelAgora::get()->actionChooseConspireRemnantPosition($top);

        self::ajaxResponse();
    }

    public function actionPrepareConspiracy() {
        self::setAjaxMode();

        $buildingId = self::getArg("buildingId", AT_posint, true);
        $conspiracyId = self::getArg("conspiracyId", AT_posint, true);
        SevenWondersDuelAgora::get()->actionPrepareConspiracy($buildingId, $conspiracyId);

        self::ajaxResponse();
    }



}
  

