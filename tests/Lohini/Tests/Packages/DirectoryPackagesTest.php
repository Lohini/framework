<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Packages;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class DirectoryPackagesTest
extends \Lohini\Testing\TestCase
{
    public function testGettingPackages()
    {
		$dir=realpath(__DIR__.'/../Package/Fixtures');
        $finder=new \Lohini\Packages\DirectoryPackages($dir, 'Lohini\Tests\Package\Fixtures');
        $this->assertEquals(
			array(
				'Lohini\Tests\Package\Fixtures\Bar\Package',
				'Lohini\Tests\Package\Fixtures\Foo\Package',
				),
			$finder->getPackages()
			);
    }
}
