<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Types;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class StringTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Database\Doctrine\Types\String */
	private $string;
	/** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
	private $platform;


	protected function setUp()
	{
		$this->string=$this->createType('Lohini\Database\Doctrine\Types\String');
		$this->platform=new \Doctrine\DBAL\Platforms\SqlitePlatform;
	}

	/**
	 * @return array
	 */
	public function dataConvertToPhp()
	{
		return array(
			array('a', ' a '),
			array(NULL, ''),
			array(NULL, ' '),
			array(NULL, " \t\n\r"), // rly?
			);
	}

	/**
	 * @dataProvider dataConvertToPhp
	 *
	 * @param string $expected
	 * @param string $given
	 */
	public function testConvertToPhpValue($expected, $given)
	{
		$this->assertSame($expected, $this->string->convertToPHPValue($given, $this->platform));
	}

	/**
	 * @return array
	 */
	public function dataConvertToDatabase()
	{
		return array(
			array('a', ' a '),
			array(NULL, ''),
			array(NULL, ' '),
			array(NULL, " \t\n\r"), // rly?
			);
	}

	/**
	 * @dataProvider dataConvertToDatabase
	 *
	 * @param string $expected
	 * @param string $given
	 */
	public function testConvertToDatabaseValue($expected, $given)
	{
		$this->assertSame($expected, $this->string->convertToPHPValue($given, $this->platform));
	}

	/**
	 * @param string $type
	 * @return \Doctrine\DBAL\Types\Type
	 */
	private function createType($type)
	{
		return unserialize(sprintf('O:%d:"%s":0:{}', strlen($type), $type));
	}
}
