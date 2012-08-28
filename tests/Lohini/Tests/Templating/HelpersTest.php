<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Templating;

use Lohini\Templating\Helpers;

/**
 */
class HelpersTest
extends \Lohini\Testing\TestCase
{
	public function testLoader()
	{
		$this->assertEquals(callback('Lohini\Templating\Helpers', 'bytes'), Helpers::loader('bytes'), "::loader('bytes')");
		$this->assertEquals(callback('Nette\Templating\Helpers', 'date'), Helpers::loader('date'), "::loader('date')");
		$this->assertEquals('Nette\Utils\Strings::webalize', Helpers::loader('webalize'), "::loader('webalize')");
		$this->assertNull(Helpers::loader(\Nette\Utils\Strings::random()));
	}

	public function testOxmlDate()
	{
		$this->assertNull(Helpers::oxmlDate(NULL));
		$this->assertEquals('2011-06-06T00:00:00.000', Helpers::oxmlDate('6.6.2011 15:32:24'), "::oxmlDate('6.6.2011 15:32:24')");
	}

	public function testOxmlDateTime()
	{
		$this->assertNull(Helpers::oxmlDateTime(NULL));
		$this->assertEquals('2011-06-06T15:34:21.000', Helpers::oxmlDateTime('6.6.2011 15:34:21'), "::oxmlDateTime('6.6.2011 15:34:21')");
	}

	public function dataBytes()
	{
		return array(
			array('0 B', 0),
			array('0 B', 0.1),
			array('1000 B', 1000),
			array('1 kB', 1024),
			array('1.5 kB', 1536),
			array('1.46 kB', 1500),
			array('1.11 kB', 1137),
			array('1.111 kB', 1138, 3),
			array('1.1 kB', 1137, 1),
			array('1 kB', 1000, 2, 1000),
			array('1.02 kB', 1024, 2, 1000),
			array('1.54 kB', 1536, 2, 1000),
			array('1.5 kB', 1500, 2, 1000),
			array('1.14 kB', 1137, 2, 1000),
			array('1.138 kB', 1138, 3, 1000),
			array('1.1 kB', 1137, 1, 1000),
			array('1 kB', 1000, 2, 1000, TRUE),
			array('1.02 kB', 1024, 2, 1000, TRUE),
			array('1.54 kB', 1536, 2, 1000, TRUE),
			array('1.5 kB', 1500, 2, 1000, TRUE),
			array('1.14 kB', 1137, 2, 1000, TRUE),
			array('1.138 kB', 1138, 3, 1000, TRUE),
			array('1.1 kB', 1137, 1, 1000, TRUE),
			array('1000 B', 1000, 2, 1024, TRUE),
			array('1 KiB', 1024, 2, 1024, TRUE),
			array('1.5 KiB', 1536, 2, 1024, TRUE),
			array('1.46 KiB', 1500, 2, 1024, TRUE),
			array('1.11 KiB', 1137, 2, 1024, TRUE),
			array('1.111 KiB', 1138, 3, 1024, TRUE),
			array('1.1 KiB', 1137, 1, 1024, TRUE),
			array('1234567890', 1234567890, 2, 2048),
			array('1234567890', 1234567890, 2, 2048, TRUE),
			array('1.23 GB', 1234567890, 2, 1000),
			array('1.23 GB', 1234567890, 2, 1000, TRUE),
			array('1.15 GB', 1234567890, 2, 1024),
			array('1.15 GiB', 1234567890, 2, 1024, TRUE)
			);
	}

	/**
	 * @dataProvider dataBytes
	 */
	public function testBytes($out, $bytes, $precision=NULL, $kilo=NULL, $iec=NULL)
	{
		if ($precision===NULL) {
			$this->assertEquals($out, Helpers::bytes($bytes), "::bytes($bytes)");
			}
		elseif ($kilo===NULL) {
			$this->assertEquals($out, Helpers::bytes($bytes, $precision), "::bytes($bytes, $precision)");
			}
		elseif ($iec===NULL) {
			$this->assertEquals($out, Helpers::bytes($bytes, $precision, $kilo), "::bytes($bytes, $precision, $kilo)");
			}
		else {
			$this->assertEquals($out, Helpers::bytes($bytes, $precision, $kilo, $iec), "::bytes($bytes, $precision, $kilo, $iec)");
			}
	}

	public function dataDatetime()
	{
		return array(
			array('8.6.2011 20:03:59', '08.06.2011 20:03:59'),
			array('20:03:59 8.6.2011', '08.06.2011 20:03:59', 'H:i:s j.n.Y')
			);
	}

	/**
	 * @dataProvider dataDatetime
	 */
	public function testDatetime($out, $time, $format=NULL)
	{
		$this->assertEquals($out, Helpers::datetime($time, $format), "::datetime($time, '$format')");
	}

	public function testDatetimeNull()
	{
		$this->assertNull(Helpers::datetime(NULL));
	}
}
