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

use Lohini\Packages;
use Lohini\Database\Migrations\MigrationsManager;

/**
 */
class MigrationsManagerTest
extends \Lohini\Testing\OrmTestCase
{
	/**
	 * @return \Lohini\Packages\PackageManager|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockPackageManager()
	{
		return $this->getMockBuilder('Lohini\Packages\PackageManager')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @return \Symfony\Component\Console\Output\OutputInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockConsoleOutput()
	{
		$output=$this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$output->expects($this->atLeastOnce())
			->method('writeln');

		return $output;
	}

	/**
	 * @return \Lohini\Packages\PackageManager
	 */
	private function preparePackageManager()
	{
		$packageManager=new Packages\PackageManager;
		$packagesList=new Packages\DirectoryPackages(__DIR__.'/Fixtures', __NAMESPACE__.'\Fixtures');
		$packageManager->setActive(new Packages\PackagesContainer($packagesList));

		return $packageManager;
	}

	/**
	 * @param string $version
	 * @param string $package
	 * @return string
	 */
	private function migrationClass($version, $package='Blog')
	{
		return __NAMESPACE__.'\Fixtures\\'.$package.'\Migration\Version'.$version;
	}

	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Lohini\Database\Migrations\PackageVersion',
			));
	}

	public function testOutputWriter_ProvidesDefault()
	{
		$manager=new MigrationsManager($this->getDoctrine(), $this->mockPackageManager());
		$this->assertInstanceOf('Symfony\Component\Console\Output\OutputInterface', $manager->getOutputWriter());
	}

	public function testOutputWriter_CanBeReplaced()
	{
		$manager=new MigrationsManager($this->getDoctrine(), $this->mockPackageManager());

		$manager->setOutputWriter($writer=new \Symfony\Component\Console\Output\ConsoleOutput);
		$this->assertSame($writer, $manager->getOutputWriter());
	}

	public function testGetConnection()
	{
		$manager=new MigrationsManager($this->getDoctrine(), $this->mockPackageManager());
		$this->assertSame($this->getDoctrine()->getConnection(), $manager->getConnection());
	}

	public function testGetPackageVersion_AutomaticallyPersistNewlyCreatedEntity()
	{
		$manager=new MigrationsManager($this->getDoctrine(), $packages=$this->mockPackageManager());
		$packages->expects($this->once())
			->method('getPackage')
			->with($this->equalTo('Blog'))
			->will($this->returnValue(new Fixtures\Shop\Package));

		$package=$manager->getPackageVersion('Blog');
		$this->assertInstanceOf('Lohini\Database\Migrations\PackageVersion', $package);
		$this->assertNotNull($package->getId());
	}

	public function testGetPackageHistory()
	{
		$manager=new MigrationsManager($this->getDoctrine(), $packages=$this->mockPackageManager());
		$packages->expects($this->atLeastOnce())
			->method('getPackage')
			->with($this->equalTo('Blog'))
			->will($this->returnValue(new Fixtures\Blog\Package));

		$history=$manager->getPackageHistory('Blog');
		$this->assertCount(4, $history->toArray());
	}

	public function testInstallAndUninstall()
	{
		$manager=new MigrationsManager($doctrine=$this->getDoctrine(), $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// should migrate till now
		$history=$manager->getPackageHistory('Blog');
		$history->migrate($manager, '20120116160000');
		$this->assertEquals('20120116160000', (string)$history->getCurrent()->getVersion());

		$this->assertEquals(
			array(
				array('content' => 'trains are cool', 'title' => 'trains'),
				array('content' => 'cars are way more cool!', 'title' => 'cars'),
				),
			$this->getDoctrine()->getConnection()->fetchAll('SELECT * FROM articles')
			);

		$history=$manager->uninstall('Blog');
		$this->assertNull($history->getCurrent());
	}

	public function testInstall_WithSqlDump()
	{
		$manager=new MigrationsManager($doctrine=$this->getDoctrine(), $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// migrate
		$history=$manager->install('Blog');
		$this->assertEquals('20120116170000', (string)$history->getCurrent()->getVersion());

		$this->assertEquals(
			array(
				array('content' => 'trains are cool', 'title' => 'trains'),
				array('content' => 'cars are way more cool!', 'title' => 'cars'),
				array('content' => 'Čeká miminko? Modelce Kate Moss se v šatech rýsovalo bříško', 'title' => 'Kate Moss'),
				array('content' => 'Beyoncé má na snímcích z nového alba vybělenou pokožku. Stydí se snad za barvu pleti?', 'title' => 'Beyonce'),
				array('content' => 'Ta se hodně povedla! Novou Miss America je tahle kouzelná brunetka', 'title' => 'Laura Kaeppeler'),
				),
			$this->getDoctrine()->getConnection()->fetchAll('SELECT * FROM articles')
			);
	}

	public function testDumpSql()
	{
		$manager=new MigrationsManager($doctrine=$this->getDoctrine(), $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		$history=$manager->getPackageHistory('Blog');

		// dump
		$this->assertEquals(
			array(
				'20120116140000' => array(
					array('CREATE TABLE articles (content CLOB NOT NULL, title VARCHAR(255) NOT NULL)', array(), array())
					),
				'20120116150000' => array(
					array("INSERT INTO articles VALUES ('trains are cool', 'trains')", array(), array()),
					array("INSERT INTO articles VALUES ('car are fun', 'cars')", array(), array())
					),
				'20120116160000' => array(
					array("UPDATE articles SET content='cars are way more cool!' WHERE title='cars'", array(), array())
					)
				),
			$history->dumpSql($manager, '20120116160000')
			);

		$this->assertNull($history->getCurrent());
	}

	public function testInstall_SkipMigrationException()
	{
		$manager=new MigrationsManager($doctrine=$this->getDoctrine(), $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// should migrate till now
		$history=$manager->install('Shop');
		$this->assertEquals('20120116180000', $history->getCurrent()->getVersion());

		$this->assertEquals(
			array(
				array('name' => 'chuchu'),
				array('name' => 'car'),
				array('name' => 'bike')
				),
			$this->getDoctrine()->getConnection()->fetchAll('SELECT * FROM goods')
			);
	}

	/**
	 * @expectedException \Lohini\Database\Migrations\MigrationException
	 * @expectedExceptionMessage Migration 20120116150000 is irreversible, it doesn't implement down() method.
	 */
	public function testUninstall_IrreversibleMigrationException()
	{
		$manager=new MigrationsManager($doctrine=$this->getDoctrine(), $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// migrate
		$history=$manager->getPackageHistory('Shop');
		$history->migrate($manager, '20120116160000');
		$this->assertEquals('20120116160000', $history->getCurrent()->getVersion());

		$this->assertEquals(
			array(
				array('name' => 'chuchu'),
				array('name' => 'car'),
				),
			$this->getDoctrine()->getConnection()->fetchAll('SELECT * FROM goods')
			);

		// should throw
		$manager->uninstall('Shop');
	}
}
