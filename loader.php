<?php // vim: set ts=4 sw=4 ai:
/**
 * BailIff (version 0.0.1-dev $WCREV$ released on $WCDATE$)
 *
 * @copyright Copyright (c) 2010 Pavol Hluchy (Lopo)
 */

// define basic constants
define('BAILIFF', TRUE);
define('BAILIFF_DIR', __DIR__);
define('BAILIFF_VERSION_ID', 1); //0.0.1
define('BAILIFF_PACKAGE', '5.3');
// check required PHP version
if (!version_compare(phpversion(), BAILIFF_PACKAGE, ">="))
	die('This system requires PHP version '.BAILIFF_PACKAGE.' or greater, '.phpversion().' used.');
// check presence of Nette Framework
if (!is_file(LIBS_DIR.'/Nette/loader.php'))
	die('Copy Nette Framework to /libs/ directory.');
// load Nette Framework
require_once LIBS_DIR.'/Nette/loader.php';
// check required version of Nette Framework
if (!version_compare(\Nette\Framework::VERSION, "2.0-dev", ">="))
	die('This BailIff requires Nette version 2.0-dev or newer, '.\Nette\Framework::VERSION.' used.');
// load BailIff
require_once __DIR__.'/Environment.php';

@header('X-Powered-By: Nette Framework with BailIff');

