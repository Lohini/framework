<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Node class.
 * Base class for all Sass nodes.
 */
class Node
{
	/** @var Node parent of this node */
	protected $parent;
	/** @var Node root node */
	protected $root;
	/** @var array children of this node */
	protected $children=array();
	/** @var object source token */
	protected $token;
	

	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		$this->token=$token;
	}
	
	/**
	 * Getter
	 * @param string $name name of property to get
	 * @return mixed return value of getter function
	 * @throws NodeException
	 */
	public function __get($name)
	{
		$getter='get'.ucfirst($name);
		if (method_exists($this, $getter)) {
			return $this->$getter();
			}
		throw new NodeException("No getter function for $name", $this);
	}

	/**
	 * Setter.
	 * @param string $name name of property to set
	 * @return mixed $value value of property
	 * @return Node this node
	 * @throws NodeException
	 */
	public function __set($name, $value)
	{
		$setter='set'.ucfirst($name);
		if (method_exists($this, $setter)) {
			$this->$setter($value);
			return $this;
			}
		throw new NodeException("No setter function for $name", $this);
	}

	/**
	 * Resets children when cloned
	 * @see parse()
	 */
	public function __clone()
	{
		$this->children=array();
	}

	/**
	 * Return a value indicating if this node has a parent
	 * @return array the node's parent
	 */
	public function hasParent()
	{
		return !empty($this->parent);
	}

	/**
	 * Returns the node's parent
	 * @return array the node's parent
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Adds a child to this node.
	 * @return Node $child the child to add
	 * @throws Exception
	 */
	public function addChild($child)
	{
		if ($child instanceof ElseNode) {
			if (!$this->lastChild instanceof IfNode) {
				throw new \Lohini\WebLoader\Filters\Sass\Exception('@else(if) directive must come after @(else)if', $child);
				}
			$this->lastChild->addElse($child);
			}
		else {
			$this->children[]=$child;
			$child->parent=$this;
			$child->root=$this->root;			
			}
		// The child will have children if a debug node has been added
		foreach ($child->children as $grandchild) {
			$grandchild->root=$this->root;		
			}
	}

	/**
	 * Returns a value indicating if this node has children
	 * @return boolean true if the node has children, false if not
	 */
	public function hasChildren()
	{
		return !empty($this->children);
	}

	/**
	 * Returns the node's children
	 * @return array the node's children
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * Returns a value indicating if this node is a child of the passed node.
	 * This just checks the levels of the nodes. If this node is at a greater
	 * level than the passed node if is a child of it.
	 * @return boolean true if the node is a child of the passed node, false if not
	 */
	public function isChildOf($node)
	{
		return $this->level>$node->level;
	}

	/**
	 * Returns the last child node of this node.
	 * @return Node the last child node of this node
	 */
	public function getLastChild()
	{
		return $this->children[count($this->children)-1];
	}

	/**
	 * Returns the level of this node.
	 * @return integer the level of this node
	 */
	private function getLevel()
	{
		return $this->token->level;
	}

	/**
	 * Returns the source for this node
	 * @return string the source for this node
	 */
	private function getSource()
	{
		return $this->token->source;
	}

	/**
	 * Returns the debug_info option setting for this node
	 * @return boolean the debug_info option setting for this node
	 */
	private function getDebug_info()
	{
		return $this->parser->debug_info;
	}

	/**
	 * Returns the line number for this node
	 * @return string the line number for this node
	 */
	private function getLine()
	{
		return $this->token->line;
	}

	/**
	 * Returns the line_numbers option setting for this node
	 * @return boolean the line_numbers option setting for this node
	 */
	private function getLine_numbers()
	{
		return $this->parser->line_numbers;
	}

	/**
	 * Returns vendor specific properties
	 * @return array vendor specific properties
	 */
	private function getVendor_properties()
	{
		return $this->parser->vendor_properties;
	}

	/**
	 * Returns the filename for this node
	 * @return string the filename for this node
	 */
	private function getFilename()
	{
		return $this->token->filename;
	}

	/**
	 * Returns the Sass parser
	 * @return Parser the Sass parser
	 */
	public function getParser()
	{
		return $this->root->parser;
	}

	/**
	 * Returns the property syntax being used.
	 * @return string the property syntax being used
	 */
	public function getPropertySyntax()
	{
		return $this->root->parser->propertySyntax;
	}

	/**
	 * Returns the Sass\Script parser
	 * @return ScriptParser the Sass\Script parser
	 */
	public function getScript()
	{
		return $this->root->script;
	}

	/**
	 * Returns the renderer
	 * @return Renderer the renderer
	 */
	public function getRenderer()
	{
		return $this->root->renderer;
	}

	/**
	 * Returns the render style of the document tree.
	 * @return string the render style of the document tree
	 */
	public function getStyle()
	{
		return $this->root->parser->style;
	}

	/**
	 * Returns a value indicating whether this node is in a directive
	 * @param boolean true if the node is in a directive, false if not
	 */
	public function inDirective()
	{
		return $this->parent instanceof DirectiveNode
				|| $this->parent instanceof DirectiveNode; // XXX: ???
	}

	/**
	 * Returns a value indicating whether this node is in a Sass\Script directive
	 * @param bool true if this node is in a Sass\Script directive, false if not
	 */
	public function inSassScriptDirective()
	{
		return $this->parent instanceof EachNode
				|| $this->parent->parent instanceof EachNode
				|| $this->parent instanceof ForNode
				|| $this->parent->parent instanceof ForNode
				|| $this->parent instanceof IfNode
				|| $this->parent->parent instanceof IfNode
				|| $this->parent instanceof WhileNode
				|| $this->parent->parent instanceof WhileNode;
	}

	/**
	 * Evaluates a Sass\Script expression.
	 * @param string $expression expression to evaluate
	 * @param Context $context the context in which the expression is evaluated
	 * @param $x
	 * @return Literal value of parsed expression
	 */
	protected function evaluate($expression, $context, $x=NULL)
	{
		$context->node=$this;
		return $this->script->evaluate($expression, $context, $x);
	}

	/**
	 * Replace interpolated Sass\Script contained in '#{}' with the parsed value.
	 * @param string $expression the text to interpolate
	 * @param Context $context the context in which the string is interpolated
	 * @return string the interpolated text
	 */
	protected function interpolate($expression, $context)
	{
		$context->node=$this;
		return $this->script->interpolate($expression, $context);
	}
	
	/**
	 * Adds a warning to the node. 
	 * @param string $message warning message
	 * @param array $params line
	 */
	public function addWarning($message, $params=array())
	{
		$this->addChild(new DebugNode($this->token, $message, $params));
	}

	/**
	 * Parse the children of the node.
	 * @param Context $context the context in which the children are parsed
	 * @return array the parsed child nodes
	 */
	protected function parseChildren($context)
	{
		$children=array();
		foreach ($this->children as $child) {
			$children=array_merge($children, $child->parse($context));
			}
		return $children; 
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object $token token
	 * @return boolean true if the token represents this type of node, false if not
	 * @throws NodeException
	 */
	public static function isa($token)
	{
		throw new NodeException('Child classes must override this method');
	}
}
