<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassPropertyNode class file.
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
 * PropertyNode class.
 * Represents a CSS property.
 */
class PropertyNode
extends Node
{
	const MATCH_PROPERTY_NEW='/^([^\s=:"]+)\s*(?:(= )|:)(.*?)$/';
	const MATCH_PROPERTY_OLD='/^:([^\s=:]+)(?:\s*(=)\s*|\s+|$)(.*)/';
	const MATCH_PSUEDO_SELECTOR='/^:?\w[-\w]+\(?/i';
	const MATCH_INTERPOLATION='/^#\{(.*?)\}/i';
	const NAME=1;
	const SCRIPT=2;
	const VALUE=3;
	const IS_SCRIPT='= ';

	/** @var array */
	private static $psuedoSelectors=array(
		'root',
		'nth-child(',
		'nth-last-child(',
		'nth-of-type(',
		'nth-last-of-type(',
		'first-child',
		'last-child',
		'first-of-type',
		'last-of-type',
		'only-child',
		'only-of-type',
		'empty',
		'link',
		'visited',
		'active',
		'hover',
		'focus',
		'target',
		'lang(',
		'enabled',
		'disabled',
		'checked',
		':first-line',
		':first-letter',
		':before',
		':after',
		// CSS 2.1
		'first-line',
		'first-letter',
		'before',
		'after'
		);
	/** @var string property name */
	private $name;
	/** @var string property value or expression to evaluate */
	private $value;


	/**
	 * @param object $token source token
	 * @param string $syntax property syntax
	 */
	public function __construct($token, $syntax='new')
	{
		parent::__construct($token);
		$matches=self::match($token, $syntax);
		$this->name=$matches[self::NAME];
		$this->value=$matches[self::VALUE];
		if ($matches[self::SCRIPT]===self::IS_SCRIPT) {
			$this->addWarning('Setting CSS properties with "=" is deprecated; use "{name}: {value};"', array('{name}'=>$this->name, '{value}'=>$this->value));
			}
	}

	/**
	 * Parse this node.
	 * If the node is a property namespace return all parsed child nodes. If not
	 * return the parsed version of this node.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node
	 */
	public function parse($context)
	{
		$return=array();
		if ($this->value) {
			$node=clone $this;
			$node->name= ($this->inNamespace()? "{$this->namespace}-" : '').$this->interpolate($this->name, $context);
			$node->value=$this->evaluate($this->interpolate($this->value, $context), $context, Sass\ScriptParser::CSS_PROPERTY)->toString();
			if (array_key_exists($node->name, $this->vendor_properties)) {
				foreach ($this->vendor_properties[$node->name] as $vendorProperty) {
					$_node=clone $node;
					$_node->name=$vendorProperty;
					$return[]=$_node;
					}
				}
			$return[]=$node;
			}
		if ($this->children) {
			$return=array_merge($return, $this->parseChildren($context));
			}
		return $return; 
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render()
	{
		return $this->renderer->renderProperty($this);
	}

	/**
	 * Returns a value indicating if this node is in a namespace
	 * @return boolean true if this node is in a property namespace, false if not
	 */
	public function inNamespace()
	{
		$parent=$this->parent;
		do {
			if ($parent instanceof Sass\PropertyNode) {
				return TRUE;
				}
			$parent=$parent->parent;
			} while (is_object($parent));
		return FALSE;
	}

	/**
	 * Returns the namespace for this node
	 * @return string the namespace for this node
	 */
	protected function getNamespace()
	{
		$namespace=array();
		$parent=$this->parent;
		do {
			if ($parent instanceof Sass\PropertyNode) {
				$namespace[]=$parent->name;
				}
			$parent=$parent->parent;
			} while (is_object($parent));
		return join('-', array_reverse($namespace));
	}

	/**
	 * Returns the name of this property.
	 * If the property is in a namespace the namespace is prepended
	 * @return string the name of this property
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the parsed value of this property.
	 * @return string the parsed value of this property
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object $token token
	 * @param string $syntax the property syntax being used
	 * @return boolean true if the token represents this type of node, false if not
	 * @throws PropertyNodeException
	 */
	public static function isa($token/*, $syntax*/)
	{
		$matches=self::match($token, /*$syntax*/func_get_arg(1));

		if (!empty($matches)) {	
			if (isset($matches[self::VALUE]) && self::isPseudoSelector($matches[self::VALUE])) {
				return FALSE;
				}
		  	if ($token->level===0) {
		  		throw new Sass\PropertyNodeException('Properties can not be assigned at root level', $this);
		  		}
		  	else {
		  		return TRUE;
		  		}
			}
		else {
			return FALSE;
			}
	}

	/**
	 * Returns the matches for this type of node.
	 * @param array $token the line to match
	 * @param string $syntax the property syntax being used
	 * @return array matches
	 */
	public static function match($token, $syntax)
	{
		switch ($syntax) {
			case 'new':
				preg_match(self::MATCH_PROPERTY_NEW, $token->source, $matches);
				break;
			case 'old':
				preg_match(self::MATCH_PROPERTY_OLD, $token->source, $matches);
				break;
			default:
				if (preg_match(self::MATCH_PROPERTY_NEW, $token->source, $matches)==0) {
					preg_match(self::MATCH_PROPERTY_OLD, $token->source, $matches);
					}
				break;
			}
		return $matches;
	}
	
	/**
	 * Returns a value indicating if the string starts with a pseudo selector.
	 * This is used to reject pseudo selectors as property values as, for example,
	 * "a:hover" and "text-decoration:underline" look the same to the property
	 * match regex.
	 * It will also match interpolation to allow for constructs such as
	 * content:#{$pos}
	 * @see isa() 
	 * @param string the string to test
	 * @return bool true if the string starts with a pseudo selector, false if not
	 */
	private static function isPseudoSelector($string)
	{
		preg_match(self::MATCH_PSUEDO_SELECTOR, $string, $matches);
		return (isset($matches[0]) && in_array($matches[0], self::$psuedoSelectors))
			|| preg_match(self::MATCH_INTERPOLATION, $string);
	}
}
