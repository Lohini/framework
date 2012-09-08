<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Pokud je objekt zmrazen půjde pouze volat properties a metody
 */
class LogicDelegator
extends \Nette\FreezableObject
{
	/** @var array */
	private $callbacks=array();
	/** @var mixed */
	private $delegate;


	/**
	 * @param mixed $delegate
	 */
	public function __construct($delegate=NULL)
	{
		$this->delegate=$delegate;
	}

	/**
	 * pokud je neznámá metoda zavolána s argumentem uložit do $this->callbacks
	 * pokud je neznámá metoda zavolána bez argumentu a je v poli callbacks tak zavolat a vrátit, jinak vyjímka
	 *
	 * @param string $method
	 * @param callable|NULL $callback
	 * @return LogicDelegator|mixed
	 */
	public function __call($method, $callback=NULL)
	{
		if (is_callable($callback)) {
			$this->updating();
			$this->callbacks[$method]=$callback;

			return $this;
			}

		return $this->callbacks[$method]($this->delegate);
	}

	/**
	 * pokud je čteno z neznámé property a je v poli callbacks tak zavolat a vrátit, jinak vyjímka
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->callbacks[$name]($this->delegate);
	}

	/**
	 * pokud je do neznámé property ukládáno uložit do $this->callbacks
	 *
	 * @param string $property
	 * @param callable $callback
	 */
	public function __set($property, $callback)
	{
		$this->updating();

		if (is_callable($callback)) {
			$this->callbacks[$property] = $callback;
			}
	}

	public function __clone()
	{
		$this->updating();
	}
}
