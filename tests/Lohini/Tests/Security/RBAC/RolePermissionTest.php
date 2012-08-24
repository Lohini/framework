<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Security\RBAC;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Security\RBAC;

/**
 */
class RolePermissionTest
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

		$this->getDao($division)->save($division);

		$this->assertEntityValues('Lohini\Security\RBAC\Action', array('name' => 'read'), 1);
		$this->assertEntityValues('Lohini\Security\RBAC\Resource', array('name' => 'article'), 1);
		$this->assertEntityValues(
			'Lohini\Security\RBAC\Privilege',
			array(
				'action' => 1,
				'resource' => 1
				),
			1
			);

		$this->assertEntityValues('Lohini\Security\RBAC\Division', array('name' => 'blog'), 1);
		$this->assertEntityValues(
			'Lohini\Security\RBAC\Role',
			array(
				'name' => 'reader',
				'division' => 1
				),
			1
			);

		$this->assertEntityValues(
			'Lohini\Security\RBAC\RolePermission',
			array(
				'isAllowed' => TRUE,
				'role' => 1,
				'privilege' => 1
				),
			1
			);
	}
}
