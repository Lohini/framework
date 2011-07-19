<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\DataGrid\Filters;
/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Representation of data grid column selectbox filter
 */
class SelectboxFilter
extends ColumnFilter
{
	/** @var array asociative array of items in selectbox */
	protected $generatedItems;
	/** @var array asociative array of items in selectbox */
	protected $items;
	/** @var bool */
	protected $translateItems;
	/** @var bool */
	protected $firstEmpty;


	/**
	 * Selectbox filter constructor
	 * @param array $items from which to choose
	 * @param bool $firstEmpty add empty first item to selectbox?
	 * @param bool $translateItems translate all items in selectbox?
	 */
	public function __construct(array $items=NULL, $firstEmpty=TRUE, $translateItems=TRUE)
	{
		$this->items=$items;
		$this->firstEmpty=$firstEmpty;
		$this->translateItems=$translateItems;
		parent::__construct();
	}

	/**
	 * Generates selectbox items
	 * @return array
	 */
	public function generateItems()
	{
		// NOTE: don't generate if was items given in constructor
		if (is_array($this->items)) {
			return;
			}

		$dataGrid=$this->lookup('Lohini\Components\DataGrid\DataGrid');
		$items=$dataGrid->getDataSource()->getFilterItems($this->getName());
		$this->generatedItems= $this->firstEmpty? array_merge(array('' => '?'), $items) : $items;

		// if was data grid already filtered by this filter don't update with filtred items (keep full list)
		if (empty($this->element->value)) {
			$this->element->setItems($this->generatedItems);
			}

		return $this->items;
	}

	/**
	 * Returns filter's form element
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function getFormControl()
	{
		if ($this->element instanceof \Nette\Forms\Controls\BaseControl) {
			return $this->element;
			}
		$this->element=new \Nette\Forms\Controls\SelectBox($this->getName(), $this->items);

		// prepare items
		if ($this->items===NULL) {
			$this->generateItems();
			}
		// skip first item?
		if ($this->firstEmpty) {
			$this->element->setPrompt('?');
			}
		// translate items?
		if (!$this->translateItems) {
			$this->element->setTranslator(NULL);
			}

		return $this->element;
	}

	/**
	 * Translate all items in selectbox?
	 * @param bool $translate
	 * @return SelectboxFilter provides a fluent interface
	 */
	public function translateItems($translate)
	{
		$this->translateItems=(bool)$translate;
		return $this;
	}
}
