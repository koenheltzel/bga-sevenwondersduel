<?php

require_once '_bga_ide_helper.php';

// SWD namespace autoloader from /modules/php/ folder.
use SWD\Building;
use SWD\Player;
use SWD\ProgressToken;use SWD\Wonder;

$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'SWD') {
        array_shift($classParts);
        $file = dirname(__FILE__) . "/modules/php/" . implode(DIRECTORY_SEPARATOR, $classParts) . ".php";
        if (file_exists($file)) {
            require_once($file);
        }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once 'material.inc.php';

?><!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="sevenwondersduel.css">
    <style>
        td {
            vertical-align: top;
        }
        div {
            display: block;
        }
        #opponent>div, #me>div, #subject>div, #buildings>div, #wonders>div, #progress_tokens>div {
            /*float:left;*/
        }
        #actions {
            display: inline-block;
            vertical-align: top;
            width: 150px;
            float: left;
        }
        #actions button {
            display: block;
            margin-bottom: 10px;
            margin: var(--gutter);
        }

        #opponent #buttonToOpponent {
            opacity: 0.5;
            pointer-events: none;
        }
        #me #buttonToMe {
            opacity: 0.5;
            pointer-events: none;
        }
        #subject #buttonToSubject {
            opacity: 0.5;
            pointer-events: none;
        }
        #buildings #buttonToMaterial {
            opacity: 0.5;
            pointer-events: none;
        }
        #wonders #buttonToMaterial {
            opacity: 0.5;
            pointer-events: none;
        }
        #progress_tokens #buttonToMaterial {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
    <script src="//ajax.googleapis.com/ajax/libs/dojo/1.14.1/dojo/dojo.js"></script>
</head>
<body>
<table>
    <tr>
        <td width="50%">
            <h3>Opponent:</h3>
            <div id="opponent"></div>
            <h3>Me:</h3>
            <div id="me"></div>
            <h3>Payment Plan Subject:</h3>
            <div id="subject"></div>
            <h3>Payment Plan:</h3>
            <div id="plan"></div>
        </td>
        <td width="50%" id="material">
            <h3>Buildings:</h3>
            <div id="buildings">
                <?php foreach(\SWD\Material::get()->buildings->filterByTypes([Building::TYPE_BROWN, Building::TYPE_GREY, Building::TYPE_YELLOW]) as $building): ?>
                    <?php
                    if (count($building->resources) > 0 || count($building->resourceChoice) > 0 || count($building->fixedPriceResources) > 0):
                        $spritesheetColumns = 10;
                        $x = ($building->id - 1) % $spritesheetColumns;
                        $y = floor(($building->id - 1) / $spritesheetColumns);
                        ?>
                        <div id="<?= $building->id ?>"
                             class="item building building_small"
                             style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                        ></div>
                    <?php endif ?>
                <?php endforeach ?>
                <div id="actions">
                    <button id="buttonToOpponent">To Opponent</button>
                    <button id="buttonToMe">To Me</button>
                    <button id="buttonToSubject">To Subject</button>
                    <button id="buttonToMaterial">Remove</button>
                </div>
            </div>
            <h3>Wonders:</h3>
            <div id="buildings">
                <?php foreach([Wonder::get(3), Wonder::get(7)] as $wonder): ?>
                    <?php
                    $spritesheetColumns = 5;
                    $x = ($wonder->id - 1) % $spritesheetColumns;
                    $y = floor(($wonder->id - 1) / $spritesheetColumns);
                    ?>
                    <div id="<?= $wonder->id ?>"
                         class="item wonder wonder_small"
                         style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                    ></div>
                <?php endforeach ?>
            </div>
            <h3>Progress tokens:</h3>
            <div id="progress_tokens">
                <?php foreach([ProgressToken::get(2), ProgressToken::get(5)] as $progressToken): ?>
                    <?php
                    $spritesheetColumns = 4;
                    $x = ($progressToken->id - 1) % $spritesheetColumns;
                    $y = floor(($progressToken->id - 1) / $spritesheetColumns);
                    ?>
                    <div id="<?= $progressToken->id ?>"
                         class="item progress_token progress_token_small"
                         style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                    ></div>
                <?php endforeach ?>
            </div>
        </td>
    </tr>
</table>
</body>
<script type="text/javascript">
    var currentItem = null;
    dojo.query('body').on(".item:click", (e) => {
        if (currentItem) {
            dojo.removeClass(currentItem, 'actionglow');
        }

        if (currentItem == e.target) {
        }
        else {
            currentItem = e.target;
            dojo.place( 'actions', currentItem );
            dojo.addClass(currentItem, 'actionglow');
        }


    });

    // function itemClickHandler(e) {
    //
    // }
    console.log(dojo.query('#me'));
</script>
</html>