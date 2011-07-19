<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\TreeView;
/**
 * TreeView control
 *
 * Copyright (c) 2009 Roman Novák (http://romcok.eu)
 *
 * This source file is subject to the New-BSD licence.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2009 Roman Novák
 * @author Roman Novák
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */


use Lohini\Database\DataSources\IDataSource,
	Lohini\Components\TreeView\Renderers;

/**
 * TreeView Control
 */
class TreeView
extends TreeViewNode
{
	/**#@+ mode */
	const MODE_AJAX=0;
	const MODE_EXPANDED=1;
	/**#@- */

	/** @var event */
	public $onNodeRender;
	/** @var event */
	public $onFetchDataSource;
	/** @var bool */
	public $useAjax=TRUE;
	/** @var bool */
	public $rememberState=TRUE;
	/** @var bool */
	public $recursiveMode=FALSE;
	/** @var string */
	public $labelColumn='name';
	/** @var string */
	public $primaryKey='id';
	/** @var string */
	public $parentColumn='parent_id';
	/** @var string */
	public $startParent;
	/** @var ITreeViewRenderer */
	protected $renderer;
	/** @var \Lohini\Components\TreeView\DataSources\IDataSource */
	protected $dataSource;
	/** @var int */
	protected $mode=self::MODE_AJAX;
	/** @var array used for expanded mode */
	protected $dataRows;
	/** @var string (presenter link) */
	public $itemDestination=NULL;


	/**
	 * @param string $name
	 * @return IComponent the created component (optionally)
	 */
	protected function createComponent($name)
	{
		if (!isset($this->components['nodeLink'])) {
			if (!empty($this->parent)) {
				$this->addComponent(new TreeViewLink('item', 'name', $this->primaryKey, $this->useAjax, $this->parent), 'nodeLink');
				}
			else {
				$this->addComponent(new TreeViewLink('item', 'name', $this->primaryKey, $this->useAjax), 'nodeLink');
				}
			}

		$this->load();
		return parent::createComponent($name);
	}

	/**
	 * Sets data source
	 * @param \Lohini\Database\DataSources\IDataSource $dataSource
	 * @return \Lohini\Components\TreeView\TreeView (fluent)
	 */
	public function setDataSource(IDataSource $dataSource)
	{
		if (!$dataSource instanceof IDataSource) {
			throw new \InvalidArgumentException('DataSource must implement IDataSource');
			}
		$this->dataSource=$dataSource;
		return $this;
	}

	/**
	 * Gets data source
	 * @return \Lohini\Components\TreeView\DataSources\IDataSource
	 */
	public function getDataSource()
	{
		return $this->dataSource;
	}

	/******************** rendering ********************/
	/**
	 * @param \Lohini\Components\TreeView\Renderers\IRenderer $renderer
	 * @return \Lohini\Components\TreeView\TreeView (fluent)
	 */
	public function setRenderer(Renderers\IRenderer $renderer)
	{
		$this->renderer=$renderer;
		return $this;
	}

	/**
	 * @return \Lohini\Components\TreeView\Renderers\IRenderer
	 */
	public function getRenderer()
	{
		if ($this->renderer===NULL) {
			$this->renderer=new Renderers\Conventional;
			}
		return $this->renderer;
	}

	public function render()
	{
		$this->load();
		$args=func_get_args();
		array_unshift($args, $this);
		$s=call_user_func_array(array($this->getRenderer(), 'render'), $args);

		echo mb_convert_encoding($s, 'HTML-ENTITIES', 'UTF-8');
	}

	public function __toString()
	{
		$this->load();
		$args=func_get_args();
		array_unshift($args, $this);
		$s=call_user_func_array(array($this->getRenderer(), 'render'), $args);

		echo mb_convert_encoding($s, 'HTML-ENTITIES', 'UTF-8');
	}

	/**
	 * @return int
	 */
	public function getState()
	{
		if ($this->state===NULL) {
			$this->state=self::STATE_EXPANDED;
			}
		return $this->state;
	}

	/**
	 * @return \Lohini\Components\TreeView\TreeView
	 */
	public function getTreeView()
	{
		return $this;
	}

	/**
	 * @return int
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @param int $mode
	 * @return \Lohini\Components\TreeView\TreeView (fluent)
	 */
	public function setMode($mode)
	{
		$this->mode=(int)$mode;
		return $this;
	}
}
