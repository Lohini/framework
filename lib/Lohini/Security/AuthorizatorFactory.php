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

use Nette\Security\IIdentity;

/**
 */
class AuthorizatorFactory
extends \Nette\Object
{
	/** @var User */
	private $user;
	/** @var \Nette\Http\Session */
	private $session;
	/** @var \Lohini\Database\Doctrine\Dao */
	private $divisions;
	/** @var \Lohini\Database\Doctrine\Dao */
	private $resources;
	/** @var \Lohini\Database\Doctrine\Dao */
	private $rolePermissions;
	/** @var \Lohini\Database\Doctrine\Dao */
	private $userPermissions;


	/**
	 * @param User $user
	 * @param \Nette\Http\Session $session
	 * @param \Lohini\Database\Doctrine\Registry $registry
	 */
	public function __construct(User $user, \Nette\Http\Session $session, \Lohini\Database\Doctrine\Registry $registry)
	{
		$this->user=$user;
		$this->session=$session;
		$this->divisions=$registry->getDao('Lohini\Security\RBAC\Division');
		$this->resources=$registry->getDao('Lohini\Security\RBAC\Resource');
		$this->rolePermissions=$registry->getDao('Lohini\Security\RBAC\RolePermission');
		$this->userPermissions=$registry->getDao('Lohini\Security\RBAC\UserPermission');
	}

	/**
	 * @param \Nette\Security\IIdentity $identity
	 * @param RBAC\Division $division
	 * @return \Nette\Security\Permission
	 */
	public function create(IIdentity $identity=NULL, RBAC\Division $division=NULL)
	{
		if ($identity===NULL) {
			$identity=$this->user->getIdentity();
			if (!$identity instanceof IIdentity) {
				return new SimplePermission; // default stub
				}
			}

		if ($division===NULL) {
			$divisionName=$this->user->getStorage()->getNamespace();
			$division=$this->divisions->findByName($divisionName);
			}

		if (!$division) {
			return new SimplePermission; // default stub
			}

		$session=$this->session->getSection('Lohini.Security.Permission/'.$division->getName());
		if (isset($session['permission']) && $session['identity']===$identity->getId()) {
			return $session['permission'];
			}

		// create IAuthorizator object
		$permission=$this->doCreatePermission();

		// find resources
		$resources=$this->resources->fetch(new RBAC\DivisionResourcesQuery($division));
		foreach ($resources as $resource) {
			$permission->addResource($resource->name);
			}

		// identity roles
		foreach ($identity->getRoles() as $role) {
			$permission->addRole($role->getRoleId());

			// identity role rules
			$rules=$this->rolePermissions->fetch(new RBAC\RolePermissionsQuery($role));
			foreach ($rules as $rule) {
				if ($rule->getDivision()!==$division) {
					continue;
					}

				$rule->applyTo($permission);
				}
			}

		// identity specific rules
		$rules=$this->userPermissions->fetch(new RBAC\UserPermissionsQuery($identity, $division));
		foreach ($rules as $rule) {
			if ($rule->getDivision()!==$division) {
				continue;
				}

			$rule->applyTo($permission);
			}

		$session['identity']=$identity->getId();
		return $session['permission']=$permission;
	}

	/**
	 * @return \Nette\Security\Permission
	 */
	protected function doCreatePermission()
	{
		return new Permission;
	}
}
