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

use Lohini\Database\Migrations\Version,
	Lohini\Database\Migrations\VersionDatetime;

/**
 */
class VersionTest
extends \Lohini\Testing\OrmTestCase
{
	/**
	 * @return \Lohini\Database\Migrations\PackageVersion|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockPackage()
	{
		return $this->getMockBuilder('Lohini\Database\Migrations\PackageVersion')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @param \Lohini\Database\Migrations\PackageVersion $package
	 * @return \Lohini\Database\Migrations\History|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockHistory(\Lohini\Database\Migrations\PackageVersion $package=NULL)
	{
		$history=$this->getMockBuilder('Lohini\Database\Migrations\History')
			->disableOriginalConstructor()
			->getMock();

		if ($package){
			$history->expects($this->once())
				->method('getPackage')
				->will($this->returnValue($package));
			}

		return $history;
	}

	/**
	 * @param string $version
	 * @param string $package
	 * @return string
	 */
	private function migrationClass($version, $package='Shop')
	{
		return __NAMESPACE__.'\Fixtures\\'.$package.'\Migration\Version'.$version;
	}

	public function testCreation()
	{
		$version=new Version($history=$this->mockHistory(), $class=$this->migrationClass($time=VersionDatetime::from('20120116140000')));
		$this->assertSame($history, $version->getHistory());
		$this->assertEquals($class, $version->getClass());
		$this->assertEquals($time, $version->getVersion());
		$this->assertEquals(0, $version->getTime());
	}

	public function testIsMigrated_WhenEquals()
	{
		$package=$this->mockPackage();
		$version=new Version($this->mockHistory($package), $this->migrationClass($time=VersionDatetime::from('20120116150000')));
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($time));

		$this->assertTrue($version->isMigrated());
	}

	public function testIsMigrated_WhenLess()
	{
		$package=$this->mockPackage();
		$version=new Version($this->mockHistory($package), $this->migrationClass($time=VersionDatetime::from('20120116150000')));
		$time=clone $time;
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($time->modify('+1 second')));

		$this->assertTrue($version->isMigrated());
	}

	public function testIsNotMigrated_WhenBigger()
	{
		$package=$this->mockPackage();
		$version=new Version($this->mockHistory($package), $this->migrationClass($time=VersionDatetime::from('20120116150000')));
		$time=clone $time;
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($time->modify('-1 second')));

		$this->assertFalse($version->isMigrated());
	}

	public function testIsReversible_WhenDownMethodIsImplemented()
	{
		$version=new Version($this->mockHistory(), $this->migrationClass('20120116140000'));
		$this->assertTrue($version->isReversible());
	}

	public function testIsNotReversible_WhenDownMethodIsNotImplemented()
	{
		$version=new Version($this->mockHistory(), $this->migrationClass('20120116150000'));
		$this->assertFalse($version->isReversible());
	}

	public function testAddingSql()
	{
		$version=new Version($this->mockHistory(), $this->migrationClass('20120116150000'));
		$version->addSql($sql1="INSERT INTO user ('admin')");
		$version->addSql($sql2='INSERT INTO user (?)', array('admin'), array('string'));

		$this->assertEquals(
			array(
				array($sql1, array(), array()),
				array($sql2, array('admin'), array('string')),
				),
			$version->getSql()
			);
	}

	public function testMarkMigrated()
	{
		$version=new Version($history=$this->mockHistory(), $this->migrationClass('20120116150000'));
		$history->expects($this->once())
			->method('setCurrent')
			->with($this->equalTo($version));

		$version->markMigrated(TRUE);
	}

	public function testGetNextAndPrevious()
	{
		$history=new \Lohini\Database\Migrations\History($this->mockPackage(), NULL);
		$first=$history->add($this->migrationClass('20120116140000'));
		$second=$history->add($this->migrationClass('20120116150000'));
		$third=$history->add($this->migrationClass('20120116160000'));

		$this->assertNull($first->getPrevious());
		$this->assertSame($first, $second->getPrevious());
		$this->assertSame($third, $second->getNext());
		$this->assertNull($third->getNext());
	}
}
