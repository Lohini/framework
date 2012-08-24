<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\DI;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class FactoryGeneratorExtensionTest
extends \Lohini\Testing\TestCase
{
	public function testFunctionality()
	{
		$container=$this->createContainer(
			__DIR__.'/files/factories.neon',
			array( 'dicFactories' => new \Lohini\Extension\DI\FactoryGeneratorExtension)
			);

		/** @var Foo $foo */
		$foo=$container->getService('foo');
		$this->assertInstanceOf(__NAMESPACE__.'\\Foo', $foo);
		$this->assertInstanceOf(__NAMESPACE__.'\\IBarFactory', $foo->factory);

		/** @var Bar $bar */
		$bar=$foo->factory->create();
		$this->assertInstanceOf(__NAMESPACE__.'\\Bar', $bar);
		$this->assertInstanceOf('Nette\Application\Application', $bar->app);
	}
}


/**
 */
class Foo
extends \Nette\Object
{
	/** @var IBarFactory */
	public $factory;


	/**
	 * @param IBarFactory $factory
	 */
	public function __construct(IBarFactory $factory)
	{
		$this->factory=$factory;
	}
}


/**
 */
class Bar
extends \Nette\Object
{
	/** @var \Nette\Application\Application */
	public $app;


	/**
	 * @param \Nette\Application\Application $app
	 */
	public function __construct(\Nette\Application\Application $app)
	{
		$this->app=$app;
	}
}
