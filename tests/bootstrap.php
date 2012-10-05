<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
/**
 * @author Filip Procházka <filip@prochazka.su>
 */

// take care of autoloading
// require class loader
/** @var \Composer\Autoload\ClassLoader $loader */
$loader=require_once __DIR__.'/../vendor/autoload.php';
$loader->add('Lohini\\Testing', __DIR__);
$loader->add('Lohini\\Tests', __DIR__);
$loader->add('Lohini', __DIR__.'/../libs');

// Doctrine annotations
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(callback('class_exists'));

// create container
\Lohini\Testing\Configurator::testsInit(__DIR__)
	->getContainer();

unset($loader); // cleanup