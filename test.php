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
$buildings = $age1 + $age2 + $age3 + $guilds;

$player1 = new \SWD\Player(1);
$player2 = new \SWD\Player(2);

// Calculate "Study" cost, testing fixed cost and oponent resource production.
$player1->buildingIds = [1, 7, 14];
$player2->buildingIds = [24, 8, 28];
$payment = Player::me()->calculateCost($buildings[50]); // Study
print "<PRE>" . print_r($payment, true) . "</PRE>";

// Calculate "Study" cost, testing fixed cost and oponent resource production.
//$player1->wonderIds = [5, 8];
//$player1->buildingIds = [14, 30];
//$player2->buildingIds = [24, 8, 28];
//$payment = Player::me()->calculateCost(Building::get(50), true); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";

