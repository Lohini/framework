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
 * Representation of numeric data grid column
 */
class NumericColumn
extends Column
{
	/** @var int */
	public $precision;


	/**
	 * Checkbox column constructor
	 * @param string $caption column's textual caption
	 * @param string $precision number of digits after the decimal point
	 */
	public function __construct($caption=NULL, $precision=2)
	{
		parent::__construct($caption);
		$this->precision=$precision;
	}

	/**
	 * Formats cell's content
	 * @param mixed $value
	 * @param \DibiRow|array $data
	 * @return string
	 */
	public function formatContent($value, $data=NULL)
	{
		$value=round($value, $this->precision);

		if (is_array($this->replacement) && !empty($this->replacement)) {
			if (in_array($value, array_keys($this->replacement))) {
				$value=$this->replacement[$value];
				}
			}

		foreach ($this->formatCallback as $callback) {
			if (is_callable($callback)) {
				$value=call_user_func($callback, $value, $data);
				}
			}

		return $value;
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

		$dataGrid=$this->getDataGrid();

		if ($value==='NULL' || $value==='NOT NULL') {
			$dataGrid->getDataSource()->filter($this->name, "IS $value");
			}
		else {
			$operator='=';
			$v=str_replace(',', '.', $value);

			if (preg_match('/^(?<operator>\>|\>\=|\<|\<\=|\=|\<\>)?(?<value>[\.|\d]+)$/', $v, $matches)) {
				if (isset($matches['operator']) && !empty($matches['operator'])) {
					$operator=$matches['operator'];
					}
				$value=$matches['value'];
				}
			$dataGrid->getDataSource()->filter($this->name, $operator, (float) $value); //or skip converting?
			}
	}
}
