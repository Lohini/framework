<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Migrations;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Migrations\History;

/**
 */
class HistoryTest
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
	 * @param string $version
	 * @param string $package
	 * @return string
	 */
	private function migrationClass($version, $package='Shop')
	{
		return __NAMESPACE__.'\Fixtures\\'.$package.'\Migration\Version'.$version;
	}

	/**
	 * @return array
	 */
	public function dataShopMigrations()
	{
		return array(
			'20120116140000' => $this->migrationClass('20120116140000'),
			'20120116150000' => $this->migrationClass('20120116150000'),
			'20120116160000' => $this->migrationClass('20120116160000'),
			);
	}

	/**
	 * @param \Lohini\Database\Migrations\PackageVersion $package
	 * @param int $current
	 * @return History
	 */
	private function createShopHistory($package=NULL, $current=0)
	{
		$history=new History($package ?: $this->mockPackage(), $current);
		foreach ($this->dataShopMigrations() as $migration) {
			$history->add($migration);
			}
		return $history;
	}

	public function testHistoryProvidesVersions()
	{
		$history=$this->createShopHistory();

		$this->assertCount(3, $versions=$history->toArray());
		$this->assertContainsOnly('Lohini\Database\Migrations\Version', $versions);

		$this->assertCount(3, $versions=$history->getIterator()->getArrayCopy());
		$this->assertContainsOnly('Lohini\Database\Migrations\Version', $versions);
	}

	public function testHistoryProvidesPackage()
	{
		$history=$this->createShopHistory();
		$this->assertInstanceOf('Lohini\Database\Migrations\PackageVersion', $history->getPackage());
	}

	public function testFreshHistoryIsNotUpToDate()
	{
		$history=$this->createShopHistory($package=$this->mockPackage());
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue(0));

		$this->assertFalse($history->isUpToDate());
	}

	public function testAdd_VersionsAreSortedWhenAdded()
	{
		$history=new History($this->mockPackage(), 0);
		$migrations=$this->dataShopMigrations();

		$this->assertInstanceOf('Lohini\Database\Migrations\Version', $version=$history->add(end($migrations)));
		$this->assertInstanceOf('Lohini\Database\Migrations\Version', $version=$history->add(reset($migrations)));

		$this->assertCount(2, $versions=$history->toArray());
		$this->assertEquals('20120116140000', (string)array_shift($versions)->getVersion());
		$this->assertEquals('20120116160000', (string)array_shift($versions)->getVersion());
	}

	public function testAdd_HistoryIsPassedToVersion()
	{
		$history=new History($this->mockPackage(), 0);
		$migration=current($this->dataShopMigrations());

		$version=$history->add($migration);
		$this->assertSame($history, $version->getHistory());
	}

	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testAdd_VersionMustBeUniqueException()
	{
		$history=new History($this->mockPackage(), 0);
		$migration=current($this->dataShopMigrations());

		$history->add($migration);
		$history->add($migration);
	}
}
