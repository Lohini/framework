<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassContext class file.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Context class.
 * Defines the context that the parser is operating in and so allows variables to be scoped.
 * A new context is created for Mixins, Functions and imported files.
 */
class Context
{
	/** @var Context enclosing context */
	protected $parent;
	/** @var array mixins defined in this context */
	protected $mixins=array();
	/** @var array variables defined in this context */
	protected $variables=array();
	/** @var array functions defined in this context */
	protected $functions=array();
	/** @var Node the node being processed */
	public $node; 


	/**
	 * @param Context $parent - the enclosing context
	 */
	public function __construct($parent=NULL)
	{
		$this->parent=$parent;
	}

	/**
	 * Adds a mixin
	 * @param string $name of mixin
	 * @param MixinDefinitionNode $mixin
	 * @return Context the mixin
	 */
	public function addMixin($name, $mixin)
	{
		$this->mixins[$name]=$mixin;
		return $this;
	}

	/**
	 * Returns a mixin
	 * @param string $name of mixin to return
	 * @return MixinDefinitionNode the mixin
	 * @throws ContextException if mixin not defined in this context
	 */
	public function getMixin($name)
	{
		if (isset($this->mixins[$name])) {
			return $this->mixins[$name];
			}
		elseif (!empty($this->parent)) {
			return $this->parent->getMixin($name);
			}
		throw new ContextException("Undefined Mixin: $name", $this->node);
	}

	/**
	 * Returns a variable defined in this context
	 * @param string $name of variable to return
	 * @return string the variable
	 * @throws ContextException if variable not defined in this context
	 */
	public function getVariable($name)
	{
		$name=str_replace('-', '_', $name);
		if (isset($this->variables[$name])) {
			return $this->variables[$name];
			}
		elseif (!empty($this->parent)) {
			return $this->parent->getVariable($name);
			}
		else {
			throw new ContextException("Undefined Variable: $name", $this->node);
			}
	}

	/**
	 * Returns a value indicating if the variable exists in this context
	 * @param string $name of variable to test
	 * @return bool
	 */
	public function hasVariable($name)
	{
		return isset($this->variables[str_replace('-', '_', $name)]);
	}

	/**
	 * Sets a variable to the given value
	 * @param string $name of variable
	 * @param Literal $value of variable
	 * @return Context
	 */
	public function setVariable($name, $value)
	{
		$this->variables[str_replace('-', '_', $name)]=$value;
		return $this;
	}

	/**
	 * Makes variables, mixins and functions from this context available in the parent context.
	 * Note that if there are variables or mixins or functions with the same name in the two
	 * contexts they will be set to that defined in this context.
	 */
	public function merge()
	{
		$this->parent->variables=array_merge($this->parent->variables, $this->variables);
		$this->parent->mixins=array_merge($this->parent->mixins, $this->mixins);
		$this->parent->functions=array_merge($this->parent->functions, $this->functions);
	}

	/**
	 * Adds a function
	 * @param string $name of function
	 * @param FunctionNode $function
	 * @return Context the function
	 */
	public function addFunction($name, $function)
	{
		$this->functions[$name]=$function;
		return $this;
	}

	/**
	 * Returns a value indicating if the function exists in this context
	 * @param string $name of function to test
	 * @return bool
	 */
	public function hasFunction($name)
	{
		return isset($this->functions[$name]);
	}

	/**
	 * Returns a function
	 * @param string $name of function to return
	 * @return FunctionNode the function
	 * @throws ContextException if function not defined in this context
	 */
	public function getFunction($name)
	{
		if (isset($this->functions[$name])) {
			return $this->functions[$name];
			}
		elseif (!empty($this->parent)) {
			return $this->parent->getFunction($name);
			}
		throw new ContextException("Undefined Function: $name", $this->node);
	}
}
