<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\DataGrid\Renderers;
/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @package    Nette\Extras\DataGrid
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Defines method that must implement data grid rendered
 */
interface IRenderer
{
	/**
	 * Provides complete data grid rendering
	 * @param Datagrid $dataGrid
	 * @return string
	 */
	function render(\Lohini\Components\DataGrid\DataGrid $dataGrid);
}
