<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security\Authenticators;

use Nette\Security\IAuthenticator;

/**
 * Authentication against LDAP (Windows domain controller)
 * @author Lopo <lopo@lohini.net>
 */
class LDAP
extends \Lohini\Security\Authenticator
{
	/** @var \Lohini\Database\Doctrine\ORM\Container */
	private $context;


	/**
	 * @param \Lohini\Database\Models\Services\Users $users
	 */
	public function __construct(\Lohini\Database\Doctrine\ORM\Container $context)
	{
		$this->context=$context;
	}

	/**
	 * Performs an authentication
	 * @param array $credentials
	 * @return bool
	 */
	public function authenticate(array $credentials)
	{
		$entity=$this->context->getModelService('Lohini\Database\Models\Entities\User')->repository->findByUsernameOrEmail($credentials[IAuthenticator::USERNAME]);
		$ret=FALSE;
		$con=ldap_connect($entity->authconnection->address);
		if (!$con) {
			return FALSE;
			}
		ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
		if (@ldap_bind($con, $credentials[IAuthenticator::USERNAME].'@'.$entity->authconnection->storage, $credentials[IAuthenticator::PASSWORD])) {
			$ret=TRUE;
			}
		ldap_close($con);
		return $ret;
	}
}
