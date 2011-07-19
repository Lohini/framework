<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Controls;

class ResetButton
extends \Nette\Forms\Controls\Button
{
	/**
	 * @param string $caption
	 */
	public function __construct($caption=NULL)
	{
		parent::__construct($caption);
		$this->control->type='reset';
		$this->control->class='button';
	}
}
