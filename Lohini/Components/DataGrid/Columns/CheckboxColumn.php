<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\DataGrid\Columns;
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
 * Representation of checkbox data grid column
 */
class CheckboxColumn
extends NumericColumn
{
	/**
	 * Checkbox column constructor
	 * @param string $caption column's textual caption
	 */
	public function __construct($caption=NULL)
	{
		parent::__construct($caption, 0);
		$this->getCellPrototype()->style('text-align: center');
	}

	/**
	 * Formats cell's content
	 * @param mixed $value
	 * @param \DibiRow|array $data
	 * @return string
	 */
	public function formatContent($value, $data=NULL)
	{
		$checkbox=\Nette\Utils\Html::el('input')->type('checkbox')->disabled('disabled');
		if ($value) {
			$checkbox->checked=TRUE;
			}
		return (string)$checkbox;
	}

	/**
	 * Filters data source
	 * 
	 * @param mixed $value
	 */
	public function applyFilter($value)
	{
		if (!$this->hasFilter()) {
			return;
			}

		$dataSource=$this->getDataGrid()->getDataSource();
		$value=(boolean)$value;
		if ($value) {
			$dataSource->filter($this->name, '>=', $value);
			}
		else {
			$dataSource->filter($this->name, array('=', 'IS NULL'), $value, 'OR');
			}
	}
}
