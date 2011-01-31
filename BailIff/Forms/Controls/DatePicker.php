<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Forms;

use Nette\Forms\TextInput,
	Nette\Web\Html;

/**
 * DatePicker input control
 *
 * @author Tomáš Kraina, Roman Sklenář, Lopo
 */
class DatePicker
extends TextInput
{
	/**
	 * @param string $label
	 * @param int $cols width of the control
	 * @param int $maxLenght maximum number of characters the user may enter
	 */
	public function __construct($label, $cols=NULL, $maxLenght=NULL)
	{
		parent::__construct($label, $cols, $maxLenght);
	}

	/**
	 * Returns control's value
	 * @return mixed 
	 */
	public function getValue()
	{
		if (strlen($this->value)) {
			$tmp=preg_replace('~([[:space:]])~', '', $this->value);
			$tmp=explode('.', $tmp);
			// database format Y-m-d
			return "{$tmp[2]}-{$tmp[1]}-{$tmp[0]}";
		}
		return $this->value;
	}

	/**
	 * Sets control's value
	 * @param  string
	 * @return void
	 */
	public function setValue($value)
	{
		$value=preg_replace('~([0-9]{4})-([0-9]{2})-([0-9]{2})~', '$3.$2.$1', $value);
		parent::setValue($value);
	}

	/**
	 * Generates control's HTML element
	 * @return Html
	 */
	public function getControl()
	{
		$control=parent::getControl();
		$control->class='datepicker';
		$control->value=$this->value;
		$control->setName($control->getName(), false); // enable add()
		$control->add(Html::el('script', array('type'=>'text/javascript'))->add("$(function() {
				$('#$control->id').datepicker({
					dateFormat: 'dd.mm.yy',
					firstDay: 1,
					changeMonth: true,
					changeYear: true,
					duration: 'fast'
					},
				$.datepicker.regional['sk']);
				});"));
		return $control;
	}
}
