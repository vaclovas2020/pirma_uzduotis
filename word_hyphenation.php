<?php
/*
WORD HYPHENATION PHP CLI
Vaclovas lapinskis
*/

use CLI\Main;

require_once('classes/Core/AutoLoader.php');
Core\AutoLoader::register();

/** @noinspection PhpParamsInspection */
Main::main($argc, $argv);