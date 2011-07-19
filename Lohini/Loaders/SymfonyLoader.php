<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Loaders;
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


class SymfonyLoader
{
	/** @var array */
	private static $registered=FALSE;


	/**
	 * @param string|NULL $namespace
	 * @return \Lohini\Loaders\SymfonyLoader
	 */
	public static function register()
	{
		if (self::$registered) {
			throw SymfonyLoaderException::alreadyRegistered();
			}

		require_once LIBS_DIR.'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

		$symfonyLoader=self::$registered[]=new Symfony\Component\ClassLoader\UniversalClassLoader();
		$symfonyLoader->registerNamespaces(array(
			'Symfony' => LIBS_DIR,
			));
		$symfonyLoader->register();

		return new self;
	}
}


class SymfonyLoaderException
extends \Exception
{
	/**
	 * @return \Lohini\Loaders\SymfonyLoaderException
	 */
	public static function alreadyRegistered()
	{
		return new self('Cannot register, loader for Symfony already registered');
	}
}
