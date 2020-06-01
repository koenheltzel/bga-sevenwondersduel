<?php
// SWD namespace autoloader from /modules/ folder.
use SWD\Building;
use SWD\Player;

$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'SWD') {
        array_shift($classParts);
        $file = dirname(__FILE__) . "/modules/" . implode(DIRECTORY_SEPARATOR, $classParts) . ".php";
        if (file_exists($file)) {
            require_once($file);
        }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

function clienttranslate($translation) {
    return $translation;
}

require_once 'material.inc.php';

$player1 = new \SWD\Player(1);
$player2 = new \SWD\Player(2);

// Calculate "Study" cost, testing fixed cost and oponent resource production.
$player1->buildingIds = [1, 7, 20]; // 1 Wood, 1 Glass,  Wood fixed price
$player2->buildingIds = [24, 8, 28];
$payment = Player::me()->calculateCost(Building::get(54), 1, 0); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";

// Calculate "Study" cost, testing fixed cost and oponent resource production.
$player1->wonderIds = [3, 7]; // Wood/Stone/Clay, Papyrus/Glass
$player1->buildingIds = [20, 39]; // Glass/Papyrus, Wood fixed price
$player2->buildingIds = [24, 8, 28];
$payment = Player::me()->calculateCost(Building::get(54), 1, 0); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";

// Calculate "Study" cost, with all 4 choice cars/wonders.
$player1->wonderIds = [3, 7]; // Wood/Stone/Clay, Papyrus/Glass
$player1->buildingIds = [39, 40]; // Glass/Papyrus, Wood/Stone/Clay
$player2->buildingIds = [24, 8, 28];
$payment = Player::me()->calculateCost(Building::get(54), 1, 0); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";

