<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Migrations\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Migrations\Tools\SqlDump;

/**
 */
class SqlDumpTest
extends \Lohini\Testing\OrmTestCase
{
	/** @var SqlDump */
	private $dump;


	protected function setUp()
	{
		$this->dump=new SqlDump(__DIR__.'/../Fixtures/Blog/Migration/Version20120116170000.sql');
	}

	/**
	 * @return array
	 */
	public function dataSqls()
	{
		return array(
			"INSERT INTO articles VALUES ('Čeká miminko? Modelce Kate Moss se v šatech rýsovalo bříško', 'Kate Moss');",
			"INSERT INTO articles VALUES ('Beyoncé má na snímcích z nového alba vybělenou pokožku. Stydí se snad za barvu pleti?', 'Beyonce');",
			"INSERT INTO articles VALUES ('Ta se hodně povedla! Novou Miss America je tahle kouzelná brunetka', 'Laura Kaeppeler');"
			);
	}

	public function testIterating()
	{
		$sqls=$this->dataSqls();
		foreach ($this->dump as $sql) {
			$this->assertEquals(array_shift($sqls), $sql);
			}
		$this->assertEmpty($sqls);
	}

	public function testGetSqls()
	{
		$this->assertEquals($this->dataSqls(), $this->dump->getSqls());
	}
}
