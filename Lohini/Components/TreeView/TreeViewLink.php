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

/**
 * TreeView link
 */
class TreeViewLink
extends \Nette\ComponentModel\Component
{
	/** @var \Nette\Application\UI\PresenterComponent */
	public $presenterComponent;
	/** @var string */
	public $destination;
	/** @var string */
	public $labelKey;
	/** @var string */
	public $paramKey;
	/** @var bool */
	public $useAjax;


	/**
	 * @param string $destination
	 * @param string $labelkey
	 * @param string $paramKey
	 * @param bool $useAjax
	 * @param \Nette\Application\UI\PresenterComponent $presenterComponent
	 */
	public function __construct($destination, $labelkey, $paramKey, $useAjax=FALSE, \Nette\Application\UI\PresenterComponent $presenterComponent=NULL)
	{
		$this->destination=$destination;
		$this->labelKey=$labelkey;
		$this->paramKey=$paramKey;
		$this->useAjax=$useAjax;
		$this->presenterComponent=$presenterComponent;
	}

	/**
	 * @param IComponent $node
	 */
	protected function attached($node)
	{
		if ($this->presenterComponent===NULL) {
			$this->presenterComponent=$node->presenter;
			}
		parent::attached($node);
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		$dataRow=$this->getParent()->dataRow;
		if ($this->paramKey===NULL || !isset($dataRow[$this->labelKey])) {
			return $this->labelKey;
			}
		return $dataRow[$this->labelKey];
	}

	/**
	 * @return mixed
	 */
	public function getParam()
	{
		if ($this->paramKey===NULL) {
			return NULL;
			}
		$dataRow=$this->getParent()->dataRow;
		if (!is_array($this->paramKey) && $this->getParent()->getTreeView()->recursiveMode) {
			$param='';
			$preparent=$this->getParent()->getParent();
			if ($preparent instanceof TreeViewNode && !$preparent instanceof TreeView) {
				$param.=$this->getParent()->getParent()->getComponent('nodeLink')->getParam().'/';
				}
			$param.=$dataRow[$this->paramKey];
			}
		elseif (is_array($this->paramKey)) {
			$param=array();
			foreach ($this->paramKey as $key) {
				$param[$key]=$dataRow[$key];
				}
			}
		else {
			$param=$dataRow[$this->paramKey];
			}
		return $param;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		$param=$this->getParam();
		if ($this->destination=='item') {
			return $this->lookup('Lohini\Components\TreeView\TreeView', TRUE)->link($this->destination, $param);
			}
		if ($param===NULL) {
			return $this->presenterComponent->link($this->destination);
			}
		return $this->presenterComponent->link($this->destination, $param);
	}
}
