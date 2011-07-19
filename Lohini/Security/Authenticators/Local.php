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
 * Authentication against own database
 * @author Lopo <lopo@lohini.net>
 */
class Local
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
		$password=$credentials[IAuthenticator::PASSWORD];
		$salt=$this->context->context->params['security']['salt'];

		return $entity->password==eval("return {$entity->authconnection->passwordCallback};");
	}
}
