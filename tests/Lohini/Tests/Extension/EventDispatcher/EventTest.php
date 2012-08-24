<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\EventDispatcher;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\EventDispatcher\Event;

/**
 */
class EventTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return FooMock
	 */
	public function dataDispatch()
	{
		$foo=new FooMock;
		$foo->onBar=new Event('bar');
		$foo->onBar[]=function($lorem) { echo $lorem; };
		$foo->onBar[]=function($lorem) { echo $lorem+1; };

		return $foo;
	}

	public function testDispatch_Method()
	{
		ob_start();
		$foo=$this->dataDispatch();
		$foo->onBar->dispatch(array(10));
		$this->assertSame('1011', ob_get_clean());
	}

	public function testDispatch_Invoke()
	{
		ob_start();
		try {
			$foo=$this->dataDispatch();
			$foo->onBar(15);
			$this->assertSame('1516', ob_get_clean());
			}
		catch (\Nette\MemberAccessException $e) {
			ob_end_clean();
			$this->markTestSkipped('Nette Framework issue: https://github.com/nette/nette/issues/730');
		}
	}

	/**
	 */
	public function testDispatch_toManager()
	{
		// create
		$evm=new \Lohini\Extension\EventDispatcher\EventManager;
		$foo=new FooMock;
		$foo->onMagic=new Event('onMagic', $evm);

		// register
		$evm->addEventSubscriber(new LoremListener());
		$foo->onMagic[]=function(FooMock $foo, $int) { echo $int*3; };

		ob_start();
		$foo->onMagic($foo, 2);
		$this->assertSame('64', ob_get_clean());


		ob_start();
		$foo->onMagic->dispatch(array($foo, 2));
		$this->assertSame('64', ob_get_clean());
	}
}


/**
 * @method onBar($lorem)
 * @method onMagic(FooMock $foo, $int)
 */
class FooMock
extends \Nette\Object
{
	/** @var array|callable[]|Event */
	public $onBar=array();
	/**  @var array|callable[]|Event */
	public $onMagic=array();
}

/**
 */
class LoremListener
extends \Nette\Object
implements \Lohini\Extension\EventDispatcher\EventSubscriber
{
	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			'onMagic'
			);
	}

	/**
	 * @param FooMock $foo
	 * @param $int
	 */
	public function onMagic(FooMock $foo, $int)
	{
		echo $int*2;
	}
}
