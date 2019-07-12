<?php
/*
WORD HYPHENATION PHP CLI
Vaclovas lapinskis
*/

use AppConfig\Config;
use CLI\App;
use Log\Logger;
use SimpleCache\FileCache;

require_once('classes/Core/AutoLoader.php');
Core\AutoLoader::register();

$logger = new Logger();
$config = new Config($logger);
$config->applyLoggerConfig($logger);
$cache = new FileCache($config->getCachePath(), $config->getCacheDefaultTtl());
$app = new App($logger, $config, $cache);
$app->start($argc, $argv);
