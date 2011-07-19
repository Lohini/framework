<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniTesting;

class CoreTest
extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \Nette\StaticClassException
	 */
	public function testConstruct()
	{
		new \Lohini\Core;
	}
}
