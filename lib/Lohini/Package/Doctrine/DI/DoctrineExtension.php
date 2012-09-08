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
 * OrmExtension is an extension for the Doctrine ORM library.
 */
class DoctrineExtension
extends \Lohini\Config\CompilerExtension
{
	public function beforeCompile()
	{
		$this->registerEventSubscribers($this->getContainerBuilder());
	}

	/**
	 * @param \Nette\DI\ContainerBuilder $builder
	 */
	protected function registerEventSubscribers(\Nette\DI\ContainerBuilder $builder)
	{
		$connectionIds=array_keys($builder->parameters['doctrine']['connections']);

		foreach ($builder->findByTag('doctrine.eventSubscriber') as $listener => $meta) {
			if (isset($meta['connection'])) {
				$this->registerEventSubscriber($meta['connection'], $listener);
				}
			elseif (isset($meta['connections'])) {
				foreach ($meta['connections'] as $id) {
					$this->registerEventSubscriber($id, $listener);
					}
				}
			else {
				foreach ($connectionIds as $id) {
					$this->registerEventSubscriber($id, $listener);
					}
				}
			}
	}

	/**
	 * @param string $connectionName
	 * @param string $listener
	 */
	protected function registerEventSubscriber($connectionName, $listener)
	{
		$this->getContainerBuilder()->getDefinition($listener)
			->addTag('doctrine.eventSubscriber.'.$connectionName);
	}
}
