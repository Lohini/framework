<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

// take care of autoloading
require_once __DIR__ . '/../autoload.php';

// create container
\LohiniTesting\Configurator::testsInit(__DIR__)
	->getContainer();
