<?php

use Base\{Application, Core\Installer, Core\Workspace, Services\ViewRender};

require __DIR__ . '/vendor/autoload.php';


$workspace = new Workspace('habarnam-todo');
$installer = new Installer($workspace);

$installer->checkCompatibility();

if (!$installer->isInstalled()) {
    $installer->run();
}

/* folder with surfaces.xml and other view files*/
$render = new ViewRender(__DIR__. '/views/');

(new Application($workspace, $render->prepare(), 'welcome'))
    ->debug(true)
    ->handle();
