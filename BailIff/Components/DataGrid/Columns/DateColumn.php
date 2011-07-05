<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\DataGrid\Columns;
/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Representation of date data grid column.
 */
class DateColumn
extends TextColumn
{
	/** @var string */
	public $format;


	/**
	 * Date column constructor.
	 * @param string column's textual caption
	 * @param string date format supported by PHP strftime()
	 */
	public function __construct($caption=NULL, $format='%x')
	{
		parent::__construct($caption);
		$this->format=$format;
	}

	/**
	 * Formats cell's content.
	 * @param mixed
	 * @param \DibiRow|array
	 * @return string
	 */
	public function formatContent($value, $data=NULL)
	{
		if ((int)$value==NULL || empty($value)) {
			return 'N/A';
			}
		$value=parent::formatContent($value, $data);

		$value= is_numeric($value)? (int)$value : ($value instanceof \DateTime ? $value->format('U') : strtotime($value));
		return strftime($this->format, $value);
	}

	/**
	 * Applies filtering on dataset.
	 * @param mixed
	 */
	public function applyFilter($value)
	{
		if (!$this->hasFilter()) {
			return;
			}

		$this->getDataGrid()->getDataSource()->filter($this->name, '=', $value);
	}
}
