<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Forms\Controls;

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
