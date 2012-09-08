<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Iterators;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Iterators\SelectIterator;

/**
 */
class SelectIteratorTest
extends \Lohini\Testing\TestCase
{
	/** @var SelectIterator */
	private $iterator;


	protected function setUp()
	{
		$this->iterator=new SelectIterator(new \ArrayIterator(range(1, 100)));
	}

	public function testFilteringWithOneFilter()
	{
		$result=$this->iterator
			->select(function(SelectIterator $iterator) { return $iterator->current()<=10; })
			->toArray();

		$this->assertSame(range(1,10), array_values($result));
	}

	public function testFilteringWithMultipleFilters()
	{
		$result=$this->iterator
			->select(function(SelectIterator $iterator) { return $iterator->current()<=10; })
			->select(function(SelectIterator $iterator) { return $iterator->current()%2==0; })
			->toArray();

		$this->assertSame(range(2,10,2), array_values($result));
	}
}
