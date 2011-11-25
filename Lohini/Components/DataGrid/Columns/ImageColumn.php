<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\DataGrid\Columns;

/**
 * Representation of image data grid column
 *
 * @author     Roman SklenĂˇĹ™
 * @copyright  Copyright (c) 2009 Roman SklenĂˇĹ™ (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class ImageColumn
extends TextColumn
{
	/**
	 * Checkbox column constructor
	 * @param string $caption column's textual caption
	 */
	public function __construct($caption=NULL)
	{
		throw new \Nette\NotImplementedException('Class was not implemented yet.');
		parent::__construct($caption);
	}
}
