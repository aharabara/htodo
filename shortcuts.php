<?php

use App\TaskController;
use Base\Application;
use Illuminate\Container\Container;

$container = Container::getInstance();

/** @var Application $app */
$app = $container->make(Application::class);

$app->on(Application::EVENT_KEYPRESS.'.'.NCURSES_KEY_F5, [TaskController::class, 'addItem']);