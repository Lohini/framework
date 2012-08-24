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
class DivisionTest
extends \Lohini\Testing\TestCase
{
	/** @var Division */
	private $division;


	public function setUp()
	{
		$this->division=new \Lohini\Security\RBAC\Division('forum');
	}

	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->division->getId());
	}

	public function testSettingName()
	{
		$this->assertEquals('forum', $this->division->getName());
	}

	public function testSettingDescription()
	{
		$this->division->setDescription('Something with spam');
		$this->assertEquals('Something with spam', $this->division->getDescription());
	}

//	public function testStoringPermissions()
//	{
//		$permission=new \Lohini\Security\RBAC\RolePermission($this->division, $privilege, $role);
//	}
}
