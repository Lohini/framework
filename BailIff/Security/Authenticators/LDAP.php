<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Security\Authenticators;

use Nette\Security\IAuthenticator;

/**
 * Authentication against LDAP (Windows domain controller)
 * @author Lopo <lopo@losys.eu>
 */
class LDAP
extends \BailIff\Security\Authenticator
{
	/** @var \BailIff\Database\Doctrine\ORM\Container */
	private $context;


	/**
	 * @param \BailIff\Database\Models\Services\Users $users
	 */
	public function __construct(\BailIff\Database\Doctrine\ORM\Container $context)
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
		$entity=$this->context->getModelService('BailIff\Database\Models\Entities\User')->repository->findByUsernameOrEmail($credentials[IAuthenticator::USERNAME]);
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
