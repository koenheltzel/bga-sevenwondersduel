<?php
require_once 'sevenwondersduel.game.php';

// SWD namespace autoloader from /modules/php/ folder.
use SWD\Building;
use SWD\Decree;
use SWD\MythologyToken;
use SWD\Player;
use SWD\ProgressToken;
use SWD\Wonder;

require_once 'material.inc.php';

$baseurl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]".dirname($_SERVER['REQUEST_URI']) . "/";
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]".$_SERVER['REQUEST_URI'];

$fileName = basename($_SERVER['SCRIPT_NAME']);
$jsonFile = 'paymenttester_' . $_SERVER['HTTP_HOST'] . '.json';
if (!file_exists($jsonFile)) file_put_contents($jsonFile, '[]');
$scenarios = json_decode(file_get_contents($jsonFile), true);
if (isset($_POST['name'])) {
    $scenarios[$_POST['name']] = $_SERVER['QUERY_STRING'];
    file_put_contents($jsonFile, json_encode($scenarios, JSON_PRETTY_PRINT));
    header("Location: " . $url . '&name=' . urlencode($_POST['name']));
    exit;
}

if (!isset($_GET['me_decrees'])) $_GET['me_decrees'] = "";
if (!isset($_GET['opponent_decrees'])) $_GET['opponent_decrees'] = "";

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
        #mythology_tokens #buttonToMaterial {
            display: none;
            pointer-events: none;
        }
        #plan {
            width: 100%;
            height: 900px;
        }
        .wonder #buttonToOpponent {
            display: none;
            pointer-events: none;
        }
        .progress_token #buttonToOpponent {
            display: none;
            pointer-events: none;
        }
        .progress_token #buttonToSubject {
            display: none;
            pointer-events: none;
        }
        .mythology_token #buttonToOpponent {
            display: none;
            pointer-events: none;
        }
        .mythology_token #buttonToSubject {
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
            <h3>Scenarios:</h3>
            <div style="margin-left: 20px">
                <p><a href="<?= "{$baseurl}{$fileName}" ?>">New scenario</a></p>
                <strong>Saved scenarios:</strong>
                <ul>
                    <?php foreach($scenarios as $name => $queryString): ?>
                        <li><a href="<?= "{$baseurl}{$fileName}?{$queryString}&name=" . urlencode($name) ?>"><?= $name ?></a></li>
                    <?php endforeach ?>
                </ul>
                <strong>Save scenario (overwrite if same name):</strong>
                <form method="post">
                    <input type="text" id="name" name="name" size="50" value="<?= isset($_GET['name']) ? $_GET['name'] : '' ?>" />
                    <input type="submit" value="Save" />
                </form>
            </div>

            <h3>Opponent:</h3>
            <div id="opponent"></div>
            <h3>Me:</h3>
            <div id="me"></div>
            <h3>Payment Plan Subject:</h3>
            <div id="subject"></div>
            <h3>Payment Plan: <a id="refreshPaymentPlan" href="#">&#8635;</a></h3>
            <iframe id="plan" frameBorder="0"></iframe>
        </td>
        <td width="50%" id="material">
            <h3>Buildings:</h3>
            <div id="buildings">
                <?php foreach(\SWD\Material::get()->buildings->array as $building):
                    if (count($building->resources) > 0 || count($building->resourceChoice) > 0 || count($building->fixedPriceResources) > 0 || count($building->cost) > 0):
                        ?><div id="<?= $building->id ?>" data-id="<?= $building->id ?>"
                             class="item building building_small"
                             style="background-position: -<?= $building->spriteXY[0] ?>00% -<?= $building->spriteXY[1] ?>00%;"
                               title="<?= $building->name ?>"
                        ></div>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
            <h3>Wonders (as if constructed):</h3>
            <div id="wonders">
                <?php foreach(\SWD\Material::get()->wonders->array as $wonder):
                    $spritesheetColumns = 5;
                    $x = ($wonder->id - 1) % $spritesheetColumns;
                    $y = floor(($wonder->id - 1) / $spritesheetColumns);
                    ?><div id="<?= $wonder->id ?>" data-id="<?= $wonder->id ?>"
                         class="item wonder wonder_small"
                         style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                        title="<?= $wonder->name ?>"
                    ></div>
                <?php endforeach ?>
            </div>
            <h3>Progress tokens:</h3>
            <div id="progress_tokens">
                <?php foreach([ProgressToken::get(2), ProgressToken::get(5), ProgressToken::get(15)] as $progressToken):
                    $spritesheetColumns = 4;
                    $x = ($progressToken->id - 1) % $spritesheetColumns;
                    $y = floor(($progressToken->id - 1) / $spritesheetColumns);
                    ?><div id="<?= $progressToken->id ?>" data-id="<?= $progressToken->id ?>"
                           class="item progress_token progress_token_small"
                           style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                           title="<?= $progressToken->name ?>"
                    ></div><?php endforeach ?>
            </div>
            <h3>Decrees (as if controlled):</h3>
            <div id="decrees">
                <?php foreach([Decree::get(5), Decree::get(6), Decree::get(7), Decree::get(8), Decree::get(10), Decree::get(11)] as $decree):
                    $spritesheetColumns = 6;
                    $x = ($decree->id - 1) % $spritesheetColumns;
                    $y = floor(($decree->id - 1) / $spritesheetColumns);
                    ?><div id="<?= $decree->id ?>" data-id="<?= $decree->id ?>"
                           class="item decree decree_small"
                           style="background-position: -<?= $x ?>00% -<?= $y ?>00%;"
                    ></div><?php endforeach ?>
            </div>
            <h3>Mythology tokens:</h3>
            <div id="mythology_tokens">
                <?php foreach([MythologyToken::get(1), MythologyToken::get(3), MythologyToken::get(5), MythologyToken::get(7), MythologyToken::get(9)] as $mythologyToken):
                    ?><div id="<?= $mythologyToken->id ?>" data-id="<?= $mythologyToken->id ?>"
                           class="item mythology_token mythology_token_small mythology_token<?= $mythologyToken->type ?>"
                           style="display:inline-block"
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
    var url = '<?= $url ?>';
    var baseurl = '<?= $baseurl ?>';
    var fileName = '<?= $fileName ?>';
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
            dojo.addClass(currentItem, 'red_border');
            dojo.style( 'actions', 'display', 'inline-block' );
        }
    });
    dojo.query('#buttonToOpponent').on("click", (e) => {
        console.log('buttonToOpponent click');
        dojo.stopEvent(e);
        dojo.place( currentItem, 'opponent' );
        deselect();
        updatePaymentPlan();
    });
    dojo.query('#buttonToMe').on("click", (e) => {
        console.log('buttonToMe click');
        dojo.stopEvent(e);
        dojo.place( currentItem, 'me' );
        deselect();
        updatePaymentPlan();
    });
    dojo.query('#buttonToSubject').on("click", (e) => {
        console.log('buttonToSubject click');
        dojo.stopEvent(e);

        moveToMaterial(dojo.query('#subject>.item')[0]);

        dojo.place( currentItem, 'subject' );
        deselect();
        updatePaymentPlan();
    });
    dojo.query('#buttonToMaterial').on("click", (e) => {
        console.log('buttonToMaterial click');
        dojo.stopEvent(e);
        moveToMaterial(currentItem);
        deselect();
        updatePaymentPlan();
    });
    dojo.query('#refreshPaymentPlan').on("click", (e) => {
        console.log('refreshPaymentPlan click');
        dojo.stopEvent(e);

        dojo.attr('plan', 'src', dojo.attr('plan', 'src'));
    });


    function moveToMaterial(node) {
        var container = null;
        if (node) {
            if (dojo.hasClass(node, 'building')) {
                container = 'buildings';
            }
            if (dojo.hasClass(node, 'wonder')) {
                container = 'wonders';
            }
            if (dojo.hasClass(node, 'progress_token')) {
                container = 'progress_tokens';
            }
            if (dojo.hasClass(node, 'decree')) {
                container = 'decrees';
            }
            if (dojo.hasClass(node, 'mythology_token')) {
                container = 'mythology_tokens';
            }
            dojo.place( node, container);

            // Sort material
            if (1) {
                var list = document.getElementById(container);

                var items = list.childNodes;
                var itemsArr = [];
                for (var i in items) {
                    if (items[i].nodeType == 1) { // get rid of the whitespace text nodes
                        itemsArr.push(items[i]);
                    }
                }

                itemsArr.sort(function(a, b) {
                    return parseInt(a.id) == parseInt(b.id)
                        ? 0
                        : (parseInt(a.id) > parseInt(b.id) ? 1 : -1);
                });

                for (i = 0; i < itemsArr.length; ++i) {
                    list.appendChild(itemsArr[i]);
                }
            }
        }
    }

    function deselect(e) {
        if (currentItem) {
            dojo.removeClass(currentItem, 'red_border');
            currentItem = null;
        }
        dojo.style( 'actions', 'display', 'none' );
    }

    function queryString(obj) {
        var str = [];
        for (var p in obj)
            if (obj.hasOwnProperty(p)) {
                str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
            }
        return str.join("&");
    }

    function updatePaymentPlan() {
        var data = Object.assign(
            getTypeStrings('me'),
            getTypeStrings('opponent'),
            getTypeStrings('subject'),
        );

        dojo.attr('plan', 'src', baseurl + 'test.php?' + queryString(data));
        var scenarioUrl = baseurl + fileName + '?' + queryString(data) + '&name=' + encodeURIComponent(dojo.query('#name')[0].value);
        window.history.pushState('paymenttester', 'Title', scenarioUrl);
    }

    function getIdsString(container, typeClass) {
        var ids = [];
        dojo.query('#' + container + ' .' + typeClass).forEach(function (item) {
            ids.push(dojo.attr(item, 'id'));
        });
        return ids.join(',');
    }
    function getTypeStrings(container) {
        var strings = {};
        ['building', 'wonder', 'progress_token', 'decree', 'mythology_token'].forEach(function (type) {
            strings[container + '_' + type + 's'] = this.getIdsString(container, type);
        });
        return strings;
    }

    function moveIdsToContainer(ids, sourceContainer, targetcontainer) {
        for (let i = 0; i < ids.length; i++) {
            var node = dojo.query('#' + sourceContainer + ' [data-id=' + ids[i] + ']')[0];
            dojo.place( node, targetcontainer );
        }
    }
    <?php if(isset($_GET['name'])): ?>
        moveIdsToContainer([<?= $_GET['me_buildings'] ?>], 'buildings', 'me');
        moveIdsToContainer([<?= $_GET['me_wonders'] ?>], 'wonders', 'me');
        moveIdsToContainer([<?= $_GET['me_progress_tokens'] ?>], 'progress_tokens', 'me');
        moveIdsToContainer([<?= $_GET['me_decrees'] ?>], 'decrees', 'me');
        moveIdsToContainer([<?= $_GET['me_mythology_tokens'] ?>], 'mythology_tokens', 'me');
        moveIdsToContainer([<?= $_GET['opponent_buildings'] ?>], 'buildings', 'opponent');
        moveIdsToContainer([<?= $_GET['opponent_wonders'] ?>], 'wonders', 'opponent');
        moveIdsToContainer([<?= $_GET['opponent_progress_tokens'] ?>], 'progress_tokens', 'opponent');
        moveIdsToContainer([<?= $_GET['opponent_decrees'] ?>], 'decrees', 'opponent');
        moveIdsToContainer([<?= $_GET['opponent_mythology_tokens'] ?>], 'mythology_tokens', 'opponent');
        moveIdsToContainer([<?= $_GET['subject_buildings'] ?>], 'buildings', 'subject');
        moveIdsToContainer([<?= $_GET['subject_wonders'] ?>], 'wonders', 'subject');
        updatePaymentPlan();
    <?php endif ?>
</script>
</html>