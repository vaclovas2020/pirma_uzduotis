<?php


use Core\AppContainer;

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
$container = new AppContainer(
    array(
        'document_root' => $_SERVER['DOCUMENT_ROOT'] . '/',
        'allowed_api_methods' => array('GET', 'POST', 'PUT', 'DELETE')
    )
);
$container->getApiObject('API\WordsController', 'words');
$container->getApiObject('API\PatternsController', 'patterns');
$container->getRouter()->route();
