<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassMixinDefinitionNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass;

/**
 * MixinDefinitionNode class.
 * Represents a Mixin definition.
 */
class MixinDefinitionNode
extends Node
{
	const NODE_IDENTIFIER='=';
	const MATCH='/^(=|@mixin\s+)([-\w]+)\s*(?:\((.+?)\))?\s*$/i';
	const IDENTIFIER=1;
	const NAME=2;
	const ARGUMENTS=3;

	/** @var string name of the mixin */
	private $name;
	/**
	 * @var array arguments for the mixin as name=>value pairs were value is the
	 * default value or null for required arguments
	 */
	private $args=array();


	/**
	 * @param object $token source token
	 * @throws MixinDefinitionNodeException
	 */
	public function __construct($token)
	{
		if ($token->level!==0) {
			throw new Sass\MixinDefinitionNodeException('Mixins can only be defined at root level', $this);
		 	}
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		if (empty($matches)) {
			throw new Sass\MixinDefinitionNodeException('Invalid Mixin', $this);
			}
		$this->name=$matches[self::NAME];
		if (isset($matches[self::ARGUMENTS])) {
			foreach (explode(',', $matches[self::ARGUMENTS]) as $arg) {
				$arg=explode(
						$matches[self::IDENTIFIER]===self::NODE_IDENTIFIER? '=' : ':',
						trim($arg)
						);
				$this->args[substr(trim($arg[0]), 1)]= count($arg)==2? trim($arg[1]) : NULL;
				}
			}
	}

	/**
	 * Parse this node.
	 * Add this mixin to the current context.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node - an empty array
	 */
	public function parse($context)
	{
		$context->addMixin($this->name, $this);
		return array();
	}

	/**
	 * Returns the arguments with default values for this mixin
	 * @return array the arguments with default values for this mixin
	 */
	public function getArgs()
	{
		return $this->args;
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
