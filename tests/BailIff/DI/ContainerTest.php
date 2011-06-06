<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIffTesting\DI;

class ContainerTest
extends \PHPUnit_Framework_TestCase
{
	/** @var \BailIff\DI\Container */
	private $container;

	public function setUp()
	{
		$this->container=new \BailIff\DI\Container;
	}

	/**
	 * @expectedException \Nette\OutOfRangeException
	 */
	public function testGetParamMissing()
	{
		$this->container->getParam(\Nette\Utils\Strings::random());
	}
}
