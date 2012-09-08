<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Security\RBAC;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Security\RBAC\RolePermission,
	Lohini\Security\RBAC\RolePermissionsQuery;

/**
 */
class RolePermissionsQueryTest
extends \Lohini\Testing\OrmTestCase
{
	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Lohini\Security\RBAC\BasePermission',
			'Lohini\Security\RBAC\RolePermission',
			'Lohini\Security\RBAC\UserPermission',
		));
	}

	/**
	 * @param array $permissions
	 * @param array $actions
	 * @param array $resources
	 */
	private function assertPermissionCombinations($permissions, $actions, $resources)
	{
		$this->assertContainsCombinations(
			$permissions,
			array($actions, $resources),
			array(
				function(RolePermission $permission) {
					return $permission->getPrivilege()->getAction()->getName();
					},
				function(RolePermission $permission) {
					return $permission->getPrivilege()->getResource()->getName();
					}
				)
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingAdminPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'admin'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(20, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'create', 'delete'),
			array('identity', 'article', 'comment', 'thread')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingRedactorPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'redactor'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'create', 'delete'),
			array('article')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingCommentsModeratorPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'commentsModerator'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'create', 'delete'),
			array('comment')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingBlogVisitorPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'blog-visitor'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(4, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view'),
			array('comment', 'article')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingForumModeratorPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'forumModerator'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'delete', 'create'),
			array('thread')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingForumVisitorPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'forum-visitor'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(4, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'create'),
			array('thread')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientAdminPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'client-admin'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(10, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'create', 'delete'),
			array('article', 'identity')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientBlogPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'client-blog'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(10, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'create', 'delete'),
			array('article', 'comment')
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientForumPermissions()
	{
		$role=$this->getDao('Lohini\Security\RBAC\Role')->findOneBy(array('name' => 'client-forum'));

		$permissions=$this->getDao('Lohini\Security\RBAC\RolePermission')
			->fetch(new RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('access', 'view', 'edit', 'create', 'delete'),
			array('thread')
			);
	}
}
