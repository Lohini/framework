<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Script;
/**
 * SassScriptLexer class file.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass\Script\Literals;

/**
 * Script Lexer class.
 * Lexes Script into tokens for the parser.
 * 
 * Implements a {@link http://en.wikipedia.org/wiki/Shunting-yard_algorithm Shunting-yard algorithm} to provide {@link http://en.wikipedia.org/wiki/Reverse_Polish_notation Reverse Polish notation} output.
 */
class Lexer
{
	const MATCH_WHITESPACE='/^\s+/';

	/** @var Parser the parser object */
	private $parser;


	/**
	 * @param Parser $parser
	 */
	public function __construct($parser)
	{
		$this->parser=$parser;
	}

	/**
	 * Lex an expression into Script tokens.
	 * @param string $string expression to lex
	 * @param \Lohini\WebLoader\Filters\Sass\Tree\Context $context the context in which the expression is lexed
	 * @return array tokens
	 */
	public function lex($string, $context)
	{
		$tokens=array();
		while ($string!==FALSE) {
			if (($match=$this->isWhitespace($string))!==FALSE) {
				$tokens[]=NULL;
				}
			elseif (($match=ScriptFunction::isa($string))!==FALSE) {
				preg_match(ScriptFunction::MATCH_FUNC, $match, $matches);

				$args=array();
				foreach (ScriptFunction::extractArgs($matches[ScriptFunction::ARGS]) as $expression) {
					$args[]=$this->parser->evaluate($expression, $context);
					}
				$tokens[]=new ScriptFunction($matches[ScriptFunction::NAME], $args);
				}
			elseif (($match=Literals\String::isa($string))!==FALSE) {
				$tokens[]=new Literals\String($match);
				}
			elseif (($match=Literals\Boolean::isa($string))!==FALSE) {
				$tokens[]=new Literals\Boolean($match);
				}
			elseif (($match=Literals\Colour::isa($string))!==FALSE) {
				$tokens[]=new Literals\Colour($match);
				}
			elseif (($match=Literals\Number::isa($string))!==FALSE) {
				$tokens[]=new Literals\Number($match);
				}
			elseif (($match=Operation::isa($string))!==FALSE) {
				$tokens[]=new Operation($match);
				}
			elseif (($match=Variable::isa($string))!==FALSE) {
				$tokens[]=new Variable($match);
				}
			else {
				$_string=$string;
				$match='';
				while (strlen($_string) && !$this->isWhitespace($_string)) {
					foreach (Operation::$inStrOperators as $operator) {
						if (\Nette\Utils\Strings::startsWith($_string, $operator)) {
							break 2;
							}
						}
					$match.=$_string[0];
					$_string=substr($_string, 1);
					}
				$tokens[]=new Literals\String($match);
				}
			$string=substr($string, strlen($match));
			}
		return $tokens;
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string $subject the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public function isWhitespace($subject)
	{
		return preg_match(self::MATCH_WHITESPACE, $subject, $matches)? $matches[0] : FALSE;
	}
}
