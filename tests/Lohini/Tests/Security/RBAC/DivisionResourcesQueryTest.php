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

/**
 */
class DivisionResourcesQueryTest
extends \Lohini\Testing\OrmTestCase
{
	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Lohini\Security\RBAC\Division',
			'Lohini\Security\RBAC\BasePermission',
			'Lohini\Security\RBAC\RolePermission',
			'Lohini\Security\RBAC\UserPermission',
			));
	}

	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingResources()
	{
		$blog=$this->getDao('Lohini\Security\RBAC\Division')->findOneBy(array('name' => 'blog'));

		$resources=$this->getDao('Lohini\Security\RBAC\Resource')
			->fetch(new \Lohini\Security\RBAC\DivisionResourcesQuery($blog));

		$resources=iterator_to_array($resources);
		$this->assertCount(2, $resources, 'There are two resources comment & article in blog');
		$this->assertContainsOnly('Lohini\Security\RBAC\Resource', $resources);

		list($a, $b)=$resources;
		$this->assertSame('article', $a->getName());
		$this->assertSame('comment', $b->getName());
	}
}
