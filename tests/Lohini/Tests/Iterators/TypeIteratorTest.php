<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Iterators;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Iterators\TypeIterator;

/**
 */
class TypeIteratorTest
extends \Lohini\Testing\TestCase
{
	/** @var TypeIterator */
	private $iterator;


	public function setUp()
	{
		$this->iterator=new TypeIterator(new \ArrayIterator(array(
			'Lohini\Tests\Iterators\Mocks\Bar_1',
			'Lohini\Tests\Iterators\Mocks\Bar_2',
			'Lohini\Tests\Iterators\Mocks\Foo_1',
			'Lohini\Tests\Iterators\Mocks\Foo_2',
			'Lohini\Tests\Iterators\Mocks\Foo_3',
			'Lohini\Tests\Iterators\Mocks\Foo_4',
			'Lohini\Tests\Iterators\Mocks\Foo_5',
			'Lohini\Tests\Iterators\Mocks\Foo_6',
			)));
	}

	public function testSelectAbstractClasses()
	{
		$this->assertSame(array(
			'Lohini\Tests\Iterators\Mocks\Foo_1',
			'Lohini\Tests\Iterators\Mocks\Foo_5',
		), array_values($this->iterator->isAbstract()->getResult()));
	}

	public function testSelectSubclasses()
	{
		$it=$this->iterator->isSubclassOf('Lohini\Tests\Iterators\Mocks\Foo_1');

		$this->assertSame(
			array(
				'Lohini\Tests\Iterators\Mocks\Foo_2',
				),
			array_values($it->getResult())
			);

		// there can't be subclass of two different classes
		$this->assertEquals(array(), array_values($it->isSubclassOf('Lohini\Tests\Iterators\Mocks\Foo_5')->getResult()));
	}

	public function testImplementsInterface()
	{
		$this->assertSame(
			array(
				'Lohini\Tests\Iterators\Mocks\Foo_4',
				'Lohini\Tests\Iterators\Mocks\Foo_5',
				'Lohini\Tests\Iterators\Mocks\Foo_6'
				),
			array_values($this->iterator->isSubclassOf('Lohini\Tests\Iterators\Mocks\Bar_2')->getResult())
			);
	}

	public function testIsInNamespace()
	{
		$iterator=new TypeIterator(new \ArrayIterator(array(
			'Lohini\Tests\Iterators\Mocks\Bar_1',
			'Lohini\Tests\Iterators\Mocks\Bar_2',
			'Lohini\Tests\Iterators\Mocks\Foo_1',
			'Lohini\Tests\Iterators\Mocks\Foo_2',
			'Lohini\Tests\Iterators\Mocks\Foo_3',
			'Lohini\Tests\Iterators\Mocks\Foo_4',
			'Lohini\Tests\Iterators\Mocks\Foo_5',
			'Lohini\Tests\Iterators\Mocks\Foo_6',
			'Lohini\Tests\Iterators\Mocks\Foo\Bar',
			'Lohini\Tests\Iterators\Mocks\Foo\Foo\Bar',
			)));
	}
}


namespace Lohini\Tests\Iterators\Mocks;

	interface Bar_1 { }
	interface Bar_2 extends Bar_1 { }

	abstract class Foo_1 { }
	class Foo_2 extends Foo_1 { }

	class Foo_3 implements Bar_1 { }
	class Foo_4 implements Bar_2 { }
	abstract class Foo_5 implements Bar_2 { }
	class Foo_6 extends Foo_5 { }


namespace Lohini\Tests\Iterators\Mocks\Foo;
	class Bar { }


namespace Lohini\Tests\Iterators\Mocks\Foo\Foo;
	class Bar {}
