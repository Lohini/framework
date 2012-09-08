<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Package\Doctrine\DI;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * AnnotationExtension
 */
class AnnotationExtension
extends \Lohini\Config\CompilerExtension
{
	/**
	 * @param \Nette\Utils\PhpGenerator\ClassType $class 
	 */
	public function afterCompile(\Nette\Utils\PhpGenerator\ClassType $class)
	{
		/** @var \Nette\Utils\PhpGenerator\Method $init */
		$init=$class->methods['initialize'];

		// just look it up, mother fucker!
		$init->addBody('Doctrine\Common\Annotations\AnnotationRegistry::registerLoader("class_exists");');
	}
}
