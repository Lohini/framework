<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\EventDispatcher;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class Event
extends \Nette\Object
implements \ArrayAccess, \IteratorAggregate, \Countable
{
	/** @var array|callable[]|\Nette\Callback[] */
	private $listeners=array();
	/** @var string */
	private $name;
	/** @var EventManager */
	private $eventManager;


	/**
	 * @param string $name
	 * @param EventManager $eventManager
	 */
	public function __construct($name, EventManager $eventManager=NULL)
	{
		$this->name=$name;
		$this->eventManager=$eventManager;
	}

	/**
	 * Invokes the event.
	 *
	 * @param array $args
	 */
	public function dispatch(array $args=array())
	{
		foreach ($this->getListeners() as $handler) {
			if ($handler->invokeArgs(array_values($args))===FALSE) {
				return;
				}
			}
	}

	/**
	 * @param callable $listener
	 */
	public function add($listener)
	{
		$this->listeners[]=callback($listener);
	}

	/**
	 * @return array|\Nette\Callback[]
	 */
	public function getListeners()
	{
		$listeners=$this->listeners;
		if (!$this->eventManager || !$this->eventManager->hasListeners($this->name)) {
			return $listeners;
			}

		foreach ($this->eventManager->getListeners($this->name) as $listener) {
			$listeners[]=callback($listener, $this->name);
			}

		return $listeners;
	}

	/**
	 * Invokes the event.
	 */
	public function __invoke()
	{
		$this->dispatch(func_get_args());
	}

	/********************* interface \Countable *********************/
	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->listeners);
	}

	/********************* interface \IteratorAggregate *********************/
	/**
	 * @return \ArrayIterator|\Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getListeners());
	}

	/********************* interface \ArrayAccess *********************/
	/**
	 * @param int|NULL $index
	 * @param mixed $item
	 */
	public function offsetSet($index, $item)
	{
		if ($index===NULL) { // append
			$this->listeners[]=callback($item);
			}
		else { // replace
			$this->listeners[$index]=callback($item);
			}
	}

	/**
	 * @param int $index
	 * @return callable|mixed
	 * @throws \Lohini\MemberAccessException
	 */
	public function offsetGet($index)
	{
		if (!$this->offsetExists($index)) {
			throw new \Lohini\MemberAccessException;
			}

		return $this->listeners[$index];
	}

	/**
	 * @param int $index
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->listeners[$index]);
	}

	/**
	 * @param int $index
	 */
	public function offsetUnset($index)
	{
		unset($this->listeners[$index]);
	}
}
