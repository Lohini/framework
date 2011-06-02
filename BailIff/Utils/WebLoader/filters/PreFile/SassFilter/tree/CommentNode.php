<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassCommentNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * CommentNode class.
 * Represents a CSS comment.
 */
class CommentNode
extends Node
{
	const NODE_IDENTIFIER='/';
	const MATCH='%^/\*\s*(.*?)\s*(\*/)?$%s';
	const COMMENT=1;

	private $value; 


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);		
		preg_match(self::MATCH, $token->source, $matches);
		$this->value=$matches[self::COMMENT];
	}
	
	protected function getValue()
	{
		return $this->value; 
	} 

	/**
	 * Parse this node.
	 * @param mixed $context
	 * @return array the parsed node - an empty array
	 */
	public function parse($context)
	{
		return array($this);
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render()
	{
		return $this->renderer->renderComment($this);
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
}
