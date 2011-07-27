<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassDirectiveNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Utils\Strings;

/**
 * DirectiveNode class.
 * Represents a CSS directive.
 */
class DirectiveNode
extends Node
{
	const NODE_IDENTIFIER='@';
	const MATCH='/^(@[\w-]+)/';


	protected function getDirective()
	{
		preg_match('/^(@[\w-]+)(?:\s*(\w+))*/', $this->token->source, $matches);
		array_shift($matches);
		$parts=implode(' ', $matches);
		return Strings::lower($parts);
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
		return Strings::lower($matches[1]);
	}
}
