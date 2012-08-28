<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Utils;

use Lohini\Utils\Network;

/**
 */
class NetworkTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @expectedException \Nette\ArgumentOutOfRangeException
	 */
	public function testCIDR2LongRangeException1()
	{
		Network::CIDR2LongRange('1.2.3.4/0');
	}

	/**
	 * @expectedException \Nette\ArgumentOutOfRangeException
	 */
	public function testCIDR2LongRangeException2()
	{
		Network::CIDR2LongRange('1.2.3.4/33');
	}

	public function dataCIDR2LongRange()
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
	 * @dataProvider dataCIDR2LongRange
	 */
	public function testCIDR2LongRange($in, $out)
	{
		$range=Network::CIDR2LongRange($in);
		$this->assertEquals($out[0], sprintf('%u', $range[0]));
		$this->assertEquals($out[1], sprintf('%u', $range[1]));
	}

	public function dataHostInCIDR()
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
	 * @dataProvider dataHostInCIDR
	 */
	public function testHostInCIDR($ok, $ip, $net)
	{
		$this->assertEquals($ok, Network::HostInCIDR($ip, $net));
	}
}
