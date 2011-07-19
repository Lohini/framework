<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Security;

/**
 * Base authenticator
 * @author Lopo <lopo@losys.eu>
 */
class Authenticator
extends \Nette\Object
implements \Nette\Security\IAuthenticator
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
	 * Performs an authentication against e.g. database.
	 * @param array $credentials
	 * @return \Nette\Security\IIdentity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$service=$this->context->getModelService('BailIff\Database\Models\Entities\User');
		if (strpos($credentials[self::USERNAME], '@')!==FALSE) {
			$user=$service->repository->findOneByEmail($credentials[self::USERNAME]);
			}
		else {
			$user=$service->repository->findOneByUsername($credentials[self::USERNAME]);
			}
//		$user=$service->repository->findByUsernameOrEmail($credentials[self::USERNAME]);
		if (empty($user)) {
			throw new \Nette\Security\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
			}
		$dname=__NAMESPACE__.'\Authenticators\\'.$user->authconnection->authenticator;
		$driver=new $dname($this->context);
		if ($driver->authenticate($credentials)===FALSE) {
			throw new \Nette\Security\AuthenticationException('Invalid password.', self::INVALID_CREDENTIAL);
			}
		return $user->identity;
	}
}
