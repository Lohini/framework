<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security\RBAC;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Persistence\IDao;

/**
 */
class UnitBuilder
extends \Nette\Object
{
	/** @var array */
	public $actions=array();
	/** @var array */
	public $resources=array();
	/** @var array */
	public $divisions=array();
	/** @var array */
	public $roles=array();
	/** @var array */
	public $users=array();
	/** @var array */
	public $privileges=array();
	/** @var array */
	public $permissions=array();
	/** @var array */
	private $acl;


	/**
	 * @param array $acl
	 */
	public function __construct(array $acl)
	{
		$this->acl=$acl+array(
			'actions' => array(),
			'resources' => array(),
			'divisions' => array(),
			'roles' => array(),
			'users' => array(),
			'permissions' => array(),
			'userPermissions' => array(),
			);
	}

	/**
	 */
	public function build()
	{
		$this->buildActions();
		$this->buildResources();
		$this->buildDivisions();
		$this->buildRoles();
		$this->buildUsers();
		$this->buildPrivileges();
		$this->buildPermissions();
		$this->buildUserPermissions();
	}

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function persist(\Doctrine\ORM\EntityManager $em)
	{
		$em->getRepository('Lohini\Security\RBAC\Action')->save($this->actions, IDao::NO_FLUSH);
		$em->getRepository('Lohini\Security\RBAC\Resource')->save($this->resources, IDao::NO_FLUSH);
		$em->getRepository('Lohini\Security\RBAC\Division')->save($this->divisions, IDao::NO_FLUSH);
		$em->getRepository('Lohini\Security\RBAC\Role')->save($this->roles, IDao::NO_FLUSH);
		$em->getRepository('Lohini\Security\Identity')->save($this->users, IDao::NO_FLUSH);
		$em->getRepository('Lohini\Security\RBAC\Privilege')->save($this->privileges, IDao::NO_FLUSH);
		$em->getRepository('Lohini\Security\RBAC\BasePermission')->save($this->permissions, IDao::NO_FLUSH);
		$em->flush();
	}

	/**
	 */
	private function buildActions()
	{
		foreach ($this->acl['actions'] as $action) {
			$this->actions[$action['name']]=new Action($action['name'], $action['description']);
			}
	}

	/**
	 */
	private function buildResources()
	{
		foreach ($this->acl['resources'] as $resource) {
			$this->resources[$resource['name']]=new Resource($resource['name'], $resource['description']);
			}
	}

	/**
	 */
	private function buildDivisions()
	{
		foreach ($this->acl['divisions'] as $division) {
			$this->divisions[$division['name']]=new Division($division['name']);
			}
	}

	/**
	 */
	private function buildRoles()
	{
		foreach ($this->acl['roles'] as $role) {
			$division=$this->divisions[$role['division']];
			$this->roles[$role['name']]=new Role($role['name'], $division);
			}
	}

	/**
	 */
	private function buildUsers()
	{
		foreach ($this->acl['users'] as $user) {
			$this->users[$user['username']]=$identity=new \Lohini\Security\Identity($user['username'], $user['password'], $user['email']);
			foreach ($user['roles'] as $role) {
				$identity->addRole($this->roles[$role]);
				}
			}
	}

	/**
	 */
	private function buildPrivileges()
	{
		foreach ($this->resources as $resource) {
			foreach ($this->actions as $action) {
				$this->privileges[]=new Privilege($resource, $action);
				}
			}
	}

	/**
	 * @param string $resource
	 * @param string $action
	 * @return array
	 */
	public function searchPrivileges($resource, $action)
	{
		return array_filter(
			$this->privileges,
			function(Privilege $privilege) use ($resource, $action) {
				return ($resource==='*' || in_array($privilege->getResource()->getName(), (array)$resource))
					&& ($action==='*' || in_array($privilege->getAction()->getName(), (array)$action));
				}
			);
	}

	/**
	 */
	private function buildPermissions()
	{
		foreach ($this->acl['permissions'] as $permission) {
			$role=$this->roles[$permission['role']];
			foreach ($this->searchPrivileges($permission['resource'], $permission['action']) as $privilege) {
				$role->getDivision()->addPrivilege($privilege);
				$this->permissions[]=$role->createPermission($privilege);
				}
			}
	}

	/**
	 */
	private function buildUserPermissions()
	{
		foreach ($this->acl['userPermissions'] as $permission) {
				$user=$this->users[$permission['user']];
				$role=$this->roles[$permission['role']];
				foreach ($this->searchPrivileges($permission['resource'], $permission['action']) as $privilege) {
					$this->permissions[]=$perm=$user->overridePermission($role, $privilege);
					$perm->setAllowed($permission['isAllowed']);
					}
				}
	}
}
