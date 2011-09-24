<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;

/**
 * FunctionNode class.
 * Represents a Function definition.
 *
 * @author Lopo <lopo@lohini.net>
 */
class FunctionNode
extends Node
{
	const MATCH='/^@function\s+([-\w]+)\s*(?:\((.+?)\))?\s*$/i';
	const NAME=1;
	const ARGUMENTS=2;

	/** @var string name of the function */
	private $name;
	/**
	 * @var array arguments for the function as name=>value pairs were value is the
	 * default value or null for required arguments
	 */
	private $args=array();


	/**
	 * @param object $token source token
	 * @throws FunctionNodeException
	 */
	public function __construct($token)
	{
		if ($token->level!==0) {
			throw new FunctionNodeException('Functions can only be defined at root level', $this);
		 	}
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		if (empty($matches)) {
			throw new FunctionNodeException('Invalid Function', $this);
			}
		$this->name=$matches[self::NAME];
		if (isset($matches[self::ARGUMENTS])) {
			foreach (explode(',', $matches[self::ARGUMENTS]) as $arg) {
				$args=explode(':', trim($arg));
				$this->args[substr(trim($args[0]), 1)]= count($args)==2? trim($args[1]) : NULL;
				}
			}
	}

	/**
	 * Parse this node.
	 * Add this function to the current context.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node - an empty array
	 */
	public function parse($context)
	{
		$context->addFunction($this->name, $this);
		return array();
	}

	/**
	 * Returns the arguments with default values for this function
	 * @return array the arguments with default values for this function
	 */
	public function getArgs()
	{
		return $this->args;
	}

	public function perform($args)
	{
		$context=new Context;
		$argc=count($args);
		$count=0;
		foreach ($this->args as $name => $value) {
			if ($count<$argc) {
				$context->setVariable($name, $this->evaluate($args[$count++], $context));
				}
			elseif (!is_null($value)) {
				$context->setVariable($name, $this->evaluate($value, $context));
				}
			else {
				throw new FunctionNodeException("Function::$this->name: Required variable ($name) not given.\nFunction defined: {$this->token->filename}::{$this->token->line}\nFunction used", $this);
				}
			} // foreach

		$children=array();
		foreach ($this->children as $child) {
			if ($child instanceof ReturnNode) {
				return $this->evaluate($child->getExpression(), $context);
				}
			$child->parent=$this;
			$children=array_merge($children, $child->parse($context));
			} // foreach
		throw FunctionNodeException("Function::$this->name: Required node @return not found.\nFunction defined: {$this->token->filename}::{$this->token->line}\nFunction used", $this);
	}
}
