<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\DataGrid\Columns;

/**
 * Representation of bool data grid column
 *
 * @author Lopo <lopo@losys.eu>
 */
class BoolColumn
extends CheckboxColumn
{
	public function FormatContent($value, $data=NULL)
	{
		$checkbox=\Nette\Utils\Html::el('span')->class('ui-icon');
		$checkbox->addClass($value? 'ui-icon-check' : 'ui-icon-close');
		return (string)$checkbox;
	}
}
