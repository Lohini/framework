<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini;

use Lohini\Core;

class CoreTest
extends \LohiniTesting\Tests\TestCase
{
	/**
	 * @expectedException \Nette\StaticClassException
	 */
	public function testConstruct()
	{
		new Core;
	}
}
