<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Forms\Controls;

use Nette\Utils\Html;

/**
 * DatePicker input control
 *
 * @author Lopo <lopo@losys.eu>
 */
class DatePicker
extends \Nette\Forms\Controls\TextInput
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
			// database format Y-m-d
			return date('Y-m-d', strtotime($this->value));
			}
		return $this->value;
	}

	/**
	 * Sets control's value
	 * @param string
	 * @return void
	 */
	public function setValue($value)
	{
		parent::setValue(date('d.m.Y', strtotime($value)));
	}

	/**
	 * Generates control's HTML element
	 * @return Html
	 */
	public function getControl()
	{
		$container=Html::el();
		$basePath=rtrim($this->form->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBasePath(), '/');
		if (($t=$this->getTranslator())===NULL) {
			$t=$this->form->getPresenter(FALSE)->getContext()->getService('translator');
			}
		$lng=$t->getLang();
		if ($lng=='en') {
			$regional="$('input#".$this->getHtmlId()."').datepicker();";
			}
		else {
			$regional="yepnope({
							test: $.datepicker.regional['$lng'],
							nope: '$basePath/js/ui/i18n/jquery.ui.datepicker-$lng.js',
							complete: function() {
								$('input#".$this->getHtmlId()."').datepicker($.datepicker.regional['$lng']);
								}
							});";
			}
		$control=parent::getControl();
		$control->type='date';
		$control->class='datepicker';
		$control->value= ($this->value!=NULL && $this->value!='')? $this->value : NULL;
		$container->add($control);
		$container->add(Html::el('script', array('type'=>'text/javascript'))
				->add("head.ready(function() {
					yepnope({
					test: Modernizr.inputtypes && Modernizr.inputtypes.date,
					nope: '$basePath/fbcks/datepicker.css',
					callback: function() {
						yepnope({
							test: $.ui,
							nope: ['$basePath/js/ui/jquery-ui.min.js', '$basePath/css/jquery-ui.css'],
							complete: function() {
								$regional
								}
							});
						}
					});
				});"));
		return $container;
	}
}
