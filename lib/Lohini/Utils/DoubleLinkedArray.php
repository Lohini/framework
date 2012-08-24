<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class DoubleLinkedArray
extends \Nette\Object
implements \IteratorAggregate, \Countable, \ArrayAccess
{
	/** @var \SplObjectStorage */
	private $links=array();
	/** @var array */
	private $map=array();


	/**
	 * @param array|object[] $siblings
	 */
	public function __construct(array $siblings=array())
	{
		foreach ($siblings as $key => $object) {
			$this->insert($key, $object);
			}
	}

	/**
	 * @param string $key
	 * @param object $object
	 * @throws \Nette\InvalidArgumentException
	 */
	public function insert($key, $object)
	{
		if (isset($this->map[$key])) {
			throw new \Nette\InvalidArgumentException('Given key already exists.');
			}
		if (!is_object($object) || array_search($object, $this->map, TRUE)!==FALSE) {
			throw new \Nette\InvalidArgumentException('Given object already in array.');
			}

		$this->map[$key]=$object;
		ksort($this->map);
		$this->links[$key]=$this->getUncachedKeyLinks($key);

		if ($next=$this->links[$key]['next']) {
			$this->links[$next]=$this->getUncachedKeyLinks($next);
			}
		if ($prev=$this->links[$key]['prev']) {
			$this->links[$prev]=$this->getUncachedKeyLinks($prev);
			}
	}

	/**
	 * @param object $object
	 * @throws \Nette\InvalidArgumentException
	 */
	public function remove($object)
	{
		if (($key=array_search($object, $this->map, TRUE))===FALSE) {
			throw new \Nette\InvalidArgumentException('Given object not found.');
			}

		$siblings=$this->links[$key];
		unset($this->map[$key]);
		unset($this->links[$key]);

		if ($next=$siblings['next']) {
			$this->links[$next]=$this->getUncachedKeyLinks($next);
			}
		if ($prev=$siblings['prev']) {
			$this->links[$prev]=$this->getUncachedKeyLinks($prev);
			}
	}

	/**
	 * @param string $key
	 * @return array
	 * @throws \Nette\OutOfRangeException
	 */
	public function getKeyLinks($key)
	{
		if (!isset($this->links[$key])) {
			throw new \Nette\OutOfRangeException("Undefined key '$key'.");
			}

		return $this->links[$key];
	}

	/**
	 * @param string $needle
	 * @return array
	 */
	private function getUncachedKeyLinks($needle)
	{
		$next= $current= $prev= NULL;
		foreach ($this->map as $key => $value) {
			if ($current) {
				$next=$key;
				break;
				}

			if ($key===$needle) {
				$current=$key;
				continue;
				}

			$prev=$key;
			}

		return array(
			'next' => $next,
			'prev' => $prev,
			);
	}

	/**
	 * @param object $object
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getOffset($object)
	{
		if (!$key=array_search($object, $this->map, FALSE)) {
			throw new \Nette\InvalidArgumentException('Given object not found.');
			}

		return $key;
	}

	/**
	 * @return object
	 */
	public function getLast()
	{
		return end($this->map);
	}

	/**
	 * @return object
	 */
	public function getFirst()
	{
		return reset($this->map);
	}

	/**
	 * @param object $object
	 * @return NULL
	 */
	public function getNextTo($object)
	{
		$links=$this->getKeyLinks($this->getOffset($object));
		if ($next=$links['next']) {
			return $this->map[$next];
			}

		return NULL;
	}

	/**
	 * @param string $key
	 * @return object|NULL
	 */
	public function getNextToKey($key)
	{
		$links=$this->getKeyLinks($key);
		if ($next=$links['next']) {
			return $this->map[$next];
			}

		return NULL;
	}

	/**
	 * @param object $object
	 * @return object|NULL
	 */
	public function getPreviousTo($object)
	{
		$links=$this->getKeyLinks($this->getOffset($object));
		if ($next=$links['prev']) {
			return $this->map[$next];
			}

		return NULL;
	}

	/**
	 * @param string $key
	 * @return object|NULL
	 */
	public function getPreviousToKey($key)
	{
		$links=$this->getKeyLinks($key);
		if ($next=$links['prev']) {
			return $this->map[$next];
			}

		return NULL;
	}

	/**
	 * @return array
	 */
	public function getValues()
	{
		return array_values($this->map);
	}

	/**
	 * @return array
	 */
	public function getKeys()
	{
		return array_keys($this->map);
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->map);
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->map[$offset]);
	}

	/**
	 * @param string $offset
	 * @return object
	 */
	public function offsetGet($offset)
	{
		return $this->map[$offset];
	}

	/**
	 * @param string $offset
	 * @param object $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->insert($offset, $value);
	}

	/**
	 * @param string $offset
	 * @throws \Nette\OutOfRangeException
	 */
	public function offsetUnset($offset)
	{
		if (!isset($this->map[$offset])) {
			throw new \Nette\OutOfRangeException("Undefined key '$offset'.");
			}

		$this->remove($this->map[$offset]);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->map);
	}

	/**
	 */
	protected function doFlush()
	{
		$this->getCache()->clean(array(
			\Nette\Caching\Cache::TAGS => array('doctrine')
			));
	}

	/**
	 * @return NULL
	 */
	protected function doGetStats()
	{
		return NULL;
	}
}
