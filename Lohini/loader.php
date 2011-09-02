<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

// define basic constants
define('LOHINI', TRUE);
define('LOHINI_DIR', __DIR__);
define('LOHINI_VERSION_ID', 100); //0.1.0
define('LOHINI_PACKAGE', '5.3');

// check required PHP version
if (!version_compare(phpversion(), LOHINI_PACKAGE, '>=')) {
	die('This system requires PHP version '.LOHINI_PACKAGE.' or latter, '.phpversion().' used.');
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
	die('This Lohini requires Nette version 2.0-beta or latter, '.\Nette\Framework::VERSION.' used.');
	}

@header('X-Powered-By: Lohini');

// load Lohini
require_once LOHINI_DIR.'/Loaders/SplClassLoader.php';
\Lohini\Loaders\SplClassLoader::getInstance(array(
	'Lohini' => LOHINI_DIR,
	'Doctrine' => LIBS_DIR.'/Doctrine',
	'DoctrineExtensions' => LIBS_DIR.'/Doctrine/DoctrineExtensions',
	'Gedmo' => LIBS_DIR.'/Doctrine/Gedmo',
	'Symfony' => LIBS_DIR.'/Symfony'
	))->register();
// Set debug options
\Nette\Diagnostics\Debugger::$strictMode=TRUE;
require_once __DIR__.'/Localization/shortcuts.php';
