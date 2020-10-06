<?php

use Francerz\GithubWebhook\Handler;

define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config.json');

require_once ROOT_PATH.str_replace('/', DIRECTORY_SEPARATOR, '/vendor/autoload.php');

$handler = new Handler();
$handler->loadConfigFromFile(CONFIG_PATH);
$handler->handle();