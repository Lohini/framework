<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Types;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @property string $salt
 */
class Password
extends \Nette\Object
{
	/** @var string */
	const SEPARATOR='##';
	/** @var string */
	private $value;
	/** @var string */
	private $salt;


	/**
	 * @param string $hash
	 * @param string $salt
	 */
	public function __construct($hash=NULL, $salt=NULL)
	{
		$this->value=$hash;
		$this->salt=$salt;
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
	public function createSalt()
	{
		return $this->salt=\Nette\Utils\Strings::random(5);
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
	 * @return Password
	 */
	public function setPassword($password, $salt=NULL)
	{
		if ($password===NULL) {
			$this->value=NULL;
			$this->salt=NULL;
			return $this;
			}

		if ($salt!==NULL) {
			$this->salt=$salt;
			}
		elseif ($this->salt===NULL) {
			$this->salt=$this->createSalt();
			}

		$this->value=$this->hashPassword($password, $this->salt);
		return $this;
	}

	/**
	 * @param string $password
	 * @param string $salt
	 */
	public function isEqual($password, $salt=NULL)
	{
		if ($salt!==NULL) {
			$this->salt=$salt;
			}

		return $this->value===$this->hashPassword($password, $this->salt);
	}

	/**
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	protected function hashPassword($password, $salt=NULL)
	{
		return hash('sha512', $salt.self::SEPARATOR.(string)$password);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}
}
