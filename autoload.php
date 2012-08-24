<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

/** @var \Composer\Autoload\ClassLoader $loader */
$loader=require_once __DIR__.'/vendor/autoload.php';
$loader->add('Lohini\\Testing', __DIR__.'/tests');
$loader->add('Lohini\\Tests', __DIR__.'/tests');
$loader->add('Lohini', __DIR__.'/lib');

@header('X-Powered-By: Lohini');

// Doctrine annotations
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(callback('class_exists'));

unset($loader); // cleanup
