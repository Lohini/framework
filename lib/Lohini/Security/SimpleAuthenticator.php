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
class SimpleAuthenticator
extends \Nette\Object
implements \Nette\Security\IAuthenticator
{
	/** @var \Nette\Security\IIdentity */
	private $identity;


	/**
	 * @param \Nette\Security\IIdentity $identity
	 */
	public function __construct(\Nette\Security\IIdentity $identity)
	{
		$this->identity=$identity;
	}

	/**
	 * @param array $credentials
	 * @return \Nette\Security\IIdentity
	 */
	public function authenticate(array $credentials)
	{
		return $this->identity;
	}
}
