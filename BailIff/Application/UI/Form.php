<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Application\UI;

use Nette\Environment as NEnvironment,
	Nette\Forms\Container;

/**
 * Extended Form
 * - preregistered translator
 * - ajax-ed
 * 
 * @author Lopo <lopo@losys.eu>
 */
class Form
extends \Nette\Application\UI\Form
{
	/**
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param string $name
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->setTranslator(NEnvironment::getService('translator'));
		$this->setRenderer(new \BailIff\Forms\Rendering\FormRenderer);
		$this->getElementPrototype()->addClass('ajax');
		$this->addProtection("Ehm ... Please try to submit the form 1 more time, the goblin stoled something.");
	}
}
/*
Container::extensionMethod('addPswd', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\PswdInput($label); });
Container::extensionMethod('addCBox3S', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\CBox3S($label); });
Container::extensionMethod('addDatePicker', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\DatePicker($label); });
Container::extensionMethod('addReset', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\ResetButton($label); });
*/