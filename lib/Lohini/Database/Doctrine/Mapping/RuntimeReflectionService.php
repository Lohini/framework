<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Mapping;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Reflection;

/**
 */
class RuntimeReflectionService
extends \Doctrine\Common\Persistence\Mapping\RuntimeReflectionService
{
	/**
	 * Return a reflection class instance or null
	 *
	 * @param string $class
	 * @return Reflection\ClassType
	 */
	public function getClass($class)
	{
		return new Reflection\ClassType($class);
	}

	/**
	 * Return an accessible property (setAccessible(true)) or NULL.
	 *
	 * @param string $class
	 * @param string $property
	 * @return Reflection\Property
	 */
	public function getAccessibleProperty($class, $property)
	{
		$property=new Reflection\Property($class, $property);
		$property->setAccessible(TRUE);
		return $property;
	}
}
