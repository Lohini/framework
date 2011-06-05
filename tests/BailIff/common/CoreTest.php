<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIffTesting;

class CoreTest
extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \Nette\StaticClassException
	 */
	public function testConstruct()
	{
		new \BailIff\Core;
	}
}
