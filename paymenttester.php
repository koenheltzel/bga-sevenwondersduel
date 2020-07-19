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
            display: none;
            pointer-events: none;
        }
        #me #buttonToMe {
            display: none;
            pointer-events: none;
        }
        #subject #buttonToSubject {
            display: none;
            pointer-events: none;
        }
        #buildings #buttonToMaterial {
            display: none;
            pointer-events: none;
        }
        #wonders #buttonToMaterial {
            display: none;
            pointer-events: none;
        }
        #progress_tokens #buttonToMaterial {
            display: none;
            pointer-events: none;
        }
        .progress_token #buttonToSubject {
            display: none;
            pointer-events: none;
        }
        .item {
            cursor: pointer;
            margin-right: calc(0.5 * var(--gutter));
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
                <?php foreach(\SWD\Material::get()->buildings->filterByTypes([Building::TYPE_BROWN, Building::TYPE_GREY, Building::TYPE_YELLOW]) as $building):
                    if (count($building->resources) > 0 || count($building->resourceChoice) > 0 || count($building->fixedPriceResources) > 0):
                        $spritesheetColumns = 10;
                        $x = ($building->id - 1) % $spritesheetColumns;
                        $y = floor(($building->id - 1) / $spritesheetColumns);
                        ?><div id="<?= $building->id ?>"
                             class="item building building_small"
                             style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                        ></div><?php endif ?><?php endforeach ?>
            </div>
            <h3>Wonders:</h3>
            <div id="wonders">
                <?php foreach([Wonder::get(3), Wonder::get(7)] as $wonder):
                    $spritesheetColumns = 5;
                    $x = ($wonder->id - 1) % $spritesheetColumns;
                    $y = floor(($wonder->id - 1) / $spritesheetColumns);
                    ?><div id="<?= $wonder->id ?>"
                         class="item wonder wonder_small"
                         style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                    ></div><?php endforeach ?>
            </div>
            <h3>Progress tokens:</h3>
            <div id="progress_tokens">
                <?php foreach([ProgressToken::get(2), ProgressToken::get(5)] as $progressToken):
                    $spritesheetColumns = 4;
                    $x = ($progressToken->id - 1) % $spritesheetColumns;
                    $y = floor(($progressToken->id - 1) / $spritesheetColumns);
                    ?><div id="<?= $progressToken->id ?>"
                         class="item progress_token progress_token_small"
                         style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                    ></div><?php endforeach ?>
            </div>
        </td>
    </tr>
</table>
<div id="actions" style="display: none">
    <button id="buttonToOpponent">To Opponent</button>
    <button id="buttonToMe">To Me</button>
    <button id="buttonToSubject">To Subject</button>
    <button id="buttonToMaterial">Remove</button>
</div>
</body>
<script type="text/javascript">
    var currentItem = null;
    dojo.query('body').on(".item:click", (e) => {
        console.log('Item click');
        dojo.stopEvent(e);

        if (currentItem == e.target) {
            deselect();
        }
        else {
            deselect();
            currentItem = e.target;
            dojo.place( 'actions', currentItem );
            dojo.addClass(currentItem, 'actionglow');
            dojo.style( 'actions', 'display', 'inline-block' );
        }
    });
    dojo.query('#buttonToOpponent').on("click", (e) => {
        console.log('buttonToOpponent click');
        dojo.stopEvent(e);
        dojo.place( currentItem, 'opponent' );
        deselect();
    });
    dojo.query('#buttonToMe').on("click", (e) => {
        console.log('buttonToMe click');
        dojo.stopEvent(e);
        dojo.place( currentItem, 'me' );
        deselect();
    });
    dojo.query('#buttonToSubject').on("click", (e) => {
        console.log('buttonToSubject click');
        dojo.stopEvent(e);
        dojo.place( currentItem, 'subject' );
        deselect();
    });
    dojo.query('#buttonToMaterial').on("click", (e) => {
        console.log('buttonToMaterial click');
        dojo.stopEvent(e);
        if (dojo.hasClass(currentItem, 'building')) {
            dojo.place( currentItem, 'buildings' );
        }
        if (dojo.hasClass(currentItem, 'wonder')) {
            dojo.place( currentItem, 'wonders' );
        }
        if (dojo.hasClass(currentItem, 'progress_token')) {
            dojo.place( currentItem, 'progress_tokens' );
        }
        deselect();
    });

    function deselect(e) {
        if (currentItem) {
            dojo.removeClass(currentItem, 'actionglow');
            currentItem = null;
        }
        dojo.style( 'actions', 'display', 'none' );
    }
    console.log(dojo.query('#me'));
</script>
</html>