<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini;

/**
* The exception that is thrown when writing to a directory that is not writable.
*/
class DirectoryNotWritableException
extends \Nette\IOException
{
	/**
	 * @param string $directory
	 * @return DirectoryNotWritableException
	 */
	public static function fromDir($directory)
	{
		return new static("Unable to write to directory '$directory'. Please, make it writable.");
	}
}


/**
* The exception that is thrown when writing to a file that is not writable.
*/
class FileNotWritableException
extends \Nette\IOException
{
	/**
	 * @param string $file
	 * @return FileNotWritableException
	 */
	public static function fromFile($file)
	{
		return new static("Unable to write to file '$file'. Please, make it writable.");
	}
}


/**
* The exception that is thrown when accessing a file that does not exist on disk.
*/
class FileNotFoundException
extends \Nette\FileNotFoundException
{
	/**
	 * @param string $file
	 * @return FileNotFoundException
	 */
	public static function fromFile($file)
	{
		return new static("Unable to read file '$file'. Please, make it readable.");
	}
}


/**
 * The exception that is thrown when accessing a class member (property or method) fails.
 */
class MemberAccessException
extends \Nette\MemberAccessException
{
	/**
	 * @param string $type
	 * @param string|object $class
	 * @param string $property
	 * @return MemberAccessException
	 */
	public static function propertyNotWritable($type, $class, $property)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Cannot write to $type property $class::\$$property.");
	}

	/**
	 * @param string|object $class
	 * @return MemberAccessException
	 */
	public static function propertyWriteWithoutName($class)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Cannot write to a class '$class' property without name.");
	}

	/**
	 * @param string $type
	 * @param string|object $class
	 * @param string $property
	 * @return MemberAccessException
	 */
	public static function propertyNotReadable($type, $class, $property)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Cannot read $type property $class::\$$property.");
	}

	/**
	 * @param string|object $class
	 * @return MemberAccessException
	 */
	public static function propertyReadWithoutName($class)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Cannot read a class '$class' property without name.");
	}

	/**
	 * @param string|object $class
	 *
	 * @return MemberAccessException
	 */
	public static function callWithoutName($class)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Call to class '$class' method without name.");
	}

	/**
	 * @param object|string $class
	 * @param string $method
	 * @return MemberAccessException
	 */
	public static function undefinedMethodCall($class, $method)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Call to undefined method $class::$method().");
	}
}


/**
 * The exception that is thrown when a value (typically returned by function) does not match with the expected value.
 */
class UnexpectedValueException
extends \Nette\UnexpectedValueException
{
	/**
	 * @param mixed $list
	 * @param string|object $class
	 * @param string $property
	 * @return UnexpectedValueException
	 */
	public static function invalidEventValue($list, $class, $property)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Property $class::$$property must be array or NULL, ".gettype($list).' given.');
	}

	/**
	 * @param string|object $class
	 * @param string $property
	 * @return UnexpectedValueException
	 */
	public static function notACollection($class, $property)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Class property $class::\$$property is not an instance of Doctrine\\Common\\Collections\\Collection.");
	}

	/**
	 * @param string|object $class
	 * @param string $property
	 * @return UnexpectedValueException
	 */
	public static function collectionCannotBeReplaced($class, $property)
	{
		$class= is_object($class)? get_class($class) : $class;
		return new static("Class property $class::\$$property is an instance of Doctrine\\Common\\Collections\\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.");
	}
}
