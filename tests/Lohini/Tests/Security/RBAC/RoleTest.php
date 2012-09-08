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

/**
 */
class RoleTest
extends \Lohini\Testing\TestCase
{
	/** @var Role */
	private $role;
	/** @var Division */
	private $division;


	public function setUp()
	{
		$this->division=new \Lohini\Security\RBAC\Division('administration');
		$this->role=new \Lohini\Security\RBAC\Role('admin', $this->division);
	}

	public function testImplementsIRole()
	{
		$this->assertInstanceOf('Nette\Security\IRole', $this->role);
	}

	public function testSettingName()
	{
		$this->assertEquals('admin', $this->role->getName());
		$this->assertEquals('', $this->role->getRoleId());
	}

	public function testSettingDescription()
	{
		$this->role->setDescription('The God');
		$this->assertEquals('The God', $this->role->getDescription());
	}

	public function testProvidesDivision()
	{
		$this->assertSame($this->division, $this->role->getDivision());
	}
}
