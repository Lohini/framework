<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Config;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class TaggedServices
extends \Nette\Object
implements \Countable, \Iterator
{
	/** @var \Nette\DI\Container|\SystemContainer */
	private $container;
	/** @var string */
	private $tag;
	/** @var array */
	private $services=array();
	/** @var array */
	private $meta=array();


	/**
	 * @param string $tag
	 * @param \Nette\DI\Container $container
	 */
	public function __construct($tag, \Nette\DI\Container $container)
	{
		$this->container=$container;
		$this->tag=$tag;

		$this->meta=$this->container->findByTag($this->tag);
		$this->services=array_keys($this->meta);
	}

	/**
	 * @param mixed $meta
	 * @return array|object[]
	 */
	public function findByMeta($meta)
	{
		$container=$this->container;
		return array_map(
			function($name) use ($container) {
				/** @var \Nette\DI\Container $container */
				return $container->getService($name);
				},
			array_keys(array_filter($this->meta, function($current) use ($meta) {
				return $current===$meta;
				}))
			);
	}

	/**
	 * @param mixed $meta
	 * @return object|NULL
	 */
	public function findOneByMeta($meta)
	{
		if (!$id=array_search($meta, $this->meta, TRUE)) {
			return NULL;
			}

		return $this->container->getService($id);
	}

	/**
	 * @param mixed $meta
	 * @return object|NULL
	 */
	public function createOneByMeta($meta)
	{
		if (!$id=array_search($meta, $this->meta, TRUE)) {
			return NULL;
			}

		$method=\Nette\DI\Container::getMethodName($id, FALSE);
		if (method_exists($this->container, $method)) {
			return $this->container->$method();
			}
		$factory=$this->container->getService($id);
		return callback($factory)->invoke();
	}

	/************************* \Countable *************************/
	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->meta);
	}

	/************************* \Iterator *************************/
	/**
	 * @return bool|object
	 */
	public function current()
	{
		if ($name=current($this->services)) {
			return $this->container->getService($name);
			}

		return FALSE;
	}

	/**
	 * @return bool|object
	 */
	public function next()
	{
		next($this->services);
		return $this->current();
	}

	/**
	 * @return mixed
	 */
	public function key()
	{
		return key($this->services);
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		$key=key($this->services);
		return ($key!==NULL && $key!==FALSE);
	}

	public function rewind()
	{
		reset($this->services);
	}
}
