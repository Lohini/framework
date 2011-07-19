<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassExtendNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * ExtendNode class.
 * Represents a Sass @debug or @warn directive.
 */
class ExtendNode
extends Node
{
	const IDENTIFIER='@';
	const MATCH='/^@extend\s+(.+)/i';
	const VALUE=1;

	/** @var string the directive */
	private $value;


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		$this->value=$matches[self::VALUE];
	}

	/**
	 * Parse this node.
	 * @param $context
	 * @return array An empty array
	 */
	public function parse($context)
	{
		$this->root->extend($this->value, $this->parent->selectors);
		return array();
	}
}
