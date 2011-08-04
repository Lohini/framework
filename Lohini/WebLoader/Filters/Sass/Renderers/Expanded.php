<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Renderers;
/**
 * SassExpandedRenderer class file.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Expanded Renderer class.
 * Expanded is the typical human-made CSS style, with each property and rule
 * taking up one line. Properties are indented within the rules, but the rules
 * are not indented in any special way.
 */
class Expanded
extends Compact
{
	/**
	 * Renders the brace between the selectors and the properties
	 * @return string the brace between the selectors and the properties
	 */
	protected function between()
	{
		return " {\n" ;
	}

	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end()
	{
		return "\n}\n\n";
	}

	/**
	 * Renders a comment.
	 * @param \Lohini\WebLoader\Filters\Sass\Tree\Node $node the node being rendered
	 * @return string the rendered commnt
	 */
	public function renderComment($node)
	{
		$indent=$this->getIndent($node);
		$lines=explode("\n", $node->value);
		foreach ($lines as &$line) {
			$line=trim($line);
			}
		return "$indent/*\n$indent * ".join("\n$indent * ", $lines)."\n$indent */".(empty($indent)? "\n" : '');
	}

	/**
	 * Renders properties.
	 * @param \Lohini\WebLoader\Filters\Sass\Tree\Node $node
	 * @param array $properties properties to render
	 * @return string the rendered properties
	 */
	public function renderProperties($node, $properties)
	{
		$indent=$this->getIndent($node).self::INDENT;
		return $indent.join("\n$indent", $properties);
	}
}
