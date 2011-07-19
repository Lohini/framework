<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassString class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use BailIff\WebLoader\Filters\Sass;

/**
 * String class.
 * Provides operations and type testing for Sass strings.
 */
class String
extends Literal
{
	const MATCH='/^(((["\'])(.*)(\3))|(-[a-zA-Z][^\s]*))/i';
	const _MATCH='/^(["\'])(.*?)(\1)?$/'; // Used to match strings such as "Times New Roman",serif
	const VALUE=2;
	const QUOTE=3;
	/** @var string string quote type; double or single quotes, or unquoted. */
	private $quote; 


	/**
	 * @param string $value string
	 */
	public function __construct($value)
	{
		preg_match(self::_MATCH, $value, $matches);
		if ((isset($matches[self::QUOTE]))) {
			$this->quote=$matches[self::QUOTE];
			$this->value=$matches[self::VALUE];			
			}
		else {
			$this->quote='';
			$this->value=$value;			
			}
	}

	/**
	 * String addition.
	 * Concatenates this and other.
	 * The resulting string will be quoted in the same way as this.
	 * @param String string to add to this
	 * @return String the string result
	 * @throws StringException
	 */
	public function op_plus($other)
	{
		if (!($other instanceof Sass\String)) {
			throw new Sass\StringException('Value must be a string', Sass\ScriptParser::$context->node);
			}
		$this->value.=$other->value;
		return $this;
	}

	/**
	 * String multiplication.
	 * this is repeated other times
	 * @param Number the number of times to repeat this
	 * @return String the string result
	 * @throws StringException
	 */
	public function op_times($other)
	{
		if (!($other instanceof Sass\Number) || !$other->isUnitless()) {
			throw new Sass\StringException('Value must be a unitless number', Sass\ScriptParser::$context->node);
			}
		$this->value=str_repeat($this->value, $other->value);
		return $this;
	}

	/**
	 * Returns the value of this string.
	 * @return string the string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns a string representation of the value.
	 * @return string string representation of the value.
	 */
	public function toString()
	{
		return $this->quote.$this->value.$this->quote;
	}

	/**
	 * @return string
	 */
	public function toVar()
	{
		return Sass\ScriptParser::$context->getVariable($this->value);
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public static function isa($subject)
	{
		return (preg_match(self::MATCH, $subject, $matches)? $matches[0] : FALSE);
	}
}
