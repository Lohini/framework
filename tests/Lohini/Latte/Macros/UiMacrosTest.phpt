<?php // vim: ts=4 sw=4 ai:
/**
 * Test: Lohini\Latte\Macros\UIMacros
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

namespace Lohini\Tests\Latte\Macros;

use Tester\Assert,
	Nette\Latte;

require_once __DIR__.'/../../bootstrap.php';

class UIMacrosTest
extends \Tester\TestCase
{
	/** @var Latte\Compiler */
	private $compiler;
	/** @var Latte\Parser */
	private $parser;

	protected function setUp()
	{
		$this->compiler=new Latte\Compiler;
		$this->compiler->setContext(Latte\Compiler::CONTENT_HTML);
		$this->parser=new Latte\Parser;
		$this->parser->setContext(Latte\Parser::CONTEXT_HTML_TEXT);
		\Lohini\Latte\Macros\UIMacros::install($this->compiler);
		\Nette\Diagnostics\Debugger::$maxLen=4096;
	}

	public function testAsAttachment()
	{
		$data='{asAttachment filename.ext}';
		$expected='<?php $netteHttpResponse->setHeader("Content-Disposition", "attachment; filename=\"filename.ext\"") ?>';

		$tokens=$this->parser->parse($data);
		$actual=$this->compiler->compile($tokens);
		Assert::equal($expected, $actual);
	}
}

id(new UIMacrosTest)->run(isset($_SERVER['argv'][1])? $_SERVER['argv'][1] : NULL);
