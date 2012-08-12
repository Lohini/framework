<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

use Doctrine\Common\Annotations\AnnotationRegistry,
	Symfony\Component\ClassLoader\UniversalClassLoader;


// require class loader
require_once __DIR__.'/vendor/autoload.php';


// library
$loader=new UniversalClassLoader;
$loader->registerNamespaces(array(
	'Lohini' => __DIR__ . '/lib',
	'LohiniTesting' => __DIR__.'/tests'
	));
$loader->register();

@header('X-Powered-By: Lohini');

// exceptions
$exceptions=new \Lohini\Loaders\ExceptionsLoader;
$exceptions->register();

// Doctrine annotations
AnnotationRegistry::registerLoader(function($class) use ($loader) {
	$loader->loadClass($class);
	return class_exists($class, FALSE);
	});
AnnotationRegistry::registerFile(__DIR__.'/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

unset($loader, $exceptions); // cleanup
