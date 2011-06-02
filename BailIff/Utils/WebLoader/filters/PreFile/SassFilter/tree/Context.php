<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassContext class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use BailIff\WebLoader\Filters\Sass;

/**
 * Context class.
 * Defines the context that the parser is operating in and so allows variables to be scoped.
 * A new context is created for Mixins and imported files.
 */
class Context
{
	/** @var Context enclosing context */
	protected $parent;
	/** @var array mixins defined in this context */
	protected $mixins=array();
	/** @var array variables defined in this context */
	protected $variables=array();
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
	 * @param string $name name of mixin
	 * @param $mixin
	 * @return Context the mixin
	 */
	public function addMixin($name, $mixin)
	{
		$this->mixins[$name]=$mixin;
		return $this;
	}

	/**
	 * Returns a mixin
	 * @param string $name name of mixin to return
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
		throw new Sass\ContextException("Undefined Mixin: $name", $this->node);
	}

	/**
	 * Returns a variable defined in this context
	 * @param string $name name of variable to return
	 * @return string the variable
	 * @throws ContextException if variable not defined in this context
	 */
	public function getVariable($name)
	{
		if (isset($this->variables[$name])) {
			return $this->variables[$name];
			}
		elseif (!empty($this->parent)) {
			return $this->parent->getVariable($name);
			}
		else {
			throw new Sass\ContextException("Undefined Variable: $name", $this->node);
			}
	}

	/**
	 * Returns a value indicating if the variable exists in this context
	 * @param string $name name of variable to test
	 * @return boolean true if the variable exists in this context, false if not
	 */
	public function hasVariable($name)
	{
		return isset($this->variables[$name]);
	}

	/**
	 * Sets a variable to the given value
	 * @param string $name name of variable
	 * @param Literal $value value of variable
	 * @return Context
	 */
	public function setVariable($name, $value)
	{
		$this->variables[$name]=$value;
		return $this;
	}

	/**
	 * Makes variables and mixins from this context available in the parent context.
	 * Note that if there are variables or mixins with the same name in the two
	 * contexts they will be set to that defined in this context.
	 */
	public function merge()
	{
		$this->parent->variables=array_merge($this->parent->variables, $this->variables);
		$this->parent->mixins=array_merge($this->parent->mixins, $this->mixins);
	}
}
