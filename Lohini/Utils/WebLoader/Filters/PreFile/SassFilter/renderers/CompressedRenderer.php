<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassCompressedRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * CompressedRenderer class.
 * Compressed style takes up the minimum amount of space possible, having no
 * whitespace except that necessary to separate selectors and a newline at the
 * end of the file. It's not meant to be human-readable
 */
class CompressedRenderer
extends \Lohini\WebLoader\Filters\Sass\Renderer
{
	/**
	 * Renders the brace between the selectors and the properties
	 * @return string the brace between the selectors and the properties
	 */
	protected function between()
	{
		return '{';
	}

	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end()
	{
		return '}';
	}

	/**
	 * Returns the indent string for the node
	 * @param Node $node the node to return the indent string for
	 * @return string the indent string for this Node
	 */
	protected function getIndent($node)
	{
		return '';
	}

	/**
	 * Renders a comment.
	 * @param Node $node the node being rendered
	 * @return string the rendered comment
	 */
	public function renderComment($node)
	{
		return '';
	}

	/**
	 * Renders a directive.
	 * @param Node $node the node being rendered
	 * @param array $properties properties of the directive
	 * @return string the rendered directive
	 */
	public function renderDirective($node, $properties)
	{
		return $node->directive.$this->between().$this->renderProperties($node, $properties).$this->end();
	}

	/**
	 * Renders properties.
	 * @param Node $node the node being rendered
	 * @param array $properties properties to render
	 * @return string the rendered properties
	 */
	public function renderProperties($node, $properties)
	{
		return join('', $properties);
	}

	/**
	 * Renders a property.
	 * @param Node $node the node being rendered
	 * @return string the rendered property
	 */
	public function renderProperty($node)
	{
		return "$node->name:$node->value;";
	}

	/**
	 * Renders a rule.
	 * @param Node $node the node being rendered
	 * @param array $properties rule properties
	 * @param string $rules rendered rules
	 * @return string the rendered directive
	 */
	public function renderRule($node, $properties, $rules)
	{
		return (!empty($properties)? $this->renderSelectors($node).$this->between().$this->renderProperties($node, $properties).$this->end() : '').$rules;
	}

	/**
	 * Renders the rule's selectors
	 * @param Node $node the node being rendered
	 * @return string the rendered selectors
	 */
	protected function renderSelectors($node)
	{
		return join(',', $node->selectors);
	}
}
