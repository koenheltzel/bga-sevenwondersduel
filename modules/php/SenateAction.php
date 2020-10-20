<?php


namespace SWD;


class SenateAction extends Base
{

    const ACTION_PLACE = "place";
    const ACTION_MOVE = "move";
    const ACTION_REMOVE = "remove";
    const ACTION_MOVE_DECREE = "move_decree";

    public $action = "";
    public $moveFrom = 0;
    public $moveTo = 0;
    public $chambers = [];

    public function __construct($action) {
        $this->action = $action;
    }

    public function addChamber($chamber, $meCount, $opponentCount, $controllingPlayerId) {
        $this->chambers[$chamber] = [
            Player::me()->id => $meCount,
            Player::opponent()->id => $opponentCount,
            "controller" => $controllingPlayerId,
            "revealDecrees" => [],
        ];
    }

    /**
     * Call after calling addChamber for the corresponding chamber.
     * @param $chamber
     * @param $position
     * @param $id
     */
    public function addDecreeReveal($chamber, $position, $id) {
        $this->chambers[$chamber]["revealDecrees"][] = [
            "position" => $position,
            "id" => $id,
        ];
    }

}