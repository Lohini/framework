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
	/** @var \BailIff\Application\PresenterFactory */
	private $container;

	public function setUp()
	{
		$this->container=new \BailIff\DI\Container;
	}

	public function providerParams()
	{
		return array(
			array('foo', NULL, 'FoO'),
			array('foo', 'FOO', 'FOO')
			);
	}

	/**
	 * @dataProvider providerParams
	 */
	/*
	public function testGetParam($key, $default, $val)
	{
		$this->assertEquals($val, $this->container->getParam($key), "->getParam('$key')");
	}
*/
	/**
	 * @expectedException \Nette\OutOfRangeException
	 */
	public function testGetParamMissing()
	{
		$this->container->getParam('oof');
	}
}
