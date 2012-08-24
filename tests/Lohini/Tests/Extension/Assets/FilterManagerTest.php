<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Assets;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class FilterManagerTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Extension\Assets\FilterManager */
	private $manager;
	/** @var \Nette\DI\Container */
	private $container;


	public function setUp()
	{
		$this->container=new \Nette\DI\Container;
		$this->manager=new \Lohini\Extension\Assets\FilterManager($this->container);
	}

	public function testProvidesRegisteredService()
	{
		$this->assertFalse($this->manager->has('foo'));

		$foo=new FilterMock;
		$this->container->addService('filter_foo', $foo);
		$this->manager->registerFilterService('filter_foo', 'foo');
		$this->assertTrue($this->manager->has('foo'));
		$this->assertSame($foo, $this->manager->get('foo'));
		$this->assertEquals(array('foo'), $this->manager->getNames());

		$bar=new FilterMock;
		$this->manager->set('bar', $bar);
		$this->assertSame($bar, $this->manager->get('bar'));
		$this->assertEquals(array('foo', 'bar'), $this->manager->getNames());
	}
}
