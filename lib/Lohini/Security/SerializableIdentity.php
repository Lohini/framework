<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class SerializableIdentity
extends \Nette\Object
implements \Nette\Security\IIdentity, \Nette\Security\IRole
{
	/** @var int */
	private $id;
	/** @var Identity */
	private $identity;


	/**
	 * @param Identity $identity
	 */
	public function __construct(Identity $identity)
	{
		$this->id=$identity->getId();
		$this->identity=$identity;
	}

	/**
	 * @internal
	 * @return bool
	 */
	public function isLoaded()
	{
		return $this->identity !== NULL;
	}

	/**
	 * @internal
	 * @param \Lohini\Database\Doctrine\Dao $users
	 * @throws \Lohini\UnexpectedValueException
	 */
	public function load(\Lohini\Database\Doctrine\Dao $users)
	{
		$identity=$users->getReference($this->id);
		if (!$identity instanceof Identity) {
			throw new \Lohini\UnexpectedValueException('Expected reference of Lohini\Security\Identity, '.get_class($identity).' was given.');
			}

		$this->identity=$identity;
	}

	/**
	 * Returns the ID of user.
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns a list of roles that the user is a member of.
	 *
	 * @return array
	 */
	public function getRoles()
	{
		return $this->identity->getRoles();
	}

	/**
	 * Returns a string identifier of the Role.
	 *
	 * @return string
	 */
	public function getRoleId()
	{
		return "user#".$this->id;
	}

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('id');
	}

	/***************** decorator ******************/
	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->identity, $name), $args);
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->identity->$name;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->identity->$name=$value;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->identity->$name);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		unset($this->identity->$name);
	}
}
