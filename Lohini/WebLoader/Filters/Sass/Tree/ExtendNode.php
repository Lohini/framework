<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassExtendNode class file.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
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
