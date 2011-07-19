<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniTesting\Templating;

class HelpersTest
extends \PHPUnit_Framework_TestCase
{
	public function testLoader()
	{
		$this->assertEquals(callback('Lohini\Templating\Helpers', 'bytes'), \Lohini\Templating\Helpers::loader('bytes'), "::loader('bytes')");
		$this->assertEquals(callback('Nette\Templating\DefaultHelpers', 'date'), \Lohini\Templating\Helpers::loader('date'), "::loader('date')");
		$this->assertEquals('Nette\Utils\Strings::webalize', \Lohini\Templating\Helpers::loader('webalize'), "::loader('webalize')");
		$this->assertNull(\Lohini\Templating\Helpers::loader(\Nette\Utils\Strings::random()));
		$this->assertEquals('Lohini\Components\Gravatar::helper', \Lohini\Templating\Helpers::loader('gravatar'), "::loader('gravatar')");
	}

	public function testOxmlDate()
	{
		$this->assertNull(\Lohini\Templating\Helpers::oxmlDate(NULL));
		$this->assertEquals('2011-06-06T00:00:00.000', \Lohini\Templating\Helpers::oxmlDate('6.6.2011 15:32:24'), "::oxmlDate('6.6.2011 15:32:24')");
	}

	public function testOxmlDateTime()
	{
		$this->assertNull(\Lohini\Templating\Helpers::oxmlDateTime(NULL));
		$this->assertEquals('2011-06-06T15:34:21.000', \Lohini\Templating\Helpers::oxmlDateTime('6.6.2011 15:34:21'), "::oxmlDateTime('6.6.2011 15:34:21')");
	}

	public function providerBytes()
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
	 * @dataProvider providerBytes
	 */
	public function testBytes($out, $bytes, $precision=2, $kilo=1024, $iec=FALSE)
	{
		$this->assertEquals($out, \Lohini\Templating\Helpers::bytes($bytes, $precision, $kilo, $iec), "::bytes($bytes, $precision, $kilo, $iec)");
	}

	public function providerDatetime()
	{
		return array(
			array('8.6.2011 20:03:59', '08.06.2011 20:03:59'),
			array('20:03:59 8.6.2011', '08.06.2011 20:03:59', 'H:i:s j.n.Y')
			);
	}

	/**
	 * @dataProvider providerDatetime
	 */
	public function testDatetime($out, $time, $format=NULL)
	{
		$this->assertEquals($out, \Lohini\Templating\Helpers::datetime($time, $format), "::datetime($time, '$format')");
	}

	public function testDatetimeNull()
	{
		$this->assertNull(\Lohini\Templating\Helpers::datetime(NULL));
	}
}
