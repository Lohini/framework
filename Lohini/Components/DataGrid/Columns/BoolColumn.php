<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\DataGrid\Columns;

/**
 * Representation of bool data grid column
 *
 * @author Lopo <lopo@lohini.net>
 */
class BoolColumn
extends CheckboxColumn
{
	/**
	 * @param bool $value
	 * @param type $data (not used)
	 * @return string 
	 */
	public function formatContent($value, $data=NULL)
	{
		$checkbox=\Nette\Utils\Html::el('span')->class('ui-icon');
		$checkbox->addClass($value? 'ui-icon-check' : 'ui-icon-close');
		return (string)$checkbox;
	}
}
