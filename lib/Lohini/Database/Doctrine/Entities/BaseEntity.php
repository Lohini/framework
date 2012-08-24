<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Entities;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\Common\Collections\Collection,
	Doctrine\ORM\Mapping as ORM,
	Lohini\Utils\SerializableMixin;

/**
 * @ORM\MappedSuperclass()
 *
 * @property-read int $id
 */
abstract class BaseEntity
extends \Nette\Object
implements \Serializable
{
	/** @var array */
	private static $properties=array();
	/** @var array */
	private static $methods=array();


	/**
	 */
	public function __construct() { }

	/**
	 * Allows the user to access through magic methods to protected and public properties.
	 * There are get<name>() and set<name>($value) methods for every protected or public property,
	 * and for protected or public collections there are add<name>($entity), remove<name>($entity) and has<name>($entity).
	 * When you'll try to call setter on collection, or collection manipulator on generic value, it will throw.
	 * Getters on collections will return all it's items.
	 *
	 * @param string $name method name
	 * @param array $args arguments
	 * @return mixed
	 * @throws \Lohini\UnexpectedValueException
	 * @throws \Lohini\MemberAccessException
	 */
	public function __call($name, $args)
	{
		if (strlen($name)>3) {
			$properties=$this->listObjectProperties();

			$op=substr($name, 0, 3);
			$prop=strtolower($name[3]).substr($name, 4);
			if ($op==='set' && isset($properties[$prop])) {
				if ($this->$prop instanceof Collection) {
					throw \Lohini\UnexpectedValueException::collectionCannotBeReplaced($this, $prop);
					}

				$this->$prop=$args[0];
				return $this;
				}
			if ($op==='get' && isset($properties[$prop])) {
				if ($this->$prop instanceof Collection) {
					return $this->$prop->toArray();
					}
				return $this->$prop;
				}
			// collections
			if ($op==='add') {
				if (isset($properties[$prop.'s'])) {
					if (!$this->{$prop.'s'} instanceof Collection) {
						throw \Lohini\UnexpectedValueException::notACollection($this, $prop.'s');
						}

					$this->{$prop.'s'}->add($args[0]);
					return $this;
					}
				if (substr($prop, -1)==='y' && isset($properties[$prop=substr($prop, 0, -1).'ies'])) {
					if (!$this->$prop instanceof Collection) {
						throw \Lohini\UnexpectedValueException::notACollection($this, $prop);
						}

					$this->$prop->add($args[0]);
					return $this;
					}
				if (isset($properties[$prop])) {
					throw \Lohini\UnexpectedValueException::notACollection($this, $prop);
					}
				}
			elseif ($op==='has') {
				if (isset($properties[$prop.'s'])) {
					if (!$this->{$prop.'s'} instanceof Collection) {
						throw \Lohini\UnexpectedValueException::notACollection($this, $prop.'s');
						}

					return $this->{$prop.'s'}->contains($args[0]);
					}
				if (substr($prop, -1)==='y' && isset($properties[$prop=substr($prop, 0, -1).'ies'])) {
					if (!$this->$prop instanceof Collection) {
						throw \Lohini\UnexpectedValueException::notACollection($this, $prop);
						}

					return $this->$prop->contains($args[0]);
					}
				if (isset($properties[$prop])) {
					throw \Lohini\UnexpectedValueException::notACollection($this, $prop);
					}
				}
			elseif (strlen($name)>6 && ($op=substr($name, 0, 6))==='remove') {
				$prop=strtolower($name[6]).substr($name, 7);

				if (isset($properties[$prop.'s'])) {
					if (!$this->{$prop.'s'} instanceof Collection) {
						throw \Lohini\UnexpectedValueException::notACollection($this, $prop.'s');
						}

					$this->{$prop.'s'}->removeElement($args[0]);
					return $this;
					}
				if (substr($prop, -1)==='y' && isset($properties[$prop=substr($prop, 0, -1).'ies'])) {
					if (!$this->$prop instanceof Collection) {
						throw \Lohini\UnexpectedValueException::notACollection($this, $prop);
						}

					$this->$prop->removeElement($args[0]);
					return $this;
					}
				if (isset($properties[$prop])) {
					throw \Lohini\UnexpectedValueException::notACollection($this, $prop);
					}
				}
			}

		if ($name==='') {
			throw \Lohini\MemberAccessException::callWithoutName($this);
			}
		$class=get_class($this);

		// event functionality
		if (preg_match('#^on[A-Z]#', $name) && property_exists($class, $name)) {
			$rp=new \ReflectionProperty($this, $name);
			if ($rp->isPublic() && !$rp->isStatic()) {
				if (is_array($list=$this->$name) || $list instanceof \Traversable) {
					foreach ($list as $handler) {
						callback($handler)->invokeArgs($args);
						}
					}
				elseif ($list!==NULL) {
					throw \Lohini\UnexpectedValueException::invalidEventValue($list, $this, $name);
					}
				return NULL;
				}
			}

		// extension methods
		if ($cb=static::extensionMethod($name)) {
			/** @var \Nette\Callback $cb */
			array_unshift($args, $this);
			return $cb->invokeArgs($args);
			}

		throw \Lohini\MemberAccessException::undefinedMethodCall($this, $name);
	}

	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param string $name property name
	 * @return mixed property value
	 * @throws \Lohini\MemberAccessException if the property is not defined.
	 */
	public function &__get($name)
	{
		if ($name==='') {
			throw \Lohini\MemberAccessException::propertyReadWithoutName($this);
			}

		// property getter support
		$name[0]=$name[0] & "\xDF"; // case-sensitive checking, capitalize first character
		$m='get'.$name;

		$methods=$this->listObjectMethods();
		if (isset($methods[$m])) {
			// ampersands:
			// - uses &__get() because declaration should be forward compatible (e.g. with Nette\Utils\Html)
			// - doesn't call &$_this->$m because user could bypass property setter by: $x=& $obj->property; $x='new value';
			$val=$this->$m();
			return $val;
			}

		$m='is'.$name;
		if (isset($methods[$m])) {
			$val=$this->$m();
			return $val;
			}

		// protected attribute support
		$properties=$this->listObjectProperties();
		if (isset($properties[$name=func_get_arg(0)])) {
			if ($this->$name instanceof Collection) {
				$coll=$this->$name->toArray();
				return $coll;
				}
			else {
				$val=$this->$name;
				return $val;
				}
			}

		$type= isset($methods['set'.$name])? 'a write-only' : 'an undeclared';
		throw \Lohini\MemberAccessException::propertyNotReadable($type, $this, func_get_arg(0));
	}

	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param string $name property name
	 * @param mixed $value property value
	 * @throws \Lohini\MemberAccessException if the property is not defined or is read-only
	 * @throws \Lohini\UnexpectedValueException
	 */
	public function __set($name, $value)
	{
		if ($name==='') {
			throw \Lohini\MemberAccessException::propertyWriteWithoutName($this);
			}

		// property setter support
		$name[0]=$name[0] & "\xDF"; // case-sensitive checking, capitalize first character

		$methods=$this->listObjectMethods();
		$m='set'.$name;
		if (isset($methods[$m])) {
			$this->$m($value);
			return;
			}

		// protected attribute support
		$properties=$this->listObjectProperties();
		if (isset($properties[$name=func_get_arg(0)])) {
			if ($this->$name instanceof Collection) {
				throw \Lohini\UnexpectedValueException::collectionCannotBeReplaced($this, $name);
				}

			$this->$name=$value;
			return;
			}

		$type=isset($methods['get'.$name]) || isset($methods['is'.$name])? 'a read-only' : 'an undeclared';
		throw \Lohini\MemberAccessException::propertyNotWritable($type, $this, func_get_arg(0));
	}

	/**
	 * Is property defined?
	 *
	 * @param string $name property name
	 * @return bool
	 */
	public function __isset($name)
	{
		$properties=$this->listObjectProperties();
		if (isset($properties[$name])) {
			return TRUE;
			}

		if ($name==='') {
			return FALSE;
			}

		$methods=$this->listObjectMethods();
		$name[0]=$name[0] & "\xDF";
		return isset($methods['get'.$name]) || isset($methods['is'.$name]);
	}

	/**
	 * Should return only public or protected properties of class
	 *
	 * @return array
	 */
	private function listObjectProperties()
	{
		$class=get_class($this);
		if (!isset(self::$properties[$class])) {
			self::$properties[$class]=array_flip(array_keys(get_object_vars($this)));
			}

		return self::$properties[$class];
	}

	/**
	 * Should return all public methods of class
	 *
	 * @return array
	 */
	private function listObjectMethods()
	{
		$class=get_class($this);
		if (!isset(self::$methods[$class])) {
			// get_class_methods returns ONLY PUBLIC methods of objects
			// but returns static methods too (nothing doing...)
			// and is much faster than reflection
			// (works good since 5.0.4)
			self::$methods[$class]=array_flip(get_class_methods($class));
			}

		return self::$methods[$class];
	}

	/**************************** \Serializable ****************************/
	/**
	 * @internal
	 * @return string
	 */
	public function serialize()
	{
		return SerializableMixin::serialize($this);
	}

	/**
	 * @internal
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		SerializableMixin::unserialize($this, $serialized);
	}
}
