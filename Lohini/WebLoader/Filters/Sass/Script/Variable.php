<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Script;
/**
 * SassVariable class file.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Variable class
 */
class Variable
{
	/** Regex for matching and extracting Variables */
	const MATCH='/^(?<!\\\\)(?(?!!important\b)[!\$]([\w-]+))/';
	
	/** @var string name of variable */
	private $name;


	/**
	 * @param string $value value of the Variable type
	 */
	public function __construct($value)
	{
		$this->name=substr($value, 1);
	}

	/**
	 * Returns the Sass\Script object for this variable.
	 * @param \Lohini\WebLoader\Filters\Sass\Tree\Context $context context of the variable
	 * @return \Lohini\WebLoader\Filters\Sass\Script\Literals\Literal the Script object for this variable
	 */
	public function evaluate($context)
	{
		return $context->getVariable($this->name);
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string $subject the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public static function isa($subject)
	{
		// we need to do the check as preg_match returns a count of 1 if
		// subject == '!important'; the match being an empty match
		return preg_match(self::MATCH, $subject, $matches)? (empty($matches[0])? FALSE : $matches[0]) : FALSE;
	}
}
