<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Application\UI;

/**
 * Extended UI Form
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
		$this->setRenderer(new \BailIff\Forms\Rendering\FormRenderer);
		$this->getElementPrototype()->addClass('ajax');
		$this->addProtection("Ehm ... Please try to submit the form 1 more time, the goblin stoled something.");
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param int $cols
	 * @param int $maxLength
	 * @return \BailIff\Forms\Controls\PswdInput
	 */
	public function addPswd($name, $label=NULL, $cols=NULL, $maxLength=NULL)
	{
		return $this[$name]=new \BailIff\Forms\Controls\PswdInput($label, $cols, $maxLength);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @return \BailIff\Forms\Controls\CBox3S
	 */
	public function addCBox3S($name, $label=NULL)
	{
		return $this[$name]=new \BailIff\Forms\Controls\CBox3S($label);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param int $cols
	 * @param int $maxLenght
	 * @return \BailIff\Forms\Controls\DatePicker
	 */
	public function addDatePicker($name, $label=NULL, $cols=NULL, $maxLenght=NULL)
	{
		return $this[$name]=new \BailIff\Forms\Controls\DatePicker($label, $cols, $maxLenght);
	}

	/**
	 * @param string $name
	 * @param string $caption
	 * @return \BailIff\Forms\Controls\ResetButton
	 */
	public function addReset($name, $caption=NULL)
	{
		return $this[$name]=new \BailIff\Forms\Controls\ResetButton($caption);
	}
}
