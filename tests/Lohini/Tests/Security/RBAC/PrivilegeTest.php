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
class PrivilegeTest
extends \Lohini\Testing\TestCase
{
	/** @var Action */
	private $action;
	/** @var Resource */
	private $resource;
	/** @var Privilege */
	private $privilege;


	public function setUp()
	{
		$this->action=new \Lohini\Security\RBAC\Action('view');
		$this->resource=new \Lohini\Security\RBAC\Resource('article');
		$this->privilege=new \Lohini\Security\RBAC\Privilege($this->resource, $this->action);
	}

	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->privilege->getId());
	}

	public function testProvidesComponents()
	{
		$this->assertSame($this->action, $this->privilege->getAction());
		$this->assertSame($this->resource, $this->privilege->getResource());
	}
}
