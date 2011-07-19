<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Types\Password;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * @author Filip Procházka
 */
class Password
extends \Nette\Object
{
	/** @var string */
	private $value;
	/** @var string */
	private $salt;


	/**
	 * @param string $hash
	 */
	public function __construct($hash=NULL)
	{
		$this->value=$hash;
	}

	/**
	 * @param string $salt
	 */
	public function setSalt($salt)
	{
		$this->salt=$salt;
	}

	/**
	 * @return string
	 */
	public function getSalt()
	{
		return $this->salt;
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->value;
	}

	/**
	 * @param string $password
	 * @return \BailIff\Types\Password
	 */
	public function setPassword($password, $salt=NULL)
	{
		$this->value=$this->hashPassword($password, $salt ?: $this->salt);
		return $this;
	}

	/**
	 * @param string $password
	 * @param string $salt
	 */
	public function isEqual($password, $salt=NULL)
	{
		return $this->value===$this->hashPassword($password, $salt ?: $this->salt);
	}

	/**
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	private function hashPassword($password, $salt=NULL)
	{
		return sha1($salt.(string)$password);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}
}
