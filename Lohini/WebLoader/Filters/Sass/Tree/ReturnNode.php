<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;

/**
 * Return node class.
 * Represents Sass return statements.
 * Return statement nodes are chained below the Function statement node.
 */
class ReturnNode
extends Node
{
	const MATCH='/^@return\s+(.+)$/i';
	const EXPRESSION=1;

	/** @var string expression to evaluate */
	private $expression;


	/**
	 * @param object $token source token
	 * @param bool $if true for an "if" node, false for an "else if | else" node
	 */
	public function __construct($token, $if=TRUE)
	{
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		$this->expression=$matches[self::EXPRESSION];
	}

	/**
	 * Parse this node.
	 * @param Context $context the context in which this node is parsed
	 * @return array parsed child nodes
	 */
	public function parse($context)
	{
//		$this->evaluate($this->expression, $context);
		$this->parseChildren($context); // Parse any warnings
		return array();
	}

	public function getExpression()
	{
		return $this->expression;
	}
}
