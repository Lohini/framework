<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\DI;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
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
		throw new \Nette\OutOfRangeException("Missing key  '$key' in ".get_class($this).'->params');
	}
}
