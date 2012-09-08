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

use Lohini\Utils\MimeTypeDetector;

/**
 */
class MimeTypeDetectorTest
extends \Lohini\Testing\TestCase
{
	public function testExtensionToMime()
	{
		$this->assertEquals('image/jpeg', MimeTypeDetector::extensionToMime('jpg'));
	}

	public function testExtensionToMime_NotFound()
	{
		$this->assertNull(MimeTypeDetector::extensionToMime('fuuuuu', FALSE));
	}

	/**
	 * @expectedException \Nette\InvalidArgumentException
	 */
	public function testExtensionToMime_NotFoundException()
	{
		MimeTypeDetector::extensionToMime('fuuuuu');
	}

	public function testMimeToExtension()
	{
		$this->assertEquals('jpg', MimeTypeDetector::mimeToExtension('image/jpeg'));
	}

	public function testMimeToExtension_NotFound()
	{
		$this->assertNull(MimeTypeDetector::mimeToExtension('fuuuu/fuuuuuu', FALSE));
	}

	/**
	 * @expectedException \Nette\InvalidArgumentException
	 */
	public function testMimeToExtension_NotFoundException()
	{
		MimeTypeDetector::mimeToExtension('fuuuu/fuuuuuu');
	}
}
