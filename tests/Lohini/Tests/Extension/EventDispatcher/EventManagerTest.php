<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\EventDispatcher;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\EventDispatcher\EventManager;

require_once __DIR__.'/EventListenerMock.php';

/**
 */
class EventManagerTest
extends \Lohini\Testing\TestCase
{
	/** @var EventManager */
	private $manager;
	/** @var EventListenerMock|\PHPUnit_Framework_MockObject_MockObject */
	private $listener;


	public function setUp()
	{
		$this->manager=new EventManager;
		$this->listener=$this->getMockBuilder(__NAMESPACE__.'\EventListenerMock')
				->setMethods(array('onFoo', 'onBar'))
				->getMock();
	}

	public function testListenerHasRequiredMethod()
	{
		$this->manager->addEventListener('onFoo', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertSame(array($this->listener), $this->manager->getListeners());
	}

	public function testRemovingListenerFromSpecificEvent()
	{
		$this->manager->addEventListener('onFoo', $this->listener);
		$this->manager->addEventListener('onBar', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$this->manager->removeEventListener('onFoo', $this->listener);
		$this->assertFalse($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));
	}

	public function testRemovingListenerCompletely()
	{
		$this->manager->addEventListener('onFoo', $this->listener);
		$this->manager->addEventListener('onBar', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$this->manager->removeEventListener($this->listener);
		$this->assertFalse($this->manager->hasListeners('onFoo'));
		$this->assertFalse($this->manager->hasListeners('onBar'));
		$this->assertSame(array(), $this->manager->getListeners());
	}

	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testListenerDontHaveRequiredMethodException()
	{
		$this->manager->addEventListener('onNonexisting', $this->listener);
	}

	public function testDispatching()
	{
		$this->manager->addEventSubscriber($this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$eventArgs=new EventArgsMock;

		$this->listener->expects($this->once())
			->method('onFoo')
			->with($this->equalTo($eventArgs));

		$this->listener->expects($this->never())
			->method('onBar');

		$this->manager->dispatchEvent('onFoo', $eventArgs);
	}
}
