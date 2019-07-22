<?php

use API\ApiObject;
use API\ApiRequest;
use API\ApiRouter;
use API\PatternsController;
use API\WordsController;
use AppConfig\Config;
use Log\Logger;
use SimpleCache\FileCache;

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/Core/AutoLoader.php');
Core\AutoLoader::register(true);
$logger = new Logger();
$config = new Config($logger, $_SERVER['DOCUMENT_ROOT'] . '/app_config.json');

$cache = new FileCache($_SERVER['DOCUMENT_ROOT'] . '/' . $config->getCachePath(),
    $config->getCacheDefaultTtl());
$router = new ApiRouter(new ApiRequest(), array('GET', 'POST', 'PUT', 'DELETE'));
$wordsController = new WordsController($logger, $cache, $config, $router->getResponse());
$patternsController = new PatternsController($logger, $cache, $config, $router->getResponse());
$apiWords = new ApiObject('words', $wordsController, $router);
$apiPatterns = new ApiObject('patterns', $patternsController, $router);
$router->route();
