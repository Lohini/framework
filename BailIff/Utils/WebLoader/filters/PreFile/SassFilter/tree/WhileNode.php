<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassWhileNode class file.
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
 * WhileNode class.
 * Represents a Sass @while loop and a Sass @do loop.
 */
class WhileNode
extends Node
{
	const MATCH='/^@(do|while)\s+(.+)$/i';
	const LOOP=1;
	const EXPRESSION=2;
	const IS_DO='do';

	/**
	 * @var boolean whether this is a do/while.
	 * A do/while loop is guarenteed to run at least once.
	 */
	private $isDo;
	/** @var string expression to evaluate */
	private $expression;


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		$this->expression=$matches[self::EXPRESSION];
		$this->isDo=($matches[self::LOOP]===Sass\WhileNode::IS_DO);
	}

	/**
	 * Parse this node.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed child nodes
	 */
	public function parse($context)
	{
		$children=array();
		if ($this->isDo) {
			do {
				$children=array_merge($children, $this->parseChildren($context));
				} while ($this->evaluate($this->expression, $context)->toBoolean());
			}
		else {
			while ($this->evaluate($this->expression, $context)->toBoolean()) {
				$children=array_merge($children, $this->parseChildren($context));
				}
			}
		return $children;
	}
}
