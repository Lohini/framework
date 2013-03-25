<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Package\Framework;
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
 */
class Package
extends \Lohini\Packages\Package
{
	public function __construct()
	{
		$this->name='Framework';
	}

	/**
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 * @param \Lohini\Packages\PackagesContainer $packages
	 */
	public function compile(\Nette\Config\Configurator $config, \Nette\Config\Compiler $compiler, \Lohini\Packages\PackagesContainer $packages)
	{
		$compiler->addExtension('lohini', new DI\FrameworkExtension);
		$compiler->addExtension('migrations', new \Lohini\Database\Migrations\DI\MigrationsExtension);
	}

	/**
	 * @return array
	 */
	public function getEntityNamespaces()
	{
		return array_merge(
				parent::getEntityNamespaces(),
				array(
					'Lohini\\Security',
					'Lohini\\Database\\Doctrine\\Entities',
					'Lohini\\Database\\Doctrine\\Audit',
					'Lohini\\Domain',
					'Lohini\\Media',
					'Lohini\\Templating'
					));
	}

	/**
	 * @param \Symfony\Component\Console\Application $app
	 */
	public function registerCommands(\Symfony\Component\Console\Application $app)
	{
		parent::registerCommands($app);

		$app->addCommands(array(
			// cache
			new \Lohini\Console\Command\CacheCommand,

			// Migrations commands
			new \Lohini\Database\Migrations\Console\GenerateCommand,
			new \Lohini\Database\Migrations\Console\MigrateCommand
			));
	}
}
