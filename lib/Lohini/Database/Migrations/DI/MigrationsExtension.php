<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\DI;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class MigrationsExtension
extends \Lohini\Config\CompilerExtension
{
	public function loadConfiguration()
	{
		$container=$this->getContainerBuilder();

		$container->addDefinition($this->prefix('manager'))
			->setClass(
				'Lohini\Database\Migrations\MigrationsManager',
				array('@doctrine.registry', '@lohini.packageManager')
				);

		$container->addDefinition($this->prefix('console.helper.migrationsManager'))
			->setClass('Lohini\Database\Migrations\Console\MigrationsManagerHelper', array($this->prefix('@manager')))
			->addTag('console.helper', array('alias' => 'mm'));
	}
}
