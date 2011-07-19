<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Loaders;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use Doctrine\Common\ClassLoader;

class DoctrineLoader
{
	/** @var array */
	private static $registered=FALSE;


	/**
	 * @param string|NULL $namespace
	 * @return \BailIff\Loaders\DoctrineLoader
	 */
	public static function register()
	{
		if (self::$registered) {
			throw DoctrineLoaderException::alreadyRegistered();
			}

		require_once LIBS_DIR.'/Doctrine/Common/ClassLoader.php';

		$classLoader=self::$registered[]=new ClassLoader('Doctrine', LIBS_DIR);
		$classLoader->register();
		$classLoader=self::$registered[]=new ClassLoader('Doctrine\DBAL\Migrations', LIBS_DIR.'/Doctrine');
		$classLoader->register();
		$classLoader=self::$registered[]=new ClassLoader('DoctrineExtensions', LIBS_DIR.'/Doctrine');
		$classLoader->register();
		$classLoader=self::$registered[]=new ClassLoader('Gedmo', LIBS_DIR.'/Doctrine');
		$classLoader->register();

		return new self;
	}
}


class DoctrineLoaderException
extends \Exception
{
	/**
	 * @return \BailIff\Loaders\DoctrineLoaderException
	 */
	public static function alreadyRegistered()
	{
		return new self('Cannot register, loader for Doctrine already registered.');
	}
}
