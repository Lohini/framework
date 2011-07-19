<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassDirectiveNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * DirectiveNode class.
 * Represents a CSS directive.
 */
class DirectiveNode
extends Node
{
	const NODE_IDENTIFIER='@';
	const MATCH='/^(@\w+)/';


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
	}
	
	protected function getDirective()
	{
		return self::extractDirective($this->token);
	}

	/**
	 * Parse this node.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node
	 */
	public function parse($context)
	{
		$this->children=$this->parseChildren($context);
		return array($this);
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render()
	{
		$properties=array();
		foreach ($this->children as $child) {
			$properties[]=$child->render();
			}

		return $this->renderer->renderDirective($this, $properties);
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object $token token
	 * @return boolean true if the token represents this type of node, false if not
	 */
	public static function isa($token)
	{
		return $token->source[0]===self::NODE_IDENTIFIER;
	}

	/**
	 * Returns the directive
	 * @param object $tokentoken
	 * @return string the directive
	 */
	public static function extractDirective($token)
	{
		preg_match(self::MATCH, $token->source, $matches);
		return strtolower($matches[1]);
	}
}
