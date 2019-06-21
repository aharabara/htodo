<?php

use Base\Application;

chdir(__DIR__);
require '../habarnam/vendor/autoload.php';
require './vendor/autoload.php';

Application::boot(true);