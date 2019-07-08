<?php
/*
WORD HYPHENATION PHP CLI
Vaclovas lapinskis
*/

use CLI\Main;

require_once('classes/Core/AutoLoader.php');
Core\AutoLoader::register();


(new Main())->main($argc, $argv);