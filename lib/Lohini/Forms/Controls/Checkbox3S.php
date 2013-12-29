<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Controls;

use Nette\Utils\Html;

/**
 * 3-state checkbox input control
 *
 * @author Lopo <lopo@lohini.net>
 */
class Checkbox3S
extends \Nette\Forms\Controls\BaseControl
{
	/**
	 * @param string $label
	 */
	public function __construct($label=NULL)
	{
		parent::__construct($label);
		$this->control->type='checkbox';
		$this->value=0;
	}

	/**
	 * Returns control's value
	 *
	 * @return int
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets control's value
	 *
	 * @param int $value
	 * @return Checkbox3S (fluent)
	 * @throws \InvalidArgumentException
	 */
	public function setValue($value)
	{
		if (!in_array((int)$value, array(-1, 0, 1))) {
			throw new \InvalidArgumentException("Invalid argument passed, one of [-1, 0, 1] expected, '$value' given.");
			}
		$this->value=(int)$value;
		return $this;
	}

	/**
	 * Generates control's HTML element
	 *
	 * @return Html
	 */
	public function getControl()
	{
		$control=parent::getControl();
		$control->addClass('checkbox3s');
		$val=$this->getValue();
		$control->data('lohini-state', $val ?: 0);
		if ($val==1) {
			$control->checked='checked';
			}
		return Html::el('span')
				->add($control)
				->addClass('cb3s');
	}
}
