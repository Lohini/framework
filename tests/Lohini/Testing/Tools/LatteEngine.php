<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\Tools;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Latte;

/**
 */
class LatteEngine
extends \Nette\Object
{
	/** @var Latte\Parser */
	private $parser;
	/** @var Latte\Compiler */
	private $compiler;


	/**
	 */
	public function __construct()
	{
		$this->parser=new Latte\Parser;
		$this->compiler=new Latte\Compiler;

		$coreMacros=new Latte\Macros\CoreMacros(clone $this->compiler);
		$macros=new Latte\Macros\MacroSet($this->compiler);
		$macros->addMacro('=', array($coreMacros, 'macroExpr'));
	}

	/**
	 * Invokes filter.
	 *
	 * @param string $s
	 * @return string
	 */
	public function __invoke($s)
	{
		return $this->compiler->compile($this->parser->parse($s));
	}

	/**
	 * @return Latte\Parser
	 */
	public function getParser()
	{
		return $this->parser;
	}

	/**
	 * @return Latte\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}
}
