<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Application;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Application\PresenterManager,
	Nette\Utils\Strings;

/**
 */
class PresenterManagerTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Application\PresenterManager */
	private $manager;


	public function setup()
	{
		$pm=new \Lohini\Packages\PackageManager;
		$pm->setActive($this->getPackages());

		$container=clone $this->getContext();
		$container->addService('templateFactory', (object)NULL);
		$container->parameters['productionMode']=TRUE;

		$this->manager=new PresenterManager(
			$this->getContext()->expand('%appDir%'),
			$container,
			$pm
			);
	}

	/**
	 * @return \Lohini\Packages\PackagesContainer
	 */
	private function getPackages()
	{
		return new \Lohini\Packages\PackagesContainer(array_merge(
			\Lohini\Core::getDefaultPackages(),
			array(
				'Lohini\Tests\Application\Mocks\Bar\Package',
				'Lohini\Tests\Application\Mocks\Baz\Package',
				'Lohini\Tests\Application\Mocks\Foo\Package',
				)
			));
	}

	/**
	 * @return array
	 */
	public function dataPackagePresentersFormats()
	{
		return array(
			array('Lohini\Tests\Application\Mocks\Foo\Presenter\FooPresenter', 'FooPackage:Foo'),
			array('Lohini\Tests\Application\Mocks\Foo\Presenter\BarPresenter', 'FooPackage:Bar'),
			array('Lohini\Tests\Application\Mocks\Bar\Presenter\FooPresenter', 'BarPackage:Foo'),
			array('Lohini\Tests\Application\Mocks\Bar\Presenter\BarPresenter', 'BarPackage:Bar'),
			array('Lohini\Tests\Application\Mocks\Bar\Presenter\FooFooPresenter', 'BarPackage:FooFoo'),
			array('Lohini\Tests\Application\Mocks\Bar\Presenter\FooModule\FooBarPresenter', 'BarPackage:Foo:FooBar')
			);
	}

	/**
	 * @dataProvider dataPackagePresentersFormats
	 *
	 * @param string $class
	 * @param string $expected
	 */
	public function testFormatNameFromPackageClass($class, $expected)
	{
		$this->assertEquals($expected, $this->manager->formatPackagePresenterFromClass($class));
	}

	/**
	 * @return array
	 */
	public function dataServiceNamesAndPresenters()
	{
		return array(
			array('FooPackage:Foo', 'fooPackage.fooPresenter'),
			array('BarPackage:FooFoo', 'barPackage.fooFooPresenter'),
			array('BarPackage:Foo:FooBar', 'barPackage.foo.fooBarPresenter')
			);
	}

	/**
	 * @dataProvider dataServiceNamesAndPresenters
	 *
	 * @param string $presenterName
	 * @param string $serviceName
	 */
	public function testServiceNameFormating($presenterName, $serviceName)
	{
		$this->assertEquals($serviceName, $this->manager->formatServiceNameFromPresenter($presenterName), 'Formating service name from presenter name');
		$this->assertEquals($presenterName, $this->manager->formatPresenterFromServiceName($serviceName), 'Formating presenter name from service name');
	}

	/**
	 * @return array
	 */
	public function dataPackagePresenters()
	{
		return array(
			array('Lohini\Tests\Application\Mocks\Foo\Presenter\FooPresenter', 'FooPackage:Foo'),
			array('Lohini\Tests\Application\Mocks\Foo\Presenter\BarPresenter', 'FooPackage:Bar'),
			array('Lohini\Tests\Application\Mocks\Bar\Presenter\FooPresenter', 'BarPackage:Foo'),
			array('Lohini\Tests\Application\Mocks\Bar\Presenter\BarPresenter', 'BarPackage:Bar'),
			array('Lohini\Tests\Application\Mocks\Foo\Presenter\BarModule\BarBarPresenter', 'FooPackage:Bar:BarBar')
			);
	}

	/**
	 * @dataProvider dataPackagePresenters
	 *
	 * @param string $class
	 * @param string $name
	 */
	public function testCreatePresenterFromPackageUsingContainer($class, $name)
	{
		$pm=new \Lohini\Packages\PackageManager;
		$pm->setActive($this->getPackages());

		$manager=new PresenterManager(
			$this->getContext()->expand('%appDir%'),
			$this->createContainerWithPresenters(),
			$pm
			);

		$this->assertInstanceof($class, $manager->createPresenter($name));
	}

	/**
	 * @return \Lohini\DI\Container
	 */
	private function createContainerWithPresenters()
	{
		$presenters=array(
			'Lohini\Tests\Application\Mocks\Foo\Presenter\FooPresenter' => 'FooPackage:Foo',
			'Lohini\Tests\Application\Mocks\Foo\Presenter\BarPresenter' => 'FooPackage:Bar',
			'Lohini\Tests\Application\Mocks\Bar\Presenter\FooPresenter' => 'BarPackage:Foo',
			'Lohini\Tests\Application\Mocks\Bar\Presenter\BarPresenter' => 'BarPackage:Bar',
			'Lohini\Tests\Application\Mocks\Foo\Presenter\BarModule\BarBarPresenter' => 'FooPackage:Bar:BarBar',
			);

		$container=clone $this->getContext();
		$container->addService('templateFactory', (object)NULL);
		foreach ($presenters as $presenterClass => $presenter) {
			$serviceName=$this->manager->formatServiceNameFromPresenter($presenter);
			$container->addService($serviceName, new $presenterClass($container));
			}

		$container->parameters['productionMode']=TRUE;
		return $container;
	}

	/**
	 * @dataProvider dataPackagePresenters
	 *
	 * @param string $class
	 * @param string $name
	 */
	public function testCreatePresenterFromPackageUsingClassGuessing($class, $name)
	{
		$this->assertInstanceof($class, $this->manager->createPresenter($name));
	}

	/**
	 * @expectedException \Lohini\Application\InvalidPresenterException
	 * @expectedExceptionCode 1
	 */
	public function testGetPresenterClassForInvalidNameException()
	{
		$name=' '.Strings::random();
		$this->manager->getPresenterClass($name);
	}

	/**
	 * @expectedException \Lohini\Application\InvalidPresenterException
	 * @expectedExceptionCode 2
	 */
	public function testGetPresenterClassImplementsInterfaceException()
	{
		$name='FooPackage:Fake';
		$this->manager->getPresenterClass($name);
	}

	/**
	 * @expectedException \Lohini\Application\InvalidPresenterException
	 * @expectedExceptionCode 3
	 */
	public function testGetPresenterClassAbstractException()
	{
		$name='FooPackage:Abstract';
		$this->manager->getPresenterClass($name);
	}

	/**
	 * @expectedException \Lohini\Application\InvalidPresenterException
	 * @expectedExceptionCode 4
	 */
	public function testGetPresenterClassCaseSensitiveException()
	{
		$name='FooPackage:homepage';

		$this->manager->caseSensitive=TRUE;
		$this->manager->getPresenterClass($name);
	}

	/**
	 * @expectedException \Lohini\Application\InvalidPresenterException
	 * @expectedExceptionCode 5
	 */
	public function testGetPresenterClassMissingException()
	{
		$name='BarPackage:MissingPresenter'.Strings::random();
		$this->manager->getPresenterClass($name);
	}
}


