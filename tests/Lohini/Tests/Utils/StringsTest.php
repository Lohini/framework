<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Utils;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class TestTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return array
	 */
	public function getBlendData()
	{
		return array(
			array('/var/www/libs/library/namespace/subns', 'namespace', '/var/www/libs/library/namespace'),
			array('abcdefghij', 'hijkl', 'abcdefghijkl'),
			array('/var/www/libs/library/namespace', 'namespace', '/var/www/libs/library/namespace'),
			array('/var/www/libs/library/namespace', 'namespace/subns', '/var/www/libs/library/namespace/subns')
			);
	}

	/**
	 * @dataProvider getBlendData
	 */
	public function testBlend($a, $b, $result)
	{
		$this->assertSame($result, \Lohini\Utils\Strings::blend($a, $b));
	}
}
