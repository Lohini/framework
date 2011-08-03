<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Renderers;
/**
 * SassNestedRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Nested Renderer class.
 * Nested style is the default Sass style, because it reflects the structure of
 * the document in much the same way Sass does. Each rule is indented based on
 * how deeply it's nested. Each property has its own line and is indented
 * within the rule. 
 */
class Nested
extends Expanded
{	
	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end()
	{
		return " }\n";
	}

	/**
	 * Returns the indent string for the node
	 * @param \Lohini\WebLoader\Filters\Sass\Tree\Node $node the node being rendered
	 * @return string the indent string for this Node
	 */
	protected function getIndent($node)
	{
		return str_repeat(self::INDENT, $node->level);
	}

	/**
	 * Renders a directive.
	 * @param \Lohini\WebLoader\Filters\Sass\Tree\Node $node the node being rendered
	 * @param array $properties properties of the directive
	 * @return string the rendered directive
	 */
	public function renderDirective($node, $properties)
	{
		$directive=$this->getIndent($node).$node->directive.$this->between().$this->renderProperties($node, $properties);
		return preg_replace('/(.*})\n$/', '\1', $directive).$this->end();
	}

	/**
	 * Renders rule selectors.
	 * @param \Lohini\WebLoader\Filters\Sass\Tree\Node $node the node being rendered
	 * @return string the rendered selectors
	 */
	protected function renderSelectors($node)
	{
		$indent=$this->getIndent($node);
		return $indent.join(",\n$indent", $node->selectors);
	}
}
