<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Forms\Controls;

use Nette\Forms\Controls\TextInput,
	Nette\Utils\Html,
	Nette\Environment as NEnvironment;

/**
 * DatePicker input control
 *
 * @author Lopo <lopo@losys.eu>
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
		$basePath=preg_replace('#https?://[^/]+#A', '', rtrim(NEnvironment::getVariable('baseUri', NULL), '/'));
		$t=$this->getTranslator();
		if ($t===NULL) {
			$t=NEnvironment::getApplication()->getContext()->getService('Nette\Localization\ITranslator');
			}
		$lng=$t->getLang();
		if ($lng=='en') {
			$regional="$('input#".$this->getHtmlId()."').datepicker();";
			}
		else {
			$regional="yepnope({
							test: $.datepicker.regional['$lng'],
							nope: '$basePath/js/ui/i18n/jquery-ui-i18n.js',
							complete: function() {
								$('input#".$this->getHtmlId()."').datepicker($.datepicker.regional['$lng']);
								}
							});";
			}
		$control=parent::getControl();
		$control->type='date';
		$control->class='datepicker';
		$control->value=$this->value;
		$control->setName($control->getName(), false); // enable add()
		$control->add(Html::el('script', array('type'=>'text/javascript'))->add("yepnope({
			test: Modernizr.inputtypes && Modernizr.inputtypes.date,
			nope: '$basePath/fbcks/datepicker.css',
			callback: function() {
				yepnope({
					test: $.ui,
					nope: ['$basePath/js/jquery-ui.js', '$basePath/css/jquery-ui.css'],
					complete: function() {
						$regional
						}
					});
				}
			});"));
		return $control;
	}
}
