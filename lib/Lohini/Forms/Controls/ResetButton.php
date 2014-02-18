<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
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