/** Bar package simulation */
namespace Lohini\Tests\Application\Mocks\Bar;
class Package extends \Lohini\Packages\Package
{
}

namespace Lohini\Tests\Application\Mocks\Bar\Presenter;
class FooPresenter extends \Lohini\Application\UI\Presenter
{
}

class BarPresenter extends \Lohini\Application\UI\Presenter
{
}

/** Foo package simulation */
namespace Lohini\Tests\Application\Mocks\Foo;
class Package extends \Lohini\Packages\Package
{
}

namespace Lohini\Tests\Application\Mocks\Foo\Presenter;
class FooPresenter extends \Lohini\Application\UI\Presenter
{
}

class BarPresenter extends \Lohini\Application\UI\Presenter
{
}

class HomepagePresenter extends \Lohini\Application\UI\Presenter
{
}

abstract class AbstractPresenter extends \Lohini\Application\UI\Presenter
{
}

class FakePresenter
{
}

namespace Lohini\Tests\Application\Mocks\Foo\Presenter\BarModule;
class BarBarPresenter extends \Lohini\Application\UI\Presenter
{
}

/** Bar package simulation */
namespace Lohini\Tests\Application\Mocks\Baz;
class Package extends \Lohini\Packages\Package
{
}

namespace Lohini\Tests\Application\Mocks\Baz\Presenter;
class FooPresenter extends \Lohini\Application\UI\Presenter
{
}

class BarPresenter extends \Lohini\Application\UI\Presenter
{
}
