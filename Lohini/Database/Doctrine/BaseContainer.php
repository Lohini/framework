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
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\DI\Container;

/**
 * @author Filip Procházka
 *
 * @property-read Cache $cache
 */
abstract class BaseContainer
extends Container
{
	/**
	 * Registers doctrine types
	 *
	 * @param \Lohini\DI\Container $context
	 * @param array $parameters
	 */
	public function __construct(Container $context, $parameters=array())
	{
		$this->addService('context', $context);
		$this->addService('cache', $context->doctrineCache);
		$this->params=(array)$parameters+$this->params;

		array_walk_recursive($this->params, function(&$value) use ($context) {
			$value=$context->expand($value);
			});
/*
		foreach (get_class_methods(get_called_class()) as $method) {
			if (\Nette\Utils\Strings::startsWith($method, 'createService')) {
				$name=strtolower(substr($method, 13, 1)).substr($method, 14);
				if (!$context->hasService($name)) {
					$context->addService($name, callback(get_called_class(), $method));
					}
				}
			}
*/
	}


	/**
	 * @param string $className
	 * @return bool
	 */
	abstract public function isManaging($className);

	/**
	 * @param string $className
	 * @return \Doctrine\ORM\EntityRepository|\Doctrine\ODM\CouchDB\DocumentRepository
	 */
	abstract public function getRepository($className);
}
