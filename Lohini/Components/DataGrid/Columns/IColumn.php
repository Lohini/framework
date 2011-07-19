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
 * Defines method that must be implemented to allow a component act like a data grid column
 */
interface IColumn
{
	/**
	 * Is column orderable?
	 * @return bool
	 */
	function isOrderable();
	/**
	 * Gets header link (order signal)
	 * @param string $dir
	 * @return string
	 */
	function getOrderLink($dir=NULL);
	/**
	 * Has column filter box?
	 * @return bool
	 */
	function hasFilter();
	/**
	 * Returns column's filter
	 * @return \Lohini\Components\DataGrid\Filters\IColumnFilter|NULL
	 */
	function getFilter();
	/**
	 * Formats cell's content
	 * @param mixed $value
	 * @return string
	 */
	function formatContent($value, $data=NULL);
	/**
	 * Filters data source
	 * @param mixed $value
	 */
	function applyFilter($value);
}
