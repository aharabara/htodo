<?php

use Base\{Application, Installer, ViewRender, Workspace};
setlocale(LC_ALL, '');

require '/home/aharabara/Projects/Experimets/habarnam/vendor/autoload.php';
require __DIR__ .'/vendor/autoload.php';



$workspace = new Workspace('habarnam-todo');
$installer = new Installer($workspace);

$installer->checkCompatibility();

if (!$installer->isInstalled()){
    $installer->run();
}

$render = new ViewRender(__DIR__.'/views/');
(new Application($workspace, $render->prepare(), 'welcome'))
    ->debug(true)
    ->handle();
