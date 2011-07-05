<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\TreeView\Renderers;
/**
 * TreeView control
 *
 * Copyright (c) 2009, 2010 Roman Novák
 *
 * This source file is subject to the New-BSD licence.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2009, 2010 Roman Novák
 * @license    New-BSD
 */
/**
 * @author     Roman Novák
 * @copyright  Copyright (c) 2009, 2010 Roman Novák
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use Nette\Utils\Html,
	BailIff\Components\TreeView;

/**
 * TreeView renderer
 */
class Conventional
extends \Nette\Object
implements IRenderer
{
	/** @var \BailIff\Components\TreeView\TreeView */
	protected $tree;
	/** @var string */
	protected $snippetId;
	/** @var string template file */
	public $file;
	/** @var array */
	public $wrappers=array(
		'nodes' => array(
			'root' => 'ul',
			'container' => 'ul'
			),
		'node' => array(
			'icon' => 'span class="ui-icon ui-icon-document" style="float: left"',
			'container' => 'li style="padding-left: 1.5em;"',
			'.expanded' => 'expanded',
			'.collapsed' => 'collapsed'
			),
		'link' => array(
			'node' => 'a',
			'collapse' => 'a class="ui-icon ui-icon-circlesmall-minus" style="float: left"',
			'expand' => 'a class="ui-icon ui-icon-circlesmall-plus" style="float: left"',
			'.ajax' => 'ajax',
			),
		);


	public function __construct()
	{
		$this->file=__DIR__.'/conventional.latte';
	}

	/**
	 * @param \BailIff\Components\TreeView\TreeView $tree
	 * @param string $mode
	 * @return \Nette\Utils\Html
	 * @throws \Nette\InvalidStateException
	 */
	public function render(TreeView\TreeView $tree, $mode=NULL)
	{
		if (!$tree->dataSource instanceof \BailIff\Database\DataSources\IDataSource) {
			throw new \Nette\InvalidStateException('Data source is not instance of IDataSource. '.gettype($this->dataSource).' given.');
			}

		if ($this->tree!==$tree) {
			$this->tree=$tree;
			}

		if ($mode!==NULL) {
			return call_user_func_array(array($this, "render$mode"), array());
			}

		$this->snippetId=$this->tree->getSnippetId();

		if ($this->tree->isControlInvalid() && $this->tree->getPresenter()->isAjax()) {
			$this->tree->getPresenter()->getPayload()->snippets[$this->snippetId]=(string)$this->renderNodes($this->tree->getNodes(), 'nodes root');
			}
		if (!$this->tree->getPresenter()->isAjax()) {
			$template=$this->tree->getTemplate();
			$template->setFile($this->file);
			return $template->__toString(TRUE);
			}
	}

	/**
	 * @param type $nodes
	 * @param string $wrapper
	 * @return \Nette\Utils\Html
	 */
	public function renderNodes($nodes=NULL, $wrapper='nodes container')
	{
		if ($nodes===NULL) {
			$nodes=$this->tree->getNodes();
			$wrapper='nodes root';
			}
		$nodesContainer=$this->getWrapper($wrapper);
		if ($wrapper=='nodes root') {
			$nodesContainer->addClass("$this->snippetId-root");
			}
		foreach ($nodes as $n) {
			$child=$this->generateNode($n);
			if ($child!==NULL) {
				$nodesContainer->add($child);
				}
			}
		return $nodesContainer;
	}

	/**
	 * @param \BailIff\Components\TreeView\TreeViewNode $node
	 * @return \Nette\Utils\Html
	 */
	protected function generateNode(TreeView\TreeViewNode $node)
	{
		$nodes=$node->getNodes();
		$nodeContainer=$this->getWrapper('node container');
		$nodeContainer->id= $snippetId= $node->getSnippetId();
		if (count($nodes)>0) {
			switch ($node->getState()) {
				case TreeView\TreeViewNode::STATE_EXPANDED:
					$nodeContainer->addClass($this->getValue('node .expanded'));
					$stateLink=$this->generateLink($node, 'stateLink', 'link collapse');
					break;
				case TreeView\TreeViewNode::STATE_COLLAPSED:
					$nodeContainer->addClass($this->getValue('node .collapsed'));
					$stateLink=$this->generateLink($node, 'stateLink', 'link expand');
					break;
				}
			if ($stateLink!==NULL) {
				$nodeContainer->add($stateLink);
				}
			}
		else {
			$icon=$this->getWrapper('node icon');
			if ($icon!==NULL) {
				$nodeContainer->add($icon);
				}
			}
		$link=$this->generateLink($node, 'nodeLink');
		if ($link!==NULL) {
			$nodeContainer->add($link);
			}
		$this->tree->onNodeRender($this->tree, $node, $nodeContainer);
		if ($node->getState()===TreeView\TreeViewNode::STATE_EXPANDED && count($nodes)>0) {
			$nodesContainer=$this->renderNodes($nodes);
			if ($nodesContainer!==NULL) {
				$nodeContainer->add($nodesContainer);
				}
			}
		$html= isset($nodeContainer)? $nodeContainer : $nodesContainer;
		if ($node->isInvalid()) {
			$this->tree->getPresenter()->getPayload()->snippets[$snippetId]=(string)$html;
			}
		return $html;
	}

	/**
	 * @param \BailIff\Components\TreeView\TreeViewNode $node
	 * @param string $name
	 * @param string $wrapper
	 * @return \Nette\Utils\Html|NULL
	 */
	protected function generateLink(TreeView\TreeViewNode $node, $name, $wrapper='link node')
	{
		$el=$this->getWrapper($wrapper);
		if ($el===NULL) {
			return NULL;
			}
		$link=$node[$name];
		if ($link->useAjax) {
			$el->addClass($this->getValue('link .ajax'));
			}
		$el->setText($link->getLabel());
		if ($name=='stateLink') {
			$el->href($link->getUrl());
			$span=Html::el('span');
			$span->class='collapsable';
			$span->add($el);
			return $span;
			}
		$el->href($link->getUrl());
		return $el;
	}

	/**
	 * @param string $name
	 * @return \Nette\Utils\Html
	 */
	protected function getWrapper($name)
	{
		$data=$this->getValue($name);
		if (empty($data)) {
			return $data;
			}
		return $data instanceof Html ? clone $data : Html::el($data);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function getValue($name)
	{
		$name=explode(' ', $name);
		$data=&$this->wrappers[$name[0]][$name[1]];
		return $data;
	}
}
