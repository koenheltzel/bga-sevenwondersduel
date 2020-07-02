<?php

require_once '_bga_ide_helper.php';

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

require_once 'material.inc.php';

$player1 = Player::me();
$player2 = Player::opponent();

// Calculate "Study" cost, testing fixed cost and oponent resource production.
$player1->setBuildingIds([1, 7, 20]); // 1 Wood, 1 Glass,  Wood fixed price
$player2->setBuildingIds([24, 8, 28]); // 1 Wood, 2 Papyrus
$payment = Player::me()->calculateCost(Building::get(54), 1, 0); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";
print "====================================================================================================================================";

// Calculate "Study" cost, testing fixed cost and oponent resource production.
$player1->setWonderIds([3, 7]); // Wood/Stone/Clay, Papyrus/Glass
$player1->setBuildingIds([20, 39]); // Glass/Papyrus, Wood fixed price
$player2->setBuildingIds([24, 8, 28]); // 1 Wood, 2 Papyrus
$payment = Player::me()->calculateCost(Building::get(54), 1, 0); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";
print "====================================================================================================================================";

// Calculate "Study" cost, with all 4 choice cars/wonders.
$player1->setWonderIds([3, 7]); // Wood/Stone/Clay, Papyrus/Glass
$player1->setBuildingIds([39, 40]); // Glass/Papyrus, Wood/Stone/Clay
$player2->setBuildingIds([24, 8, 28]);
$payment = Player::me()->calculateCost(Building::get(54), 1, 0); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";
print "====================================================================================================================================";

// Calculate "Courthouse" cost, with all 4 choice cars/wonders.
$player1->setWonderIds([]); // Wood/Stone/Clay, Papyrus/Glass
$player1->setBuildingIds([4, 27, 40, 39]); // Glass/Papyrus, Wood/Stone/Clay
$player2->setBuildingIds([]);
$payment = Player::me()->calculateCost(Building::get(44), 1, 0); // Study
//print "<PRE>" . print_r($payment, true) . "</PRE>";
print "====================================================================================================================================";

