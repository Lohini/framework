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

use Nette\Reflection,
	Nette\Security\IAuthorizator;

/**
 * @method \Lohini\Security\RBAC\Role[] getRoles() getRoles()
 * @method \Lohini\Security\Identity getIdentity() getIdentity()
 */
class User
extends \Nette\Security\User
implements \Nette\Security\IAuthenticator
{
	/** @var \Lohini\Database\Doctrine\Registry */
	private $doctrine;


	/**
	 * @param \Nette\Security\IUserStorage $storage
	 * @param \Nette\DI\Container $context
	 * @param \Lohini\Database\Doctrine\Registry $doctrine
	 */
	public function __construct(\Nette\Security\IUserStorage $storage, \Nette\DI\Container $context, \Lohini\Database\Doctrine\Registry $doctrine)
	{
		parent::__construct($storage, $context);
		$this->doctrine=$doctrine;
	}

	/**
	 * @return \Lohini\Database\Doctrine\Dao
	 */
	public function getDao()
	{
		return $this->doctrine->getDao('Nette\Security\Identity');
	}

	/**
	 * @param array $credentials
	 * @return \Nette\Security\IIdentity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		/** @var Identity|NULL $identity */
		$identity=$this->getDao()
			->fetchOne(new IdentityByNameOrEmailQuery($credentials[self::USERNAME]));

		if (!$identity instanceof \Nette\Security\IIdentity) {
			throw new \Nette\Security\AuthenticationException('User not found', self::IDENTITY_NOT_FOUND);
			}
		if (!$identity->isPasswordValid($credentials[self::PASSWORD])) {
			throw new \Nette\Security\AuthenticationException('Invalid password', self::INVALID_CREDENTIAL);
			}
		if (!$identity->isApproved()) {
			throw new \Nette\Security\AuthenticationException('Account is not approved', self::NOT_APPROVED);
			}

		return new SerializableIdentity($identity);
	}

	/**
	 * @todo: validation rules
	 *
	 * @param string $username
	 * @param string $password
	 * @return Identity
	 */
	public function register($username, $password)
	{
		return $this->getDao()
			->save(new Identity($username, $password));
	}

	/**
	 * Returns a list of effective roles that a user has been granted.
	 * @return array
	 */
	public function getRoles()
	{
		if (!$this->isLoggedIn()) {
			return array($this->guestRole);
			}

		$identity=$this->getIdentity();
		return $identity? $identity->getRoleIds() : array($this->authenticatedRole);
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @param string $message
	 * @throws \Nette\Application\ForbiddenRequestException
	 */
	public function needAllowed($resource=IAuthorizator::ALL, $privilege=IAuthorizator::ALL, $message=NULL)
	{
		if (!$this->isAllowed($resource, $privilege)) {
			throw new \Nette\Application\ForbiddenRequestException($message ?: 'User is not allowed to '.($privilege? $privilege : 'access').' the resource'.($resource? " '$resource'" : NULL).'.');
			}
	}

	/**
	 * @param \Reflector|\Nette\Reflection\ClassType|\Nette\Reflection\Method $element
	 * @param string $message
	 * @return bool
	 * @throws \Nette\Application\ForbiddenRequestException
	 * @throws \Lohini\UnexpectedValueException
	 */
	public function protectElement(\Reflector $element, $message=NULL)
	{
		if (!$element instanceof Reflection\Method && !$element instanceof Reflection\ClassType) {
			return FALSE;
			}

		$user=(array)$element->getAnnotation('User');
		$message= isset($user['message'])? $user['message'] : $message;
		if (in_array('loggedIn', $user) && !$this->isLoggedIn()) {
			throw new \Nette\Application\ForbiddenRequestException($message ?: 'User '.$this->getIdentity()->getId().' is not logged in.');
			}
		if (isset($user['role']) && !$this->isInRole($user['role'])) {
			throw new \Nette\Application\ForbiddenRequestException($message ?: 'User '.$this->getIdentity()->getId()." is not in role '{$user['role']}'.");
			}
		if ($element->getAnnotation('user')) {
			throw new \Lohini\UnexpectedValueException("Annotation 'user' in $element should have been 'User'.");
			}

		$allowed=(array)$element->getAnnotation('Allowed');
		$message= isset($allowed['message'])? $allowed['message'] : $message;
		if ($allowed) {
			$resource= isset($allowed[0])? $allowed[0] : IAuthorizator::ALL;
			$privilege= isset($allowed[1])? $allowed[1] : IAuthorizator::ALL;
			$this->needAllowed($resource, $privilege, $message);
			}
		elseif ($element->getAnnotation('allowed')) {
			throw new \Lohini\UnexpectedValueException("Annotation 'allowed' in $element should have been 'Allowed'.");
			}
	}
}
