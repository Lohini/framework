<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassScriptLexer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass;

/**
 * ScriptLexer class.
 * Lexes Sass\Script into tokens for the parser.
 * 
 * Implements a {@link http://en.wikipedia.org/wiki/Shunting-yard_algorithm Shunting-yard algorithm} to provide {@link http://en.wikipedia.org/wiki/Reverse_Polish_notation Reverse Polish notation} output.
 */
class ScriptLexer
{
	const MATCH_WHITESPACE='/^\s+/';

	/** @var ScriptParser the parser object */
	private $parser;


	/**
	 * @param ScriptParser $parser
	 */
	public function __construct($parser)
	{
		$this->parser=$parser;
	}
	
	/**
	 * Lex an expression into Sass\Script tokens.
	 * @param string $string expression to lex
	 * @param Context $context the context in which the expression is lexed
	 * @return array tokens
	 */
	public function lex($string, $context)
	{
		$tokens=array();
		while ($string!==FALSE) {
			if (($match=$this->isWhitespace($string))!==FALSE) {
				$tokens[]=NULL;
				}
			elseif (($match=Sass\ScriptFunction::isa($string))!==FALSE) {
				preg_match(Sass\ScriptFunction::MATCH_FUNC, $match, $matches);
				
				$args=array();
				foreach (Sass\ScriptFunction::extractArgs($matches[Sass\ScriptFunction::ARGS]) as $expression) {
					$args[]=$this->parser->evaluate($expression, $context);
					}
				$tokens[]=new Sass\ScriptFunction($matches[Sass\ScriptFunction::NAME], $args);
				}
			elseif (($match=Sass\String::isa($string))!==FALSE) {
				$tokens[]=new Sass\String($match);
				}
			elseif (($match=Sass\Boolean::isa($string))!==FALSE) {
				$tokens[]=new Sass\Boolean($match);
				}
			elseif (($match=Sass\Colour::isa($string))!==FALSE) {
				$tokens[]=new Sass\Colour($match);
				}
			elseif (($match=Sass\Number::isa($string))!==FALSE) {				
				$tokens[]=new Sass\Number($match);
				}
			elseif (($match=Sass\ScriptOperation::isa($string))!==FALSE) {
				$tokens[]=new Sass\ScriptOperation($match);
				}
			elseif (($match=Sass\ScriptVariable::isa($string))!==FALSE) {
				$tokens[]=new Sass\ScriptVariable($match);
				}
			else {
				$_string=$string;
				$match='';
				while (strlen($_string) && !$this->isWhitespace($_string)) {
					foreach (Sass\ScriptOperation::$inStrOperators as $operator) {
						if (substr($_string, 0, strlen($operator))==$operator) {
							break 2;
							}
						}
					$match.=$_string[0];
					$_string=substr($_string, 1);			
					}
				$tokens[]=new Sass\String($match);
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
