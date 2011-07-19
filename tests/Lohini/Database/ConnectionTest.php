<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniTesting\Database;

class ConnectionTest
extends \PHPUnit_Framework_TestCase
{
	public function testGetInstance()
	{
		$this->assertInstanceOf('\Lohini\Loaders\LohiniLoader', \Lohini\Loaders\LohiniLoader::getInstance());
	}
}
