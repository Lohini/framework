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
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\DataSources\IDataSource;

/**
 * TreeView node
 */
class TreeViewNode
extends \Nette\Application\UI\Control
{
	const STATE_COLLAPSED=0;
	const STATE_EXPANDED=1;
	/** @var mixed */
	protected $dataRow;
	/** @var int */
	protected $state;
	/** @var bool */
	protected $loaded=FALSE;
	/** @var bool */
	protected $invalid=FALSE;

	/**
	 * @param mixed $dataRow
	 */
	function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL, &$dataRow=NULL)
	{
		parent::__construct($parent, $name);
		$this->setDataRow($dataRow);
	}

    /********** handlers **********/
	function handleExpand($id=NULL)
	{
		$this->invalidate();
		$this->expand();
	}

	function handleCollapse($id=NULL)
	{
		$this->invalidate();
		$this->collapse();
	}

	function handleItem($id)
	{
		if ($this->treeView->itemDestination) {
			$this->presenter->redirect($this->treeView->itemDestination, $id);
			}
	}

	/**
	 * Iterates over datagrid rows.
	 * 
	 * @return \ArrayIterator
	 * @throws \Nette\InvalidStateException
	 */
	public function getRows()
	{
		if (!$this->treeView->dataSource instanceof IDataSource) {
			throw new \Nette\InvalidStateException('Data source is not instance of \Lohini\Database\DataSources\IDataSource. '.gettype($this->dataSource).' given.');
			}
		$ds=clone $this->treeView->getDataSource();
		if ($this->getParent() instanceof TreeViewNode && !empty($this->dataRow)) {
			$ds->filter($this->treeView->parentColumn, IDataSource::EQUAL, $this->dataRow[$this->treeView->primaryKey]);
			}
		else {
			if ($this->treeView->startParent) {
				$ds->filter($this->treeView->parentColumn, IDataSource::EQUAL, $this->treeView->startParent);
				}
			else {
				$ds->filter($this->treeView->parentColumn, IDataSource::IS_NULL);
				}
			}
		return $ds->getIterator();
	}

	protected function load()
	{
		if (!$this->loaded) {
			$this->loaded=TRUE;
			$tV=$this->treeView;
			$pcn=$tV->parentColumn;
			foreach ($this->getRows() as $dataRow) {
				if (empty($this->dataRow)
					|| (!empty($this->dataRow) && $this->dataRow['id']===$dataRow[$pcn])
					) {
					$id=$dataRow[$tV->primaryKey];
					$node=new TreeViewNode($this, $id, $dataRow);
					$node->addComponent(clone $this['nodeLink'], 'nodeLink');
					if ($tV->mode===TreeView::MODE_EXPANDED
						&& (($this->treeView->rememberState && !$node->isSessionState())
							|| !$this->treeView->rememberState
							)
						) {
						$node->expand();
						}
					}
				}
			}
	}

	/**
	 * @param string $signal
	 */
	public function signalReceived($signal)
	{
		$parent=$this->getParent();
		if ($parent instanceof TreeViewNode) {
			$parent->expand();
			}
		parent::signalReceived($signal);
	}

	/**
	 * @param string $name
	 * @return IComponent the created component (optionally)
	 */
	protected function createComponent($name)
	{
		$this->load();
		return parent::createComponent($name);
	}

	/**
	 * @return \Lohini\Components\TreeView\TreeViewLink
	 */
	protected function createComponentStateLink($name)
	{
		switch ($this->getState()) {
			case self::STATE_EXPANDED:
				$destination='collapse';
				$labelKey='-';
				break;
			case self::STATE_COLLAPSED:
				$destination='expand';
				$labelKey='+';
				break;
			}
		return new TreeViewLink($destination, $labelKey, NULL, $this->treeView->useAjax, $this);
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getNodes()
	{
		$this->load();
		return $this->getComponents(FALSE, 'Lohini\Components\TreeView\TreeViewNode');
	}

	function expand()
	{
		$this->setState(self::STATE_EXPANDED);
	}

	function collapse()
	{
		$this->setState(self::STATE_COLLAPSED);
	}

	/********** state **********/
	/**
	 * @param int $state
	 */
	public function setState($state)
	{
		$this->state=$state;
		if ($this->getTreeView()->rememberState) {
			$session=$this->getNodeSession();
			$session['state']=$state;
			}
	}

	/**
	 * @return int
	 */
	public function getState()
	{
		if ($this->state===NULL) {
			if ($this->getTreeView()->rememberState===TRUE) {
				$session=$this->getNodeSession();
				$this->state= isset($session['state'])? $session['state'] : self::STATE_COLLAPSED;
				}
			else {
				$this->state=self::STATE_COLLAPSED;
				}
			}
		return $this->state;
	}

	/**
	 * @return bool
	 */
	public function isSessionState()
	{
		$session=$this->getNodeSession();
		return isset($session['state']);
	}

	/**
	 * @return \Nette\Http\SessionSection
	 */
	protected function getNodeSession()
	{
		return $this->getPresenter()->context->session->getSection('Lohini.TreeView/'.$this->getTreeView()->getName().'/'.$this->getName());
	}

	/********** node validation **********/
	public function invalidate()
	{
		$this->invalid=TRUE;
		$this->invalidateControl();
	}

	public function validate()
	{
		$this->invalid=FALSE;
		$this->validateControl();
	}

	/**
	 * @return bool
	 */
	public function isInvalid()
	{
		return $this->invalid;
	}

	/**
	 * @return bool
	 */
	public function isLoaded()
	{
		return $this->loaded;
	}

	/********** setters **********/
	/**
	 * @param mixed $dataRow
	 */
	function setDataRow($dataRow)
	{
		$this->dataRow=$dataRow;
	}

	/**
	 * @return mixed
	 */
	function getDataRow()
	{
		return $this->dataRow;
	}

	/********** getters **********/
	/**
	 * @return IComponent
	 */
	public function getTreeView()
	{
		return $this->lookup('Lohini\Components\TreeView\TreeView');
	}
}
