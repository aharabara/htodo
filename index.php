<?php

use Base\{Application, ViewRender};

chdir(__DIR__);
require './vendor/autoload.php';

$render = (new ViewRender('./views/'))->prepare();
(new Application($render, 'welcome'))
    ->debug(true)
    ->handle();
