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

use Lohini\Security\RBAC\UserPermission,
	Lohini\Security\RBAC\UserPermissionsQuery;

/**
 */
class UserPermissionsQueryTest
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
					function(UserPermission $permission) {
						return $permission->getPrivilege()->getAction()->getName();
						},
					function(UserPermission $permission) {
						return $permission->getPrivilege()->getResource()->getName();
						}
					)
				);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientAdminPermissions()
	{
		$identity=$this->getDao('Lohini\Security\Identity')->findOneBy(array('username' => 'macho-client'));
		$division=$this->getDao('Lohini\Security\RBAC\Division')->findOneBy(array('name' => 'administration'));

		$permissions=$this->getDao('Lohini\Security\RBAC\UserPermission')
			->fetch(new UserPermissionsQuery($identity, $division));

		$this->assertCount(6, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\UserPermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('edit', 'create', 'delete'),
			array('article', 'identity')
			);

		$this->assertItemsMatchesCondition(
			$permissions,
			function (UserPermission $permission) { return $permission->isAllowed()===FALSE; }
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientBlogPermissions()
	{
		$identity=$this->getDao('Lohini\Security\Identity')->findOneBy(array('username' => 'macho-client'));
		$division=$this->getDao('Lohini\Security\RBAC\Division')->findOneBy(array('name' => 'blog'));

		$permissions=$this->getDao('Lohini\Security\RBAC\UserPermission')
			->fetch(new UserPermissionsQuery($identity, $division));

		$this->assertCount(6, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\UserPermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('edit', 'create', 'delete'),
			array('article', 'comment')
			);

		$this->assertItemsMatchesCondition(
			$permissions,
			function (UserPermission $permission) { return $permission->isAllowed()===FALSE; }
			);
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientForumPermissions()
	{
		$identity=$this->getDao('Lohini\Security\Identity')->findOneBy(array('username' => 'macho-client'));
		$division=$this->getDao('Lohini\Security\RBAC\Division')->findOneBy(array('name' => 'forum'));

		$permissions=$this->getDao('Lohini\Security\RBAC\UserPermission')
			->fetch(new UserPermissionsQuery($identity, $division));

		$this->assertCount(3, $permissions);
		$this->assertContainsOnly('Lohini\Security\RBAC\UserPermission', $permissions);

		$this->assertPermissionCombinations(
			$permissions,
			array('edit', 'create', 'delete'),
			array('thread')
			);

		$this->assertItemsMatchesCondition(
			$permissions,
			function (UserPermission $permission) { return $permission->isAllowed()===FALSE; }
			);
	}
}
