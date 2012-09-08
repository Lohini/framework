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

use Lohini\Database\Migrations\VersionDatetime;

/**
 */
class MigrationLogTest
extends \Lohini\Testing\TestCase
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
	 * @return \Lohini\Database\Migrations\Version|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockVersion()
	{
		return $this->getMockBuilder('Lohini\Database\Migrations\Version')
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCreation()
	{
		$package=$this->mockPackage();
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($oldTime=VersionDatetime::from('20120116140000')));

		$version=$this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($newTime=VersionDatetime::from('20120116150000')));

		$log=new \Lohini\Database\Migrations\MigrationLog($package, $version);

		$this->assertLessThanOrEqual(new \DateTime, $log->getDate()); // paranoia
		$this->assertSame($package, $log->getPackage());
		$this->assertEquals($newTime, $log->getVersion());
		$this->assertTrue($log->isUp());
	}
}
