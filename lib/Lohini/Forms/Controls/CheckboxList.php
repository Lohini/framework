<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Controls;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Utils\Html;

/**
 * CheckboxList
 */
class CheckboxList
extends \Nette\Forms\Controls\BaseControl
{
	/** @var Html separator element template */
	protected $separator;
	/** @var Html container element template */
	protected $container;
	/** @var array */
	protected $items=[];


	/**
	 * @param string $label
	 * @param array $items  Options from which to choose
	 */
	public function __construct($label=NULL, array $items=NULL)
	{
		parent::__construct($label);

		$this->control->type='checkbox';
		$this->container=Html::el();
		$this->separator=Html::el('br');

		if ($items!==NULL) {
			$this->setItems($items);
			}
	}

	/**
	 * Returns selected radio value. NULL means nothing have been checked.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		$checked= is_array($this->value)? array_keys(array_filter($this->value)) : NULL;
		if ($checked!==NULL) {
			$checked=array_intersect(array_keys($this->items), $checked);
			}
		return $checked;
	}

	/**
	 * Returns selected radio value. NULL means nothing have been checked.
	 *
	 * @return mixed
	 */
	public function getRawValues()
	{
		return is_array($this->value)? $this->value : NULL;
	}

	/**
	 * Sets options from which to choose.
	 *
	 * @param array $items
	 * @return CheckboxList provides a fluent interface
	 */
	public function setItems(array $items)
	{
		$this->items=$items;
		return $this;
	}

	/**
	 * Returns options from which to choose.
	 *
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Returns separator HTML element template.
	 *
	 * @return Html
	 */
	public function getSeparatorPrototype()
	{
		return $this->separator;
	}

	/**
	 * Returns container HTML element template.
	 *
	 * @return Html
	 */
	public function getContainerPrototype()
	{
		return $this->container;
	}

	/**
	 * Generates control's HTML element.
	 *
	 * @param mixed $key Specify a key if you want to render just a single checkbox
	 * @return Html
	 */
	public function getControl($key=NULL)
	{
		if ($key!==NULL && !isset($this->items[$key])) {
			return NULL;
			}

		$container=clone $this->container;
		$separator=(string)$this->separator;

		$control=parent::getControl();
		$id=$control->id;
		$name=$control->name;
		$values= $this->value===NULL? NULL : (array)$this->getValue();
		$label=Html::el('label');

		$counter=-1;
		foreach ($this->items as $k => $val) {
			$counter++;
			if ($key!==NULL && $key!=$k) { // intentionally ==
				continue;
				}

			$control->name=$name.'['.$k.']';
			$control->id= $label->for= "$id-$counter";
			$control->checked= (count($values)>0)? in_array($k, $values) : FALSE;
			$control->value=$k;

			if ($val instanceof Html) {
				$label->setHtml($val);
				}
			else {
				$label->setText($this->translate($val));
				}

			if ($key!==NULL) {
				return Html::el()->add($control)->add($label);
				}

			$container->add((string)$control.(string)$label.$separator);
			}

		return $container;
	}

	/**
	 * Generates label's HTML element.
	 *
	 * @return Html
	 */
	public function getLabel($caption=NULL)
	{
		$label=parent::getLabel($caption);
		$label->for=NULL;
		return $label;
	}

	/**
	 * Filled validator: has been any checkbox checked?
	 *
	 * @param \Nette\Forms\IControl $control
	 * @return bool
	 */
	public static function validateChecked(\Nette\Forms\IControl $control)
	{
		return $control->getValue()!==NULL;
	}
}
