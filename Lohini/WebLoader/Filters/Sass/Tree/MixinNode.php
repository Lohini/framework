<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassMixinNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * MixinNode class.
 * Represents a Mixin.
 */
class MixinNode
extends Node
{
	const NODE_IDENTIFIER='+';
	const MATCH='/^(\+|@include\s+)([-\w]+)\s*(?:\((.*?)\))?$/i';
	const IDENTIFIER=1;
	const NAME=2;
	const ARGS=3;

	/** @var string name of the mixin */
	private $name;
	/** @var array arguments for the mixin */
	private $args=array();


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		$this->name=$matches[self::NAME];
		if (isset($matches[self::ARGS])) {
			$this->args=\Lohini\WebLoader\Filters\Sass\Script\ScriptFunction::extractArgs($matches[self::ARGS]);
			}
	}

	/**
	 * Parse this node.
	 * Set passed arguments and any optional arguments not passed to their
	 * defaults, then render the children of the mixin definition.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node
	 * @throws MixinNodeException
	 */
	public function parse($pcontext)
	{
		$mixin=$pcontext->getMixin($this->name);

		$context=new Context($pcontext);
		$argc=count($this->args);
		$count=0;
		foreach ($mixin->args as $name => $value) {
			if ($count<$argc) {
				$context->setVariable($name, $this->evaluate($this->args[$count++], $context));
				}
			elseif (!is_null($value)) {
				$context->setVariable($name, $this->evaluate($value, $context));
				}
			else {
				throw new MixinNodeException("Mixin::{$this->name}: Required variable ($name) not given.\nMixin defined: {$mixin->token->filename}::{$mixin->token->line}\nMixin used", $this);
				}
			} // foreach

		$children=array();
		foreach ($mixin->children as $child) {
			$child->parent=$this;
			$children=array_merge($children, $child->parse($context));
			} // foreach

//		$context->merge();
		return $children;
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object token
	 * @return boolean true if the token represents this type of node, false if not
	 */
	public static function isa($token)
	{
		return $token->source[0]===self::NODE_IDENTIFIER;
	}
}
