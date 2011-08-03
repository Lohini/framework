<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Script\Literals;
/**
 * SassBoolean class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Boolean class
 */
class Boolean
extends Literal
{
	/**@#+
	 * Regex for matching and extracting booleans
	 */
	const MATCH='/^(true|false)\b/';


	/**
	 * @param string value of the boolean type
	 * @return Boolean
	 * @throws BooleanException
	 */
	public function __construct($value)
	{
		if (is_bool($value)) {
			$this->value=$value;
			}
		elseif ($value==='true' || $value==='false') {
			$this->value= $value==='true'? TRUE : FALSE;
			}
		else {
			throw new BooleanException('Invalid Boolean', \Lohini\WebLoader\Filters\Sass\Script\Parser::$context->node);
			}
	}

	/**
	 * Returns the value of this boolean.
	 * @return boolean the value of this boolean
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
		return $this->getValue()? 'true' : 'false';
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string $subject the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public static function isa($subject)
	{
		return preg_match(self::MATCH, $subject, $matches)? $matches[0] : FALSE;
	}
}