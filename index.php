<?php

use Base\{Application, Installer, ViewRender, Workspace};
use Sabberworm\CSS\RuleSet\DeclarationBlock;


require '/home/aharabara/Projects/Experimets/habarnam/vendor/autoload.php';
require __DIR__ .'/vendor/autoload.php';

//$oCssParser = new Sabberworm\CSS\Parser(file_get_contents('styles.css'));
//$oCssDocument = $oCssParser->parse();
//$t = $oCssDocument->getAllSelectors();


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
