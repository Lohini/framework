<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class SimpleUserStorage
extends \Nette\Object
implements \Nette\Security\IUserStorage
{
	/** @var bool */
	private $autheticated=FALSE;
	/** @var \Nette\Security\IIdentity */
	private $identity;
	/** @var string */
	private $namespace;


	/**
	 * Sets the authenticated status of this user.
	 *
	 * @param bool $state
	 * @return SimpleUserStorage
	 */
	public function setAuthenticated($state)
	{
		$this->autheticated=(bool)$state;
		return $this;
	}

	/**
	 * Is this user authenticated?
	 *
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return $this->autheticated;
	}

	/**
	 * Sets the user identity.
	 *
	 * @param \Nette\Security\IIdentity|NULL $identity
	 * @return SimpleUserStorage
	 */
	public function setIdentity(\Nette\Security\IIdentity $identity=NULL)
	{
		$this->identity=$identity;
		return $this;
	}

	/**
	 * Returns current user identity, if any.
	 *
	 * @return \Nette\Security\IIdentity|NULL
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * Changes namespace; allows more users to share a session.
	 *
	 * @param string $namespace
	 * @return SimpleUserStorage
	 */
	public function setNamespace($namespace)
	{
		$this->namespace=(string)$namespace;
		return $this;
	}

	/**
	 * Returns current namespace.
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Enables log out after inactivity.
	 *
	 * @param int $time
	 * @param int $flags
	 * @return SimpleUserStorage
	 */
	public function setExpiration($time, $flags=0)
	{
		trigger_error(get_called_class().'::setExpiration() is not supported', E_USER_NOTICE);
		return $this;
	}

	/**
	 * Why was user logged out?
	 *
	 * @return NULL
	 */
	public function getLogoutReason()
	{
		return NULL;
	}
}
