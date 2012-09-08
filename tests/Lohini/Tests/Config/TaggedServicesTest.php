<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Config;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Config\TaggedServices;

/**
 */
class TaggedServicesTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return \Nette\DI\Container
	 */
	public function dataContainer()
	{
		$container=new \Nette\DI\Container;

		$container->addService(
			'one',
			function() { return (object)array('id' => 1); },
			array(\Nette\DI\Container::TAGS => array('lorem' => 'ipsum'))
			);

		$container->addService(
			'two',
			function() { return (object)array('id' => 2); },
			array(\Nette\DI\Container::TAGS => array('lorem' => 'ipsum'))
			);

		$container->addService(
			'three',
			function() { return (object)array('id' => 3); },
			array(\Nette\DI\Container::TAGS => array('lorem' => 'dolor'))
			);

		$container->addService(
			'four',
			function() { return (object)array('id' => 4); }
			);

		return $container;
	}

	public function testFindTaggedServices()
	{
		$list=new TaggedServices('lorem', $this->dataContainer());
		$this->assertEquals(
			array(
				(object)array('id' => 1),
				(object)array('id' => 2),
				(object)array('id' => 3),
				),
			iterator_to_array($list)
			);
	}

	public function testTrullyLazy()
	{
		$list=new TaggedServices('lorem', $container=$this->dataContainer());

		$this->assertFalse($container->isCreated('one'));
		$this->assertFalse($container->isCreated('two'));
		$this->assertFalse($container->isCreated('three'));
		$this->assertFalse($container->isCreated('four'));

		foreach ($list as $name => $service) {
			if ($name===0) { // one
				$this->assertTrue($container->isCreated('one'));
				$this->assertFalse($container->isCreated('two'));
				$this->assertFalse($container->isCreated('three'));
				$this->assertFalse($container->isCreated('four'));
				}
			elseif ($name===1) { // two
				$this->assertTrue($container->isCreated('one'));
				$this->assertTrue($container->isCreated('two'));
				$this->assertFalse($container->isCreated('three'));
				$this->assertFalse($container->isCreated('four'));
				}
			elseif ($name===2) { // three
				$this->assertTrue($container->isCreated('one'));
				$this->assertTrue($container->isCreated('two'));
				$this->assertTrue($container->isCreated('three'));
				$this->assertFalse($container->isCreated('four'));
				}
			else {
				$this->fail('Unexpected key');
				}
			}
	}

	public function testFindByMeta()
	{
		$list=new TaggedServices('lorem', $this->dataContainer());
		$this->assertEquals((object)array('id' => 3), $list->findOneByMeta('dolor'));
		$this->assertEquals((object)array('id' => 1), $list->findOneByMeta('ipsum'));

		$this->assertEquals(
			array((object)array('id' => 3)),
			$list->findByMeta('dolor')
			);

		$this->assertEquals(
			array(
				(object)array('id' => 1),
				(object)array('id' => 2),
				),
			$list->findByMeta('ipsum')
			);
	}

	public function testCreateByMeta()
	{
		$list=new TaggedServices('component', new ComponentsContainerMock);

		$foo=$list->createOneByMeta('foo');
		$this->assertInstanceOf('stdClass', $foo);
		$this->assertEquals('foo', $foo->name);

		$bar=$list->createOneByMeta('bar');
		$this->assertInstanceOf('stdClass', $bar);
		$this->assertEquals('bar', $bar->name);

		$this->assertNull($list->createOneByMeta('baz'));
	}
}


/**
 */
class ComponentsContainerMock
extends \Nette\DI\Container
{
	/**
	 * @var array
	 */
	public $meta=array(
		'foo' => array(
			'tags' => array(
				'component' => 'foo'
				)
			),
		'bar' => array(
			'tags' => array(
				'component' => 'bar'
				)
			)
		);


	/**
	 * @return object
	 */
	public function createFoo()
	{
		return (object)array('name' => 'foo');
	}

	/**
	 * @return object
	 */
	public function createBar()
	{
		return (object)array('name' => 'bar');
	}

	/**
	 * @return object
	 */
	public function createBaz()
	{
		return (object)array('name' => 'baz');
	}
}
