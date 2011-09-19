<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\UI;

use Lohini\Forms\Controls;

/**
 * Extended UI Form
 * - preregistered translator
 * - ajax-ed
 * 
 * @author Lopo <lopo@lohini.net>
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
		$this->setRenderer(new \Lohini\Forms\Rendering\FormRenderer);
		$this->getElementPrototype()->addClass('ajax');
		$this->addProtection("Ehm ... Please try to submit the form 1 more time, the goblin stoled something.");
	}

	protected function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->getPresenter()->context->hasService('translator')) {
			$this->setTranslator($this->getPresenter()->context->translator);
			}
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param int $cols
	 * @param int $maxLength
	 * @return \Lohini\Forms\Controls\PswdInput
	 */
	public function addPswd($name, $label=NULL, $cols=NULL, $maxLength=NULL)
	{
		return $this[$name]=new Controls\PswdInput($label, $cols, $maxLength);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @return \Lohini\Forms\Controls\CBox3S
	 */
	public function addCBox3S($name, $label=NULL)
	{
		return $this[$name]=new Controls\CBox3S($label);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param int $cols
	 * @param int $maxLenght
	 * @return \Lohini\Forms\Controls\DatePicker
	 */
	public function addDatePicker($name, $label=NULL, $cols=NULL, $maxLenght=NULL)
	{
		return $this[$name]=new Controls\DatePicker($label, $cols, $maxLenght);
	}

	/**
	 * @param string $name
	 * @param string $caption
	 * @return \Lohini\Forms\Controls\ResetButton
	 */
	public function addReset($name, $caption=NULL)
	{
		return $this[$name]=new Controls\ResetButton($caption);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param int $cols
	 * @param int $rows
	 * @return \Nette\Forms\Controls\TextArea 
	 */
	public function addTexyla($name, $label=NULL, $cols=40, $rows=10)
	{
		return $this[$name]=new Controls\Texyla($label, $cols, $rows);
	}
}
