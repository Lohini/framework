<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

@header('X-Powered-By: BailIff');

// define basic constants
define('BAILIFF', TRUE);
define('BAILIFF_DIR', __DIR__);
define('BAILIFF_VERSION_ID', 4); //0.0.4
define('BAILIFF_PACKAGE', '5.3');

// check required PHP version
if (!version_compare(phpversion(), BAILIFF_PACKAGE, '>=')) {
	die('This system requires PHP version '.BAILIFF_PACKAGE.' or latter, '.phpversion().' used.');
	}
if (!defined('NETTE')) {
	// check presence of Nette Framework
	if (!is_file(LIBS_DIR.'/Nette/loader.php')) {
		die('Copy Nette Framework to '.LIBS_DIR.' directory.');
		}
	// load Nette Framework
	require_once LIBS_DIR.'/Nette/loader.php';
	}
// check required version of Nette Framework
if (!version_compare(\Nette\Framework::VERSION, '2.0-beta', '>=')) {
	die('This BailIff requires Nette version 2.0-beta or latter, '.\Nette\Framework::VERSION.' used.');
	}
// load BailIff
require_once BAILIFF_DIR.'/Loaders/BailIffLoader.php';
\BailIff\Loaders\BailIffLoader::getInstance()->register();

// Set debug options
\Nette\Diagnostics\Debugger::$strictMode=TRUE;
$configurator=new \BailIff\Configurator;
\Nette\Environment::setConfigurator($configurator);
