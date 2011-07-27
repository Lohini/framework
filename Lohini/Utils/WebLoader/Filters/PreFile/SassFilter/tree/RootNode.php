<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassRootNode class file.
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
 * RootNode class.
 * Also the root node of a document.
 */
class RootNode
extends Node
{
	/** @var ScriptParser Script parser */
	protected $script;
	/** @var Renderer the renderer for this node */
	protected $renderer;
	/** @var Parser */
	protected $parser;
	/** @var array extenders for this tree in the form extendee=>extender */
	protected $extenders=array();


	/**
	 * @param Parser $parser Sass parser
	 */
	public function __construct($parser)
	{
		parent::__construct((object)array(
			'source' => '',
			'level' => -1,
			'filename' => $parser->filename,
			'line' => 0,
			));
		$this->parser=$parser;
		$this->script=new Sass\ScriptParser;
		$this->renderer=Sass\Renderer::getRenderer($parser->style);
		$this->root=$this;
	}

	/**
	 * Parses this node and its children into the render tree.
	 * Dynamic nodes are evaluated, files imported, etc.
	 * Only static nodes for rendering are in the resulting tree.
	 * @param Context the context in which this node is parsed
	 * @return Node root node of the render tree
	 */
	public function parse($context)
	{
		$node=clone $this;
		$node->children=$this->parseChildren($context);
		return $node;
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render()
	{
		$node=$this->parse(new Sass\Context);
		$output='';
		foreach ($node->children as $child) {
			$output.=$child->render();
			} // foreach
		return $output;
	}

	/**
	 * @param unknown_type $extendee
	 * @param unknown_type $selectors
	 */
	public function extend($extendee, $selectors)
	{
		$this->extenders[$extendee]= isset($this->extenders[$extendee])? array_merge($this->extenders[$extendee], $selectors) : $selectors;
	}
	
	public function getExtenders()
	{
		return $this->extenders;  
	} 

	/**
	 * Returns a value indicating if the line represents this type of node.
	 * Child classes must override this method.
	 * @throws NodeException if not overriden
	 */
	public static function isa($line)
	{
		throw new Sass\NodeException('Child classes must override this method');
	}
}
