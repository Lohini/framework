<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;
/**
* @author Filip Procházka
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class InvalidPresenterException
extends \Nette\Application\InvalidPresenterException
{
	/**#@+ */
	const INVALID_NAME=1;
	const NOT_IMPLEMENTOR=2;
	const IS_ABSTRACT=3;
	const CASE_MISMATCH=4;
	const NOT_FOUND=5;
	/**#@-*/


	/**
	 * @param string $name
	 * @return InvalidPresenterException
	 */
	public static function invalidName($name)
	{
		return new self("Presenter name must be alphanumeric string, '$name' is invalid.", self::INVALID_NAME);
	}

	/**
	 * @param string $name
	 * @param string $class
	 * @return InvalidPresenterException
	 */
	public static function notImplementor($name, $class)
	{
		return new self("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.", self::NOT_IMPLEMENTOR);
	}

	/**
	 * @param string $name
	 * @param string $class
	 * @return InvalidPresenterException
	 */
	public static function isAbstract($name, $class)
	{
		return new self("Cannot load presenter '$name', class '$class' is abstract.", self::IS_ABSTRACT);
	}

	/**
	 * @param string $name
	 * @param string $realName
	 * @return InvalidPresenterException
	 */
	public static function caseMismatch($name, $realName)
	{
		return new self("Cannot load presenter '$name', case mismatch. Real name is '$realName'.", self::CASE_MISMATCH);
	}

	/**
	 * @param string $name
	 * @param string $class
	 * @return InvalidPresenterException
	 */
	public static function notFound($name, $class)
	{
		return new self("Can't load presenter '$name', class '$class' was not found.", self::NOT_FOUND);
	}
}
