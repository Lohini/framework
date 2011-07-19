<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassIfNode class file.
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
 * IfNode class.
 * Represents Sass If, Else If and Else statements.
 * Else If and Else statement nodes are chained below the If statement node.
 */
class IfNode
extends Node
{
	const MATCH_IF='/^@if\s+(.+)$/i';
	const MATCH_ELSE='/@else(\s+if\s+(.+))?/i';
	const IF_EXPRESSION=1;
	const ELSE_IF=1;
	const ELSE_EXPRESSION=2;

	/** @var IfNode the next else node */
	private $else;
	/** @var string expression to evaluate */
	private $expression;


	/**
	 * @param object $token source token
	 * @param boolean $if true for an "if" node, false for an "else if | else" node
	 */
	public function __construct($token, $if=TRUE)
	{
		parent::__construct($token);
		if ($if) {
			preg_match(self::MATCH_IF, $token->source, $matches);
			$this->expression=$matches[Sass\IfNode::IF_EXPRESSION];
			}
		else {
			preg_match(self::MATCH_ELSE, $token->source, $matches);
			$this->expression= sizeof($matches)==1? NULL : $matches[Sass\IfNode::ELSE_EXPRESSION];
			}
	}

	/**
	 * Adds an "else" statement to this node.
	 * @param IfNode $node "else" statement node to add
	 * @return IfNode this node
	 */
	public function addElse($node)
	{
		if (is_null($this->else)) {
			$node->parent=$this->parent;
			$node->root=$this->root;
			$this->else=$node;
			}
		else {
			$this->else->addElse($node);
			}
		return $this;
	}

	/**
	 * Parse this node.
	 * @param Context $context the context in which this node is parsed
	 * @return array parsed child nodes
	 */
	public function parse($context)
	{
		if ($this->isElse() || $this->evaluate($this->expression, $context)->toBoolean()) {
			$children=$this->parseChildren($context);
			}
		elseif (!empty($this->else)) {
			$children=$this->else->parse($context);
			}
		else {
			$children=array();
			}
		return $children;
	}

	/**
	 * Returns a value indicating if this node is an "else" node.
	 * @return true if this node is an "else" node, false if this node is an "if"
	 * or "else if" node
	 */
	private function isElse()
	{
	  return empty($this->expression);
	}
}
