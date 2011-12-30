<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniTesting\Utils;

class NetworkTest
extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \Nette\ArgumentOutOfRangeException
	 */
	public function testCIDR2LongRangeException1()
	{
		\Lohini\Utils\Network::CIDR2LongRange('1.2.3.4/0');
	}

	/**
	 * @expectedException \Nette\ArgumentOutOfRangeException
	 */
	public function testCIDR2LongRangeException2()
	{
		\Lohini\Utils\Network::CIDR2LongRange('1.2.3.4/33');
	}

	public function providerCIDR2LongRange()
	{
		return array(
			array('1.2.3.4/24', array(16909056, 16909311)),
			array('1.2.3.4', array(16909060, 16909060)),
			array('01.02.3.4', array(16909060, 16909060)),
			array('192.0.2.235', array(3221226219, 3221226219)),
			array('192.0x00.0002.235', array(3221226219, 3221226219))
			);
	}

	/**
	 * @dataProvider providerCIDR2LongRange
	 */
	public function testCIDR2LongRange($in, $out)
	{
		$r=\Lohini\Utils\Network::CIDR2LongRange($in);
		$this->assertEquals($out[0], sprintf("%u", $r[0]));
		$this->assertEquals($out[1], sprintf("%u", $r[1]));
	}

	public function providerHostInCIDR()
	{
		return array(
			array(TRUE, '1.2.3.4', '1.2.3.4'),
			array(TRUE, '1.2.3.4', '1.2.3.4/24'),
			array(TRUE, '1.2.3.45', '1.2.3.4/24'),
			array(FALSE, '1.2.3.4', '4.3.2.1/6'),
			array(TRUE, '192.0.2.235', '192.0.2.235'),
			array(TRUE, '192.0.2.235', '192.0.2.235/24'),
			array(FALSE, '192.0.2.235', '192.0.3.235/24'),
			array(FALSE, '192.0.2.235', '192.0.1.235/24')
			);
	}

	/**
	 * @dataProvider providerHostInCIDR
	 */
	public function testHostInCIDR($ok, $ip, $net)
	{
		$this->assertEquals($ok, \Lohini\Utils\Network::HostInCIDR($ip, $net));
	}
}
