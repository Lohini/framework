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
class ActionTest
extends \Lohini\Testing\TestCase
{
	/** @var Action */
	private $action;


	public function setUp()
	{
		$this->action=new \Lohini\Security\RBAC\Action('view');
	}

	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->action->getId());
	}

	public function testSettingName()
	{
		$this->assertEquals('view', $this->action->getName());
	}

	public function testSettingDescription()
	{
		$this->action->setDescription('Required to read stuff');
		$this->assertEquals('Required to read stuff', $this->action->getDescription());
	}
}
