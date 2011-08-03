<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassForNode class file.
 * This is an enhanced version of the standard SassScript @for loop that adds
 * an optional step clause. Step must evaluate to a positive integer.
 * The syntax is:
 * <pre>@for <var> from <start> to|through <end>[ step <step>]</pre>.
 *
 * <start> can be less or greater than <end>.
 * If the step clause is ommitted the <step> = 1.
 * <var> is available to the rest of the script following evaluation
 * and has the value that terminated the loop.
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * ForNode class.
 * Represents a Sass @for loop.
 */
class ForNode
extends Node
{
	const MATCH='/@for\s+[!\$](\w+)\s+from\s+(.+?)\s+(through|to)\s+(.+?)(?:\s+step\s+(.+))?$/i';

	const VARIABLE=1;
	const FROM=2;
	const INCLUSIVE=3;
	const TO=4;
	const STEP=5;
	const IS_INCLUSIVE='through';

	/** @var string variable name for the loop */
	private $variable;
	/** @var string expression that provides the loop start value */
	private $from;
	/** @var string expression that provides the loop end value */
	private $to;
	/** @var boolean whether the loop end value is inclusive */
	private $inclusive;
	/**
	 * @var string expression that provides the amount by which the loop variable
	 * changes on each iteration
	 */
	private $step;


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		if (!preg_match(self::MATCH, $token->source, $matches)) {
			throw new ForNodeException('Invalid @for directive', $this);
			}
		$this->variable=$matches[self::VARIABLE];
		$this->from=$matches[self::FROM];
		$this->to=$matches[self::TO];
		$this->inclusive=($matches[self::INCLUSIVE]===ForNode::IS_INCLUSIVE);
		$this->step= empty($matches[self::STEP])? 1 : $matches[self::STEP];
	}

	/**
	 * Parse this node.
	 * @param Context $context the context in which this node is parsed
	 * @return array parsed child nodes
	 */
	public function parse($context)
	{
		$children=array();
		$from=(float)$this->evaluate($this->from, $context)->value;
		$to=(float)$this->evaluate($this->to, $context)->value;
		$step=(float)$this->evaluate($this->step, $context)->value*($to>$from? 1 : -1);

		if ($this->inclusive) {
			$to+=($from<$to? 1 : -1);
			}

		$context=new Context($context);
		for ($i=$from; ($from<$to? $i<$to : $i>$to); $i=$i+$step) {
			$context->setVariable($this->variable, new \Lohini\WebLoader\Filters\Sass\Script\Literals\Number($i));
			$children=array_merge($children, $this->parseChildren($context));
		}
		return $children;
	}
}
