<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Forms\Controls;

use Nette\Environment as NEnvironment,
	Nette\Forms\Controls\BaseControl,
	Nette\Utils\Html,
	Nette\Templating\DefaultHelpers;

/**
 * 3-state checkbox input control
 *
 * @author Lopo <lopo@losys.eu>
 */
class CBox3S
extends BaseControl
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
	 * @throws InvalidArgumentException
	 */
	public function setValue($value)
	{
		if (!in_array($value, array(-1, 0, 1))) {
			throw new InvalidArgumentException("Invalid argument passed, one of [-1, 0, 1] expected, '$value' given.");
			}
		parent::setValue($value);
	}

	/**
	 * Returns container HTML element template
	 * @return Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}

	/**
	 * Generates control's HTML element
	 * @return Html
	 */
	public function getControl()
	{
		$basePath=preg_replace('#https?://[^/]+#A', '', rtrim(NEnvironment::getVariable('baseUri', NULL), '/'));
		$control=parent::getControl();
		$data=array(
			'value' => $this->getValue()!==NULL? (int)$this->getValue() : 0
			);
		return Html::el('span')
				->add($control)
				->addClass('ui-icon')
				->add(Html::el('script', array('type' => 'text/javascript'))
					->add("head.js(
						'$basePath/js/CBox3S.js',
						function() {
							CBox3S('{$control->id}', ".DefaultHelpers::escapeJs($data).');
							}
						);')
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
