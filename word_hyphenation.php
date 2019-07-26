<?php
/*
WORD HYPHENATION PHP CLI
Vaclovas lapinskis
*/

use CLI\App;
use Core\AppContainer;

require_once('vendor/autoload.php');
$container = new AppContainer(array('document_root' => './'));
$container->getConfig()->applyLoggerConfig($container->getLogger());
$app = new App($container->getLogger(), $container->getConfig(), $container->getCache());
$app->start($argc, $argv);
