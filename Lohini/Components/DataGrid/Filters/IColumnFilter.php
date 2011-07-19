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
 * Defines method that must be implemented to allow a component act like a data grid column's filter
 */
interface IColumnFilter
{
	/**
	 * Returns filter's form element
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	function getFormControl();
	/**
	 * Gets filter's value, if was filtered
	 * @return string
	 */
	public function getValue();
}
