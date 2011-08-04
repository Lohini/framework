<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassRuleNode class file.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * RuleNode class.
 * Represents a CSS rule.
 */
class RuleNode
extends Node
{
	const MATCH='/^(.+?)(?:\s*\{)?$/';
	const SELECTOR=1;
	const CONTINUED=',';
	/** @const string that is replaced with the parent node selector */
	const PARENT_REFERENCE='&';

	/** @var array selector(s) */
	private $selectors=array();
	/** @var array parent selectors */
	private $parentSelectors=array();
	/** @var array resolved selectors */
	private $resolvedSelectors=array();
	/** @var bool whether the node expects more selectors */
	private $isContinued;


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		$this->addSelectors($matches[RuleNode::SELECTOR]);
	}

	/**
	 * Adds selector(s) to the rule.
	 * If the selectors are to continue for the rule the selector must end in a comma
	 * @param string $selectors selector
	 */
	public function addSelectors($selectors)
	{
		$this->isContinued=substr($selectors, -1)===self::CONTINUED;
		$this->selectors=array_merge($this->selectors, $this->explode($selectors));
	}

	/**
	 * Returns a value indicating if the selectors for this rule are to be continued.
	 * @param bool selectors for this rule are to be continued ?
	 */
	public function getIsContinued()
	{
		return $this->isContinued;
	}

	/**
	 * Parse this node and its children into static nodes.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node and its children
	 */
	public function parse($context)
	{
		$node=clone $this;
		$node->selectors=$this->resolveSelectors($context);
		$node->children=$this->parseChildren($context);
		return array($node);
	}

	/**
	 * Render this node and its children to CSS.
	 * @return string the rendered node
	 */
	public function render()
	{
		$this->extend();
		$rules='';
		$properties=array();

		foreach ($this->children as $child) {
			$child->parent=$this;
			if ($child instanceof RuleNode) {
				$rules.=$child->render();
				}
			else {
				$properties[]=$child->render();
				}
			}

		return $this->renderer->renderRule($this, $properties, $rules);
	}

	/**
	 * Extend this nodes selectors
	 * $extendee is the subject of the @extend directive
	 * $extender is the selector that contains the @extend directive
	 * $selector a selector or selector sequence that is to be extended 
	 */
	public function extend()
	{
		foreach ($this->root->extenders as $extendee => $extenders) {
			if ($this->isPsuedo($extendee)) {
				$extendee=explode(':', $extendee);
				$pattern=preg_quote($extendee[0]).'((\.[-\w]+)*):'.preg_quote($extendee[1]);
				}
			else {
				$pattern=preg_quote($extendee);
				}
			foreach (preg_grep('/'.$pattern.'/', $this->selectors) as $selector) {
				foreach ($extenders as $extender) {
					if (is_array($extendee)) {
						$this->selectors[]=preg_replace('/(.*?)'.$pattern.'$/', "\\1$extender\\2", $selector);
						}
					elseif ($this->isSequence($extender) || $this->isSequence($selector)) {
						$this->selectors=array_merge($this->selectors, $this->mergeSequence($extender, $selector));
						}
					else {
						$this->selectors[]=str_replace($extendee, $extender, $selector);
						}
					}
				}
			}
	}

	/**
	 * Tests whether the selector is a psuedo selector
	 * @param string $selector selector to test
	 * @return bool
	 */
	private function isPsuedo($selector)
	{
		return strpos($selector, ':')!==FALSE;
	}

	/**
	 * Tests whether the selector is a sequence selector
	 * @param string $selector selector to test
	 * @return bool
	 */
	private function isSequence($selector)
	{
		return strpos($selector, ' ')!==FALSE;
	}

	/**
	 * Merges selector sequences
	 * @param string $extender the extender selector
	 * @param string $selector selector to extend
	 * @return array the merged sequences 
	 */
	private function mergeSequence($extender, $selector)
	{
		$extender=explode(' ', $extender);
		$end=' '.array_pop($extender);
		$selector=explode(' ', $selector);
		array_pop($selector);

		$common=array();
		while ($extender[0]===$selector[0]) {
			$common[]=array_shift($selector);
			array_shift($extender);
			}

		$begining= !empty($common)? join(' ', $common).' ' : '';

		return array(
			$begining.join(' ', $selector).' '.join(' ', $extender).$end,
			$begining.join(' ', $extender).' '.join(' ', $selector).$end
			);
	}

	/**
	 * Returns the selectors
	 * @return array selectors
	 */
	public function getSelectors()
	{
		return $this->selectors;
	}

	/**
	 * Resolves selectors.
	 * Interpolates Sass\Script in selectors and resolves any parent references or
	 * appends the parent selectors.
	 * @param Context $context the context in which this node is parsed
	 * @return array
	 */
	public function resolveSelectors($context)
	{
		$resolvedSelectors=array();
		$this->parentSelectors=$this->getParentSelectors($context);

		foreach ($this->selectors as $key => $selector) {
			$selector=$this->interpolate($selector, $context);
			//$selector = $this->evaluate($this->interpolate($selector, $context), $context)->toString();
			if ($this->hasParentReference($selector)) {
				$resolvedSelectors=array_merge($resolvedSelectors, $this->resolveParentReferences($selector, $context));
				}
			elseif ($this->parentSelectors) {
				foreach ($this->parentSelectors as $parentSelector) {
					$resolvedSelectors[]="$parentSelector $selector";
					}
				}
			else {
				$resolvedSelectors[]=$selector;
				}
			}
		sort($resolvedSelectors);
		return $resolvedSelectors;
	}

	/**
	 * Returns the parent selector(s) for this node.
	 * This in an empty array if there is no parent selector.
	 * @param $context
	 * @return array the parent selector for this node
	 */
	protected function getParentSelectors($context)
	{
		$ancestor=$this->parent;
		while (!$ancestor instanceof RuleNode && $ancestor->hasParent()) {
			$ancestor=$ancestor->parent;
			}
		if ($ancestor instanceof RuleNode) {
			return $ancestor->resolveSelectors($context);
			}
		return array();
	}

	/**
	 * Returns the position of the first parent reference in the selector.
	 * If there is no parent reference in the selector this function returns
	 * boolean FALSE.
	 * Note that the return value may be non-Boolean that evaluates to FALSE,
	 * i.e. 0. The return value should be tested using the === operator.
	 * @param string $selector selector to test
	 * @return mixed int: position of the the first parent reference,
	 * bool: false if there is no parent reference.
	 */
	private function parentReferencePos($selector)
	{
		$inString='';
		for ($i=0, $l=strlen($selector); $i<$l; $i++) {
			$c=$selector[$i];
			if ($c===self::PARENT_REFERENCE && empty($inString)) {
				return $i;
				}
			elseif (empty($inString) && ($c==='"' || $c==="'")) {
				$inString=$c;
				}
			elseif ($c===$inString) {
				$inString='';
				}
			}
		return FALSE;
	}

	/**
	 * Determines if there is a parent reference in the selector
	 * @param string $selector selector
	 * @return bool true if there is a parent reference in the selector
	 */
	private function hasParentReference($selector)
	{
		return $this->parentReferencePos($selector)!==FALSE;
	}

	/**
	 * Resolves parent references in the selector
	 * @param string $selector selector
	 * @param $context
	 * @return string selector with parent references resolved
	 * @throws RuleNodeException
	 */
	private function resolveParentReferences($selector, $context)
	{
		$resolvedReferences=array();
		if (!count($this->parentSelectors)) {
			throw new RuleNodeException('Can not use parent selector ('.self::PARENT_REFERENCE.') when no parent selectors', $this);
			}
		foreach ($this->getParentSelectors($context) as $parentSelector) {
			$resolvedReferences[]=str_replace(self::PARENT_REFERENCE, $parentSelector, $selector);
			}
		return $resolvedReferences;
	}

	/**
	 * Explodes a string of selectors into an array.
	 * We can't use PHP::explode as this will potentially explode attribute
	 * matches in the selector, e.g. div[title="some,value"] and interpolations.
	 * @param string selectors
	 * @return array selectors
	 */
	private function explode($string)
	{
		$selectors=array();
		$inString=FALSE;
		$interpolate=FALSE;
		$selector='';

		for ($i=0, $l=strlen($string); $i<$l; $i++) {
			$c=$string[$i];
			if ($c===self::CONTINUED && !$inString && !$interpolate) {
				$selectors[]=trim($selector);
				$selector='';
				}
			else {
				$selector.=$c;
				if ($c==='"' || $c==="'") {
					do {
						$_c=$string[++$i];
						$selector.=$_c;
						} while ($_c!==$c);
					}
				elseif ($c==='#' && $string[$i+1]==='{') {
					do {
						$c=$string[++$i];
						$selector.=$c;
						} while ($c!=='}');
					}
				}
			}

		if (!empty($selector)) {
			$selectors[]=trim($selector);
			}
		return $selectors;
	}
}
