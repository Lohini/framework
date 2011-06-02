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
 * 3-state checkbox input control
 *
 * @author Lopo <lopo@losys.eu>
 */
class CBox3S
extends \Nette\Forms\Controls\BaseControl
{
	/**
	 * @param string $label
	 */
	public function __construct($label=NULL)
	{
		parent::__construct($label);
		$this->control->type='checkbox';
	}

	/**
	 * Returns control's value
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets control's value
	 * @param string $value
	 * @throws \InvalidArgumentException
	 */
	public function setValue($value)
	{
		if (!in_array($value, array(-1, 0, 1))) {
			throw new \InvalidArgumentException("Invalid argument passed, one of [-1, 0, 1] expected, '$value' given.");
			}
		parent::setValue($value);
	}

	/**
	 * Generates control's HTML element
	 * @return Html
	 */
	public function getControl()
	{
		$control=parent::getControl();
		$data=array(
			'value' => $this->getValue()!==NULL? (int)$this->getValue() : 0
			);
		return Html::el('span')
				->add($control)
				->addClass('ui-icon')
				->add(Html::el('script', array('type' => 'text/javascript'))
					->add("head.js('".rtrim($this->form->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBasePath(), '/')."/js/CBox3S.js', function() { CBox3S('{$control->id}', ".\Nette\Templating\DefaultHelpers::escapeJs($data).');});')
					);
	}

	/**
	 * Generates label's HTML element
	 * @param string $caption
	 * @return Html
	 */
	public function getLabel($caption=NULL)
	{
		$label=parent::getLabel($caption);
		$label->for=NULL;
		return $label;
	}
}
