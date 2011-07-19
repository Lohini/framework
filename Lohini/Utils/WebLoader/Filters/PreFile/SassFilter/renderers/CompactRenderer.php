<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassCompactRenderer class file.
 * @author                      Chris Yates <chris.l.yates@gmail.com>
 * @copyright   Copyright (c) 2010 PBM Web Development
 * @license                     http://phamlp.googlecode.com/files/license.txt
 * @package                     PHamlP
 * @subpackage  Sass.renderers
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass;

/**
 * CompactRenderer class.
 * Each CSS rule takes up only one line, with every property defined on that
 * line. Nested rules are placed next to each other with no newline, while
 * groups of rules have newlines between them.
 */
class CompactRenderer
extends \Lohini\WebLoader\Filters\Sass\CompressedRenderer
{
	const DEBUG_INFO_RULE='@media -sass-debug-info';
	const DEBUG_INFO_PROPERTY='font-family';
	
	/**
	 * Renders the brace between the selectors and the properties
	 * @return string the brace between the selectors and the properties
	 */
	protected function between()
	{
		return ' { ';
	}

	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end()
	{
		return " }\n";
	}

	/**
	 * Renders a comment.
	 * Comments preceeding a rule are on their own line.
	 * Comments within a rule are on the same line as the rule.
	 * @param Node $node the node being rendered
	 * @return string the rendered commnt
	 */
	public function renderComment($node)
	{
		$nl=($node->parent instanceof Sass\RuleNode? '' : "\n");
		return "$nl/* ".join("\n * ", $node->children)." */$nl" ;
	}

	/**
	 * Renders a directive.
	 * @param Node $node the node being rendered
	 * @param array $properties properties of the directive
	 * @return string the rendered directive
	 */
	public function renderDirective($node, $properties)
	{
		return str_replace("\n", '', parent::renderDirective($node, $properties))."\n\n";
	}

	/**
	 * Renders properties.
	 * @param Node $node the node being rendered
	 * @param array $properties properties to render
	 * @return string the rendered properties
	 */
	public function renderProperties($node, $properties)
	{
		return join(' ', $properties);
	}

	/**
	 * Renders a property.
	 * @param Node $node the node being rendered
	 * @return string the rendered property
	 */
	public function renderProperty($node)
	{
		return "{$node->name}: {$node->value};";
	}

	/**
	 * Renders a rule.
	 * @param Node $node the node being rendered
	 * @param array $properties rule properties
	 * @param string $rules rendered rules
	 * @return string the rendered rule
	 */
	public function renderRule($node, $properties, $rules)
	{
		return $this->renderDebug($node).parent::renderRule($node, $properties, str_replace("\n\n", "\n", $rules))."\n";
	}

	/**
	 * Renders debug information.
	 * If the node has the debug_info options set true the line number and filename
	 * are rendered in a format compatible with
	 * {@link https://addons.mozilla.org/en-US/firefox/addon/103988/ FireSass}.
	 * Else if the node has the line_numbers option set true the line number and
	 * filename are rendered in a comment.
	 * @param Node $node the node being rendered
	 * @return string the debug information
	 */
	protected function renderDebug($node)
	{
		$indent=$this->getIndent($node);
		$debug='';

		if ($node->debug_info) {
			$debug=$indent.self::DEBUG_INFO_RULE.'{'
				.'filename{'.self::DEBUG_INFO_PROPERTY.':'.preg_replace('/([^-\w])/', '\\\\\1', "file://{$node->filename}").';}'
				.'line{'.self::DEBUG_INFO_PROPERTY.":'{$node->line}';}"
				."}\n";
			}
		elseif ($node->line_numbers) {
			$debug.="$indent/* line {$node->line} {$node->filename} */\n";
			}
		return $debug; 
	}

	/**
	 * Renders rule selectors.
	 * @param Node $node the node being rendered
	 * @return string the rendered selectors
	 */
	protected function renderSelectors($node)
	{
		return join(', ', $node->selectors);
	}
}
