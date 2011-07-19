<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniTesting\DI;

class ContainerTest
extends \PHPUnit_Framework_TestCase
{
	/** @var \Lohini\DI\Container */
	private $container;

	public function setUp()
	{
		$this->container=new \Lohini\DI\Container;
	}

	/**
	 * @expectedException \Nette\OutOfRangeException
	 */
	public function testGetParamMissing()
	{
		$this->container->getParam(\Nette\Utils\Strings::random());
	}
}
