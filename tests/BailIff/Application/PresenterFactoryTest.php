<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIffTesting\Application;


class PresenterFactoryTest
extends \PHPUnit_Framework_TestCase
{
	/** @var \Nette\DI\Container */
	private $context;
	/** @var \BailIff\Application\PresenterFactory */
	private $factory;


	public function setUp()
	{
		$this->context=new \Nette\DI\Container;
		$this->factory=new \BailIff\Application\PresenterFactory($this->context);
	}

	public function testInterface()
	{
		$this->assertInstanceOf('\Nette\Application\IPresenterFactory', $this->factory);
	}

	public function providerPresenterClassPrefix()
	{
		return array(
			array('Foo', 'App\FooPresenter'),
			array('Foo:Bar', 'App\FooModule\BarPresenter'),
			array('Foo:Bar:Baz', 'App\FooModule\BarModule\BazPresenter'),
			array('Foo', 'BailIff\Presenters\FooPresenter', 'fw'),
			array('Foo:Bar', 'BailIff\Presenters\Foo\BarPresenter', 'fw'),
			array('Foo:Bar:Baz', 'BailIff\Presenters\Foo\Bar\BazPresenter', 'fw'),
			array('Foo', 'FooPresenter', 'oof'),
			array('Foo:Bar', 'Foo\BarPresenter', 'oof'),
			array('Foo:Bar:Baz', 'Foo\Bar\BazPresenter', 'oof'),
			);
	}

	/**
	 * @dataProvider providerPresenterClassPrefix
	 */
	public function testFormatPresenterClass($presenter, $class, $prefix=NULL)
	{
		if ($prefix===NULL) {
			$this->assertEquals($class, $this->factory->formatPresenterClass($presenter), "->formatPresenterClass('$presenter')");
			}
		else {
			$this->assertEquals($class, $this->factory->formatPresenterClass($presenter, $prefix), "->formatPresenterClass('$presenter', '$prefix')");
			}
	}

	/**
	 * @dataProvider providerPresenterClassPrefix
	 */
	public function testUnformatPresenterClass($presenter, $class)
	{
		$this->assertEquals($presenter, $this->factory->unformatPresenterClass($class), "->unformatPresenterClass('$class')");
	}

	/**
	 * @dataProvider providerPresenterClassPrefix
	 */
	public function testGetPresenterClass($presenter, $class)
	{
//		$this->assertEquals($class, $this->factory->getPresenterClass($presenter), "->getPresenterClass('$presenter')");
	}

	/**
	 * @expectedException \BailIff\Application\InvalidPresenterException
	 * @expectedExceptionCode 1
	 */
	public function testGetPresenterClassInvalidNameException1()
	{
		$name=NULL;
		$this->factory->getPresenterClass($name);
	}

	/**
	 * @expectedException \BailIff\Application\InvalidPresenterException
	 * @expectedExceptionCode 1
	 */
	public function testGetPresenterClassInvalidNameException2()
	{
		$name=' Invalid';
		$this->factory->getPresenterClass($name);
	}

	/**
	 * @expectedException \BailIff\Application\InvalidPresenterException
	 * @expectedExceptionCode 1
	 */
	public function testGetPresenterClassInvalidNameException3()
	{
		$name=1;
		$this->factory->getPresenterClass($name);
	}

	/**
	 * @expectedException \BailIff\Application\InvalidPresenterException
	 * @expectedExceptionCode 2
	 */
	public function testGetPresenterClassNotImplementorException()
	{
		$name='Unimplemented';
		$this->factory->getPresenterClass($name);
	}

	/**
	 * @expectedException \BailIff\Application\InvalidPresenterException
	 * @expectedExceptionCode 3
	 */
	public function testGetPresenterClassAbstractExceptionException()
	{
		$name='Abstract';
		$this->factory->getPresenterClass($name);
	}

	/**
	 * @expectedException \BailIff\Application\InvalidPresenterException
	 * @expectedExceptionCode 4
	 */
	public function testGetPresenterClassCaseMismatchException()
	{
		$name='Foo:bar';
		$this->factory->getPresenterClass($name);
	}

	/**
	 * @expectedException \BailIff\Application\InvalidPresenterException
	 * @expectedExceptionCode 5
	 */
	public function testGetPresenterClassNotFoundException()
	{
		$name='Foo:Baz';
		$this->factory->getPresenterClass($name);
	}

	 /**
	 * @dataProvider providerPresenterClassPrefix
	 */
	public function testCreatePresenter($presenter, $class, $prefix='prefix')
	{
		if ($prefix) {
			return;
			}
		$this->assertInstanceOf($class, $this->factory->createPresenter($presenter), "->createPresenter('$presenter')");
	}

	/**
	 * @dataProvider providerPresenterClassPrefix
	 */
	public function testCreatedPresenterHasContext($presenter)
	{
		$instance=$this->factory->createPresenter($presenter);
		$this->assertSame($this->context, $instance->getContext());
	}
}
