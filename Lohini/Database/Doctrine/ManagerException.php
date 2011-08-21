<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping\MappingException;

/**
 */
class ManagerException
extends \Exception
{
	/**
	 * @param string $type
	 * @return ManagerException
	 */
	public static function unknownType($type)
	{
		return new self("Given type $type is not managed by any of registered EntityManagers or DocumentManagers.");
	}

	/**
	 * @param object $container
	 * @return ManagerException
	 */
	public static function objectIsNotAContainer($container)
	{
		return new self("Given container '".get_class($container)."' is not descendant of 'Lohini\\Database\\Doctrine\\BaseContainer'");
	}

	/**
	 * @param mixed $object
	 * @return ManagerException
	 */
	public static function notAnObject($object)
	{
		return new self('Given type '.gettype($object).' is not object.');
	}

	/**
	 * @param string $className
	 * @param MappingException $exception
	 * @return ManagerException
	 */
	public static function invalidMapping($className, MappingException $exception)
	{
		return new self("Entity of type $className has invalid mapping.", NULL, $exception);
	}
}
