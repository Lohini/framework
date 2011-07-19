<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\DI;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip ProchĂˇzka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip ProchĂˇzka
 */

/**
 * @property-read \Lohini\Database\Doctrine\Workspace $workspace
 * @property-read \Lohini\Database\Doctrine\ORM\Container $sqldb
 * @property-read \Lohini\Database\Doctrine\ODM\Container $couchdb
 *
 * @property-read Nette\Application\Application $application
 * @property-read Nette\Application\IPresenterFactory $presenterFactory
 */
class Container
extends \Nette\DI\Container
{
	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 * @throws \Nette\OutOfRangeException
	 */
	public function getParam($key, $default=NULL)
	{
		if (isset ($this->params[$key])) {
			return $this->params[$key];
			}
		if (func_num_args()>1) {
			return $default;
			}
		throw new \Nette\OutOfRangeException("Missing key '$key' in ".get_class($this).'->params');
	}

	/**
	 * @param string $name
	 * @param \Nette\DI\IContainer $container
	 */
	public function lazyCopy($name, \Nette\DI\IContainer $container)
	{
		$this->addService($name, function() use ($name, $container) {
					return $container->getService($name);
					});
	}
}
