<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Reflection\ClassType;

/**
 */
final class SerializableMixin
{
	/** @var array|ClassType[] */
	private static $classes=array();
	/** @var array|\Nette\Reflection\Property */
	private static $properties=array();


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException("Can't instantiate static class ".get_class($this));
	}

	/**
	 * @param \Serializable $object
	 * @return string
	 */
	public static function serialize(\Serializable $object)
	{
		$data=array();

		$allowed=FALSE;
		if (method_exists($object, '__sleep')) {
			$allowed=(array)$object->__sleep();
			}

		$class=ClassType::from($object);

		do {
			/** @var \Nette\Reflection\Property $propertyRefl */
			foreach ($class->getProperties() as $propertyRefl) {
				if ($allowed!==FALSE && !in_array($propertyRefl->getName(), $allowed)) {
					continue;
					}
				if ($propertyRefl->isStatic()) {
					continue;
					}

				// prefix private properties
				$prefix= $propertyRefl->isPrivate()
					? $propertyRefl->getDeclaringClass()->getName().'::'
					: NULL;

				// save value
				$propertyRefl->setAccessible(TRUE);
				$data[$prefix.$propertyRefl->getName()]=$propertyRefl->getValue($object);
				}
			} while ($class=$class->getParentClass());

		return serialize($data);
	}

	/**
	 * @param \Serializable $object
	 * @param string $serialized
	 */
	public static function unserialize(\Serializable $object, $serialized)
	{
		$data=unserialize($serialized);

		foreach ($data as $target => $value) {
			if (strpos($target, '::')!==FALSE) {
				list($class, $name)=explode('::', $target, 2);
				$propertyRefl=self::getProperty($name, $class);
				}
			else {
				$propertyRefl=self::getProperty($target, $object);
				}

			$propertyRefl->setValue($object, $value);
			}

		if (method_exists($object, '__wakeup')) {
			$object->__wakeup();
			}
	}

	/**
	 * Class reflection cache.
	 *
	 * @param string $name
	 * @param string|object $class
	 * @return \Nette\Reflection\Property
	 */
	private static function getProperty($name, $class)
	{
		$class= is_object($class)? get_class($class) : $class;
		if (isset(self::$properties[$class][$name])) {
			return self::$properties[$class][$name];
			}

		if (!isset(self::$classes[$class])) {
			self::$classes[$class]=ClassType::from($class);
			}

		/** @var \Nette\Reflection\Property $propRefl */
		$propRefl=self::$classes[$class]->getProperty($name);
		$propRefl->setAccessible(TRUE);

		if (!isset(self::$properties[$class])) {
			self::$properties[$class]=array();
			}
		return self::$properties[$class][$name]=$propRefl;
	}
}
