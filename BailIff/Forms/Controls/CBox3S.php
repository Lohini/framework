<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Forms;

use Nette\Forms\FormControl,
	Nette\Web\Html,
	Nette\Templates\TemplateHelpers;

/**
 * 3-state checkbox input control.
 * @author Lopo <lopo@losys.eu>
 */
class CBox3S
extends FormControl
{
	/** @var Html container element template */
	protected $container;
	/** @var string */
	public $img_path='/img/ico';
	/** @var array */
	public $images=array(
		-1 => 'cross.png',
		0 => 'empty.png',
		1 => 'check.png'
		);

	/**
	 * @param string $label
	 */
	public function __construct($label=NULL)
	{
		parent::__construct($label);
		$this->control->type='checkbox';
		$this->container=Html::el();
	}

	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets control's value.
	 * @param string $value
	 */
	public function setValue($value)
	{
		parent::setValue($value);
	}

	/**
	 * Returns container HTML element template.
	 * @return Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}

	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
		$control=parent::getControl();
		$name=$control->name;
		$id=$control->id;
		$data=array(
			'img_path' => $this->img_path,
			'value' => $this->getValue()!==NULL? (int)$this->getValue() : 0,
			'imgs' => $this->images
			);
		$container=Html::el('span')
					->add($control)
					->add(Html::el('script', array('type' => 'text/javascript'))
						->add("$('#$id').ready(CBox3S('$id', ".TemplateHelpers::escapeJs($data).'));')
						);
		return $container;
	}

	/**
	 * Generates label's HTML element.
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
