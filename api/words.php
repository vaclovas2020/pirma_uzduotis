<?php


use API\ApiRequest;
use AppConfig\Config;
use DB\DbWord;
use Log\Logger;
use SimpleCache\FileCache;


require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/Core/AutoLoader.php');
Core\AutoLoader::register(true);

$logger = new Logger();
$config = new Config($logger, $_SERVER['DOCUMENT_ROOT'] . '/app_config.json');
$config->applyLoggerConfig($logger);

$cache = new FileCache($_SERVER['DOCUMENT_ROOT'] . '/' . $config->getCachePath(), $config->getCacheDefaultTtl());
$request = new ApiRequest($logger, $config, $cache);
$request->processRequest('word', $_SERVER['REQUEST_METHOD']);
