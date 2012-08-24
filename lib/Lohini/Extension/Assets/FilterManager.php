<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class FilterManager
extends \Assetic\FilterManager
{
	/** @var \SystemContainer|\Nette\DI\Container */
	protected $container;
	/** @var array */
	protected $filterIds=array();


	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(\Nette\DI\Container $container)
	{
		$this->container=$container;
	}

	/**
	 * @param string $serviceId
	 * @param string $filterName
	 */
	public function registerFilterService($serviceId, $filterName)
	{
		$this->filterIds[$filterName]=$serviceId;
	}

	/**
	 * @param string $name
	 * @return \Assetic\Filter\FilterInterface
	 */
	public function get($name)
	{
		if (!isset($this->filterIds[$name])) {
			return parent::get($name);
			}

		return $this->container->getService($this->filterIds[$name]);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function has($name)
	{
		return isset($this->filterIds[$name]) || parent::has($name);
	}

	/**
	 * @return array
	 */
	public function getNames()
	{
		return array_unique(array_merge(array_keys($this->filterIds), parent::getNames()));
	}
}
