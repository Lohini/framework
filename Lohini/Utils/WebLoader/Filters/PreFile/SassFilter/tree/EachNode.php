<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassEachNode class file.
 * The syntax is:
 * <pre>@each <var> in <list></pre>.
 *
 * <list> is comma+space separated.
 * <var> is available to the rest of the script following evaluation
 * and has the value that terminated the loop.
 * 
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass;

/**
 * EachNode class.
 * Represents a Sass @each loop.
 */
class EachNode
extends Node
{
	const MATCH='/@each\s+[!\$](\w+)\s+in\s+(.+)$/i';

	const VARIABLE=1;
	const IN=2;

	/** @var string variable name for the loop */
	private $variable;
	/** @var string expression that provides the loop values */
	private $in;


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		if (!preg_match(self::MATCH, $token->source, $matches)) {
			throw new Sass\EachNodeException('Invalid @each directive', $this);
			}
		$this->variable=$matches[self::VARIABLE];
		$this->in=$matches[self::IN];
	}

	/**
	 * Parses this node.
	 * @param Context $context the context in which this node is parsed
	 * @return array parsed child nodes
	 */
	public function parse($context)
	{
		$children=array();
		$in=explode(', ', $this->in);

		$context=new Sass\Context($context);
		foreach ($in as $var) {
			$context->setVariable($this->variable, new Sass\String($var));
			$children=array_merge($children, $this->parseChildren($context));
			}
		return $children;
	}
}
