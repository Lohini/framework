<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Packages;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class PackageManagerTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Packages\PackageManager */
	private $manager;


	public function setup()
	{
		$this->manager=new \Lohini\Packages\PackageManager;
		$this->manager->setActive($this->getPackages());
	}

	/**
	 * @return \Lohini\Packages\PackagesContainer
	 */
	private function getPackages()
	{
		return new \Lohini\Packages\PackagesContainer(array(
			'Lohini\Tests\Package\Fixtures\Bar\Package',
			'Lohini\Tests\Package\Fixtures\Foo\Package'
			));
	}

	public function testSetActive()
	{
		$this->manager->setActive($this->getPackages());
		$this->assertInstanceOf('Lohini\Tests\Package\Fixtures\Bar\Package', $this->manager->getPackage('Bar'));
		$this->assertInstanceOf('Lohini\Tests\Package\Fixtures\Foo\Package', $this->manager->getPackage('Foo'));
	}

    public function testIsClassInActivePackage()
    {
		$this->assertTrue($this->manager->isClassInActivePackage('Lohini\Tests\Package\Fixtures\Bar\Entity\Dog'));
		$this->assertFalse($this->manager->isClassInActivePackage('Lohini\Tests\Package\Fixtures\Bar\Entity\Cat'));
		$this->assertFalse($this->manager->isClassInActivePackage('Lohini\Dog'));
    }

	/**
	 * @expectedException \Nette\InvalidArgumentException
	 * @expectedExceptionMessage A resource name must start with @ ('word' given).
	 */
	public function testLocateResource_DoesNotStartWithAtException()
	{
		$this->manager->locateResource('word');
	}

	/**
	 * @expectedException \Nette\InvalidArgumentException
	 * @expectedExceptionMessage File name '@word/../lorem' contains invalid characters (..).
	 */
	public function testLocateResource_ContainsDoubleDotException()
	{
		$this->manager->locateResource('@word/../lorem');
	}

	/**
	 * @return array
	 */
	public function dataLocateResource()
	{
		$foo=realpath(__DIR__.'/../Package/Fixtures/Foo');
		$bar=realpath(__DIR__.'/../Package/Fixtures/Bar');

		return array(
			array('@Bar/public/css/bar.css', $bar.'/Resources/public/css/bar.css'),
			array('@Bar/public/css/lipsum.css', $bar.'/public/css/lipsum.css'),
			array('@Foo/public/css/lorem.css', $foo.'/Resources/public/css/lorem.css'),
		);
	}

	/**
	 * @dataProvider dataLocateResource
	 *
	 * @param $path
	 * @param $expected
	 */
	public function testLocateResource($path, $expected)
	{
		$this->assertEquals($expected, $this->manager->locateResource($path));
	}

	/**
	 * @expectedException \Nette\InvalidArgumentException
	 * @expectedExceptionMessage Unable to find file '@Foo/public/js/plugin.js'
	 */
	public function testLocateResource_NonExistingFileException()
	{
		$this->manager->locateResource('@Foo/public/js/plugin.js');
	}
}
