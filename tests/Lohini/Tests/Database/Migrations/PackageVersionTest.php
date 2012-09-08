<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Migrations;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Migrations\PackageVersion,
	Lohini\Database\Migrations\VersionDatetime;

/**
 */
class PackageVersionTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return \Lohini\Packages\Package|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockPackage()
	{
		return $this->getMockBuilder('Lohini\Packages\Package')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @param PackageVersion $package
	 * @return \Lohini\Database\Migrations\History|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockHistory(PackageVersion $package=NULL)
	{
		$history=$this->getMockBuilder('Lohini\Database\Migrations\History')
			->disableOriginalConstructor()
			->getMock();

		if ($package) {
			$history->expects($this->atLeastOnce())
				->method('getPackage')
				->will($this->returnValue($package));
			}

		return $history;
	}

	/**
	 * @return \Lohini\Database\Migrations\Version|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockVersion()
	{
		return $this->getMockBuilder('Lohini\Database\Migrations\Version')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @param PackageVersion $package
	 * @param $version
	 */
	private function setMigrationVersion(PackageVersion $package, $version)
	{
		$propRefl=$package->getReflection()->getProperty('migrationVersion');
		$propRefl->setAccessible(TRUE);
		$propRefl->setValue($package, $version);
	}

	public function testCreation_Defaults()
	{
		$package=new PackageVersion(new Fixtures\Shop\Package);
		$this->assertEquals('Shop', $package->getName());
		$this->assertEquals(__NAMESPACE__.'\Fixtures\Shop\Package', $package->getClassName());
		$this->assertEquals(0, $package->getMigrationVersion());
		$this->assertCount(0, $package->getMigrationsLog());
		$this->assertLessThanOrEqual(new \DateTime, $package->getLastUpdate()); // paranoia
		$this->assertEquals(PackageVersion::STATUS_PRESENT, $package->getStatus());
	}

	public function testSetStatus()
	{
		$package=new PackageVersion($this->mockPackage());

		$package->setStatus(PackageVersion::STATUS_INSTALLED);
		$this->assertEquals(PackageVersion::STATUS_INSTALLED, $package->getStatus());

		$package->setStatus(PackageVersion::STATUS_PRESENT);
		$this->assertEquals(PackageVersion::STATUS_PRESENT, $package->getStatus());
	}

	/**
	 * @expectedException \Nette\InvalidArgumentException
	 * @expectedExceptionMessage Invalid PackageVersion status 'undefined' was given.
	 */
	public function testSetStatus_InvalidStatusException()
	{
		$package=new PackageVersion($this->mockPackage());
		$package->setStatus('undefined');
	}

	public function testCreateHistory()
	{
		$package=new PackageVersion($this->mockPackage());
		$this->assertInstanceOf('Lohini\Database\Migrations\History', $history=$package->createHistory());
		$this->assertSame($package, $history->getPackage());
	}

	public function testSetVersion_WhenSettingTheSameVersionNothingHappens()
	{
		$package=new PackageVersion($this->mockPackage());
		$this->setMigrationVersion($package, $time=VersionDatetime::from('20120116140000'));
		$lastUpdate=$package->getLastUpdate();

		$version=$this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($time));

		$package->setVersion($version);

		$this->assertSame($lastUpdate, $package->getLastUpdate());
	}

	public function testSetVersion_SettingDifferentVersion()
	{
		$package=new PackageVersion($this->mockPackage());
		$this->setMigrationVersion($package, VersionDatetime::from('20120116140000'));

		$version=$this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($newTime=VersionDatetime::from('20120116150000')));
		$version->expects($this->atLeastOnce())
			->method('getHistory')
			->will($this->returnValue($history=$this->mockHistory($package)));

		$package->setVersion($version);

		$this->assertEquals($newTime, $package->getMigrationVersion());
		$this->assertCount(1, $log=$package->getMigrationsLog());

		$this->assertInstanceOf('Lohini\Database\Migrations\MigrationLog', $event=reset($log));
	}

	/**
	 * @expectedException \Lohini\Database\Migrations\MigrationException
	 */
	public function testSetVersion_VersionNotAttachedToPackageException()
	{
		$package=new PackageVersion($this->mockPackage());
		$this->setMigrationVersion($package, VersionDatetime::from('20120116140000'));

		$version=$this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($newTime=VersionDatetime::from('20120116150000')));
		$version->expects($this->atLeastOnce())
			->method('getHistory')
			->will($this->returnValue($history=$this->mockHistory(new PackageVersion($this->mockPackage()))));

		$package->setVersion($version);
	}
}
