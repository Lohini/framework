<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Package\Doctrine\DI;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * FixtureExtension
 */
class FixtureExtension
extends \Lohini\Config\CompilerExtension
{
	public function loadConfiguration()
	{
		$container=parent::loadConfiguration();

		foreach ($container->parameters['doctrine']['entityManagers'] as $entityManagerName) {
			$prefix=$entityManagerName.'.dataFixtures';

			$container->addDefinition($prefix.'.loader')
				->setClass('Doctrine\Common\DataFixtures\Loader');

			$container->addDefinition($prefix.'.purger')
				->setClass('Doctrine\Common\DataFixtures\Purger\ORMPurger', array('@'.$entityManagerName));

			$container->addDefinition($prefix.'.executor')
				->setClass('Doctrine\Common\DataFixtures\Executor\ORMExecutor', array('@'.$entityManagerName, "@$prefix.purger"));
			}
	}
}
