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
 * Authentication against database
 * @author Lopo <lopo@lohini.net>
 */
class DB
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
		$connection=$entity->authconnection;
		$callback=$connection->passwordCallback;
		$password=$credentials[IAuthenticator::PASSWORD];
		$salt=$this->context->context->params['security']['salt'];

		$cnn=array(
			'driver' => $connection->driver,
			'host' => $connection->address,
			'username' => $connection->adminUsername,
			'password' => $connection->adminPassword,
			'persistent' => FALSE
			);

		try {
			return FALSE!==\dibi::connect($cnn)
					->select('*')
					->from($connection->storage)
					->where("$connection->cellUsername=%s", $credentials[IAuthenticator::USERNAME])
					->and(
						"$connection->cellPassword=%s",
						call_user_func(function() use (&$callback, &$password, &$salt) { return eval("return $callback;");})
						)
				->fetch();
			}
		catch (\Exception $e) {
			return FALSE;
			}
	}
}