<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Config;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Config\CompilerExtension;

/**
 */
class CompilerExtensionTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return array
	 */
	public function dataConfigsOptions()
	{
		return array(
			array(
				array('two' => 3),
				array('one' => 1, 'two' => 2, 'three' => NULL),
				array('one' => 1, 'two' => 3),
				)
			);
	}

	/**
	 * @dataProvider dataConfigsOptions
	 *
	 * @param array $config
	 * @param array $defaults
	 * @param array $options
	 */
	public function testGetOptions(array $config, array $defaults, array $options)
	{
		$this->assertEquals($options, CompilerExtension::getOptions($config, $defaults));
	}

	/**
	 * @return array
	 */
	public function dataConfigsOptionsKeepNull()
	{
		return array(
			array(
				array('two' => 3),
				array('one' => 1, 'two' => 2, 'three' => NULL),
				array('one' => 1, 'two' => 3, 'three' => NULL),
				)
			);
	}

	/**
	 * @dataProvider dataConfigsOptionsKeepNull
	 *
	 * @param array $config
	 * @param array $defaults
	 * @param array $options
	 */
	public function testGetOptionsKeepNull(array $config, array $defaults, array $options)
	{
		$this->assertEquals($options, CompilerExtension::getOptions($config, $defaults, TRUE));
	}
}
