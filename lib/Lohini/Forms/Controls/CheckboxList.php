<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Controls;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Cloned RadioList from Nette Framework distribution. Instead of radios use checkboxes.
 *
 * @copyright Copyright (c) 2004, 2009 David Grudl
 * @license http://nettephp.com/license  Nette license
 * @link http://addons.nettephp.com/cs/checkboxlist
 * @author David Grudl, Jan Vlcek
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
	protected $items = array();


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
	public function getControl($key = NULL)
	{
		if ($key===NULL) {
			$container=clone $this->container;
			$separator=(string) $this->separator;
			}
		elseif (!isset($this->items[$key])) {
			return NULL;
			}

		$control=parent::getControl();
		$control->name.='[]';
		$id=$control->id;
		$counter=-1;
		$values= $this->value===NULL? NULL : (array)$this->getValue();
		$label=Html::el('label');

		foreach ($this->items as $k => $val) {
			$counter++;
			if ($key!==NULL && $key!=$k) { // intentionally ==
				continue;
				}

			$control->id= $label->for= "$id-$counter";
			$control->checked= (count($values)>0)? in_array($k, $values) : false;
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

	/**
	 * @param string $name
	 */
	public static function register($name='addCheckboxList')
	{
		\Nette\Forms\Container::extensionMethod(
			$name,
			function(\Nette\Forms\Container $container, $name, $label=NULL, array $items=NULL) {
				return $container[$name]=new CheckboxList($label, $items);
				}
			);
	}
}
