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

/**
 */
final class Objects
extends \Nette\Object
{
	/**
	 * Static class - cannot be instantiated.
	 *
	 * @throws \Nette\StaticClassException
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException("Can't instantiate static class ".get_class($this));
	}

	/**
	 * Expands 'path.to.property' in string.
	 *
	 * @param string|array $path
	 * @param object|array $entity
	 * @param bool $need
	 * @return mixed
	 */
	public static function expand($path, $entity, $need=TRUE)
	{
		$value=$entity;
		foreach (is_array($path)? $path : explode('.', $path) as $part) {
			$value=self::getProperty($value, $part, $need);
			if ($value===NULL) {
				break;
				}
			}
		return $value;
	}

	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param bool $need
	 * @return mixed|NULL
	 * @throws \Lohini\MemberAccessException
	 */
	public static function getProperty($object, $propertyName, $need=TRUE)
	{
		if (is_array($object) || $object instanceof \ArrayAccess || $object instanceof \ArrayObject) {
			return $object[$propertyName];
			}
		if (is_object($object)) {
			if (method_exists($object, $method='get'.ucfirst($propertyName))) {
				return $object->$method();
				}
			if (property_exists($object, $propertyName)) {
				return $object->$propertyName;
				}
			if (method_exists($object, $method='is'.ucfirst($propertyName))) {
				return $object->$method();
				}
			}

		if ($need) {
			throw new \Lohini\MemberAccessException('Given'.(is_object($object)? ' entity '.get_class($object) : ' array')." has no public parameter or accesor named '$propertyName', or doesn't exists.");
			}
	}

	/**
	 * @param object $object
	 * @param array $options
	 * @param boolean $exceptionOnInvalid
	 * @throws \Nette\InvalidArgumentException
	 */
	public static function setProperties($object, array $options, $exceptionOnInvalid=TRUE)
	{
		if (!is_object($object)) {
			throw new \Nette\InvalidArgumentException('Can by applied only to objects.');
			}

		foreach	($options as $name => $value) {
			self::setProperty($object, $name, $value, $exceptionOnInvalid);
			}
	}

	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param mixed $value
	 * @param boolean $exceptionOnInvalid
	 * @throws \Lohini\MemberAccessException
	 */
	public static function setProperty($object, $propertyName, $value, $exceptionOnInvalid = TRUE)
	{
		if (property_exists($object, $propertyName)) {
			$object->$propertyName=$value;
			}
		elseif (method_exists($object, $method='set'.ucfirst($propertyName))) {
			$object->$method($value);
			}
		elseif (method_exists($object, $method='add'.ucfirst($propertyName))) {
			$object->$method($value);
			}
		elseif ($exceptionOnInvalid) {
			throw new \Lohini\MemberAccessException("Property with name '$propertyName' is not publicly writable, or doesn't exists.");
		}
	}
}
