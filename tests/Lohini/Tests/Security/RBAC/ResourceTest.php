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
class ResourceTest
extends \Lohini\Testing\TestCase
{
	/** @var Resource */
	private $resource;


	public function setUp()
	{
		$this->resource=new \Lohini\Security\RBAC\Resource('article');
	}

	public function testImplementsIResource()
	{
		$this->assertInstanceOf('Nette\Security\IResource', $this->resource);
	}

	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->resource->getId());
	}

	public function testSettingName()
	{
		$this->assertEquals('article', $this->resource->getName());
		$this->assertEquals('article', $this->resource->getResourceId());
	}

	public function testSettingDescription()
	{
		$this->resource->setDescription('Stuff to read');
		$this->assertEquals('Stuff to read', $this->resource->getDescription());
	}
}
