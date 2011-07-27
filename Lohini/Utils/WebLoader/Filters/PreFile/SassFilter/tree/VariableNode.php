<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassVariableNode class file.
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
 * VariableNode class.
 * Represents a variable.
 */
class VariableNode
extends Node
{
	const MATCH='/^([!$])([\w-]+)\s*:?\s*((\|\|)?=)?\s*(.+?)\s*(!default)?;?$/i';
	const IDENTIFIER=1;
	const NAME=2;
	const SASS_ASSIGNMENT=3;
	const SASS_DEFAULT=4;
	const VALUE=5;
	const SCSS_DEFAULT=6;
	const SASS_IDENTIFIER='!';
	const SCSS_IDENTIFIER='$';

	/** @var string name of the variable */
	private $name;
	/** @var string value of the variable or expression to evaluate */
	private $value;
	/** @var boolean whether the variable is optionally assigned */
	private $isDefault;


	/**
	 * @param object $token source token
	 * @throws VariableNodeException
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		if (empty($matches[self::NAME]) || $matches[self::VALUE]==='') {
			throw new Sass\VariableNodeException('Invalid variable definition; name and expression required', $this);			
			}
		$this->name=$matches[self::NAME];
		$this->value=$matches[self::VALUE];
		$this->isDefault=(!empty($matches[self::SASS_DEFAULT]) || !empty($matches[self::SCSS_DEFAULT]));
		
		// Warn about deprecated features
		if ($matches[self::IDENTIFIER]===self::SASS_IDENTIFIER) {
			$this->addWarning("Variables prefixed with '!' is deprecated; use '$$this->name'");
			}
		if (!empty($matches[Sass\VariableNode::SASS_ASSIGNMENT])) {
			$this->addWarning("Setting variables with '".(!empty($matches[Sass\VariableNode::SASS_DEFAULT])? '||' : '')."=' is deprecated; use '$$this->name: $this->value".(!empty($matches[Sass\VariableNode::SASS_DEFAULT])? ' !default' : '')."'");
			}
	}

	/**
	 * Parse this node.
	 * Sets the variable in the current context.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node - an empty array
	 */
	public function parse($context)
	{
		if (!$this->isDefault || !$context->hasVariable($this->name)) {
			$context->setVariable($this->name, $this->evaluate($this->value, $context));
			}
		$this->parseChildren($context); // Parse any warnings
		return array();
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object $token token
	 * @return boolean true if the token represents this type of node, false if not
	 */
	public static function isa($token)
	{
		return $token->source[0]===self::SASS_IDENTIFIER || $token->source[0]===self::SCSS_IDENTIFIER;
	}
}
