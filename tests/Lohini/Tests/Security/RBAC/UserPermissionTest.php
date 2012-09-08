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

use Lohini\Persistence\IDao,
	Lohini\Security\RBAC;

/**
 */
class UserPermissionTest
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
	 * @group database
	 */
	public function testPersisting()
	{
		$action=new RBAC\Action('read');
		$resource=new RBAC\Resource('article');
		$privilege=new RBAC\Privilege($resource, $action);

		$division=new RBAC\Division('blog');
		$division->addPrivilege($privilege);

		$role=new RBAC\Role('reader', $division);
		$role->createPermission($privilege);

		$identity=new \Lohini\Security\Identity('Lopo', 'Nette', 'lopo@lohini.net');
		$identity->addRole($role);
		$permission=$identity->overridePermission($role, $privilege)->setAllowed(FALSE);

		$this->getDao($identity)->save($identity, IDao::NO_FLUSH);
		$this->getDao($permission)->save($permission, IDao::NO_FLUSH);
		$this->getDao($division)->save($division);

		$this->assertEntityCount(1, 'Lohini\Security\RBAC\Action');
		$this->assertEntityCount(1, 'Lohini\Security\RBAC\Resource');
		$this->assertEntityCount(1, 'Lohini\Security\RBAC\Privilege');
		$this->assertEntityCount(1, 'Lohini\Security\RBAC\Division');
		$this->assertEntityCount(1, 'Lohini\Security\RBAC\Role');
		$this->assertEntityCount(1, 'Lohini\Security\Identity');
		$this->assertEntityCount(2, 'Lohini\Security\RBAC\BasePermission');
	}
}
