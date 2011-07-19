<?php //vim: ts=4 sw=4 ai:
use Nette\Diagnostics\Debugger;

// required constants
define('APP_DIR', __DIR__);
define('TESTS_DIR', __DIR__);
define('ROOT_DIR', realpath(__DIR__.'/..'));
define('LIBS_DIR', ROOT_DIR);
define('TEMP_DIR', TESTS_DIR.'/_temp');

// Take care of autoloading
require_once LIBS_DIR.'/Lohini/loader.php';

// Setup Nette debuger
Debugger::enable(Debugger::DEVELOPMENT);
Debugger::$logDirectory=APP_DIR;
Debugger::$maxLen=4096;

// Init Nette Framework robot loader
$loader=new \Nette\Loaders\RobotLoader;
$loader->setCacheStorage(new \Nette\Caching\Storages\MemoryStorage);
$loader->addDirectory(LIBS_DIR);
$loader->addDirectory(APP_DIR);
$loader->register();
