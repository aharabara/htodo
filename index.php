<?php

use Base\{Application, Installer, ViewRender, Workspace};

require '/home/aharabara/Projects/Experimets/habarnam/vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';


$workspace = new Workspace('habarnam-todo');
$installer = new Installer($workspace);

$installer->checkCompatibility();

if (!$installer->isInstalled()) {
    $installer->run();
}

/* folder with surfaces.xml and other view files*/
$viewsFolder = './views/';
$currentViewID = 'main';

$render = new ViewRender($viewsFolder);
$workspace = new Workspace('habarnam-chat');

(new Application($workspace, $render->prepare(), 'welcome'))
    ->debug(true)
    ->handle();
