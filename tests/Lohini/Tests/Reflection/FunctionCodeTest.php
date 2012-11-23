<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Reflection;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Reflection\GlobalFunction,
	Lohini\Reflection\FunctionCode;

/**
 */
class FunctionCodeTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return \Closure
	 */
	public function data()
	{
		$first=function () {
			$second=function()
			{
				$third = function () { return 'really'; };
				return $third;
			};
			return $second;
		};
		return $first;
	}

	public function testParsingMethod()
	{
		$parser=new FunctionCode($this->getReflection()->getMethod('data'));

		$code=<<<CODE

		\$first=function () {
			\$second=function()
			{
				\$third = function () { return 'really'; };
				return \$third;
			};
			return \$second;
		};
		return \$first;

CODE;

		$this->assertEquals($code."\t", $parser->parse());
	}

	public function testParsingClosure()
	{
		$parser=new FunctionCode(new GlobalFunction($this->data()));

		$code=<<<CODE

			\$second=function()
			{
				\$third = function () { return 'really'; };
				return \$third;
			};
			return \$second;

CODE;

		$this->assertEquals($code."\t\t", $parser->parse());
	}

	public function testParsingNestedClosure()
	{
		$first=$this->data();
		$parser=new FunctionCode(new GlobalFunction($first()));

		$code=<<<CODE

				\$third = function () { return 'really'; };
				return \$third;

CODE;

		$this->assertEquals($code."\t\t\t", $parser->parse());
	}

	public function testParsingDoubleNestedClosure()
	{
		$first=$this->data();
		$second=$first();
		$parser=new FunctionCode(new GlobalFunction($second()));

		$code=" return 'really'; ";
		$this->assertEquals($code, $parser->parse());
	}

	public function testParsingFunction()
	{
		$parser=new FunctionCode(new GlobalFunction(__NAMESPACE__.'\testing_function'));

		$code="\n\treturn 'mam';\n";
		$this->assertEquals($code, $parser->parse());
	}

	public function testResolvableClosureDefinition()
	{
		list($closure)=require __DIR__.'/ClosureDefinitionStub.php';
		$parser=new FunctionCode(new GlobalFunction($closure));
		$this->assertEquals(" return 'works'; ", $parser->parse());
	}

	/**
	 * @expectedException Nette\InvalidStateException
	 * @expectedExceptionMessage Lohini\Tests\Reflection\{closure}() cannot be parsed, because there are multiple closures defined on line 17.
	 */
	public function testUnresolvableDefinitionException()
	{
		list(, $closure)=require __DIR__.'/ClosureDefinitionStub.php';
		$parser=new FunctionCode(new GlobalFunction($closure));
		$parser->parse();
	}
}


/**
 * @return string
 */
function testing_function()
{
	return 'mam';
}
