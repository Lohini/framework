<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Forms\Controls;

use Nette\Forms\Controls\Button;

class ResetButton
extends Button
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
