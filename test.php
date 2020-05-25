<?php
// SWD namespace autoloader from /modules/ folder.
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
$items = $wonders + $age1 + $age2 + $age3;

$player1 = new \SWD\Player(1);
$player2 = new \SWD\Player(2);

// Calculate "Study" cost, testing fixed cost and oponent resource production.
//$player1->items = [13, 19, 26];
//$player2->items = [36, 20, 40];
//Player::me()->calculateCost($items[62]); // Study

// Calculate "Study" cost, testing fixed cost and oponent resource production.
$player1->items = [/*13, 19,*/ 26, 5, 8, 42];
$player2->items = [36, 20, 40];
$costExplanation = Player::me()->calculateCost($items[62], true); // Study
print "<PRE>" . print_r($costExplanation, true) . "</PRE>";

