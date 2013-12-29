<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\UI;

use Lohini\Forms\Controls,
	Nette\Forms\Controls\RadioList;

/**
 * Extended UI Form
 * 
 * @author Lopo <lopo@lohini.net>
 */
class Form
extends \Nette\Application\UI\Form
{
	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if ($obj instanceof \Nette\Application\IPresenter) {
			$this->attachHandlers();
			}
		parent::attached($obj);
	}

	/**
	 * Returns a fully-qualified name that uniquely identifies the component within the presenter hierarchy.
	 *
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath('Nette\Application\UI\Presenter', TRUE);
	}

	/**
	 * Automatically attach methods
	 */
	protected function attachHandlers()
	{
		if (method_exists($this, 'handleSuccess')) {
			$this->onSuccess[]=[$this, 'handleSuccess'];
			}
		if (method_exists($this, 'handleError')) {
			$this->onError[]=[$this, 'handleError'];
			}
		if (method_exists($this, 'handleValidate')) {
			$this->onValidate[]=[$this, 'handleValidate'];
			}

		foreach ($this->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $submitControl) {
			$name=ucfirst((\Nette\Utils\Strings::replace(
					$submitControl->lookupPath('Nette\Forms\Form'),
					'~\-(.)~i',
					function($m) {
						return strtoupper($m[1]);
						}
					)));

			if (method_exists($this, 'handle'.$name.'Click')) {
				$submitControl->onClick[]=[$this, 'handle'.$name.'Click'];
				}
			if (method_exists($this, 'handle'.$name.'InvalidClick')) {
				$submitControl->onInvalidClick[]=[$this, 'handle'.$name.'InvalidClick'];
				}
		}
	}

	/**
	 * Fires send/click events.
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;
			}
		if ($this->isSubmitted() instanceof \Nette\Forms\ISubmitterControl) {
			if (!$this->isSubmitted()->getValidationScope() || $this->isValid()) {
				$this->dispatchEvent($this->isSubmitted()->onClick, $this->isSubmitted());
				$valid=TRUE;
				}
			else {
				$this->dispatchEvent($this->isSubmitted()->onInvalidClick, $this->isSubmitted());
				}
			}

		if (isset($valid) || $this->isValid()) {
			$this->dispatchEvent($this->onSuccess, $this);
			}
		else {
			$this->dispatchEvent($this->onError, $this);
			}
	}

	/**
	 * @param array|\Traversable $listeners
	 * @param mixed $arg
	 */
	protected function dispatchEvent($listeners, $arg=NULL)
	{
		$args=func_get_args();
		$listeners=array_shift($args);

		foreach ((array)$listeners as $handler) {
			if ($handler instanceof \Nette\Application\UI\Link) {
				/** @var \Nette\Application\UI\Link $handler */
				$refl=$handler->getReflection();
				/** @var \Nette\Reflection\ClassType $refl */
				$compRefl=$refl->getProperty('component');
				$compRefl->accessible=TRUE;
				/** @var \Nette\Application\UI\PresenterComponent $component */
				$component=$compRefl->getValue($handler);
				$component->redirect($handler->getDestination(), $handler->getParameters());
				}
			else {
				\Nette\Utils\Callback::invokeArgs($handler, $args);
				}
			}
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param int $cols
	 * @param int $maxLength
	 * @return Controls\PswdInput
	 */
	public function addPswd($name, $label=NULL, $cols=NULL, $maxLength=NULL)
	{
		return $this[$name]=new Controls\PswdInput($label, $cols, $maxLength);
	}

	/**
	 * @param string $name
	 * @param string $caption
	 * @return Controls\Checkbox3S
	 */
	public function addCheckbox3S($name, $caption=NULL)
	{
		return $this[$name]=new Controls\Checkbox3S($caption);
	}

	/**
	 * @param string $name
	 * @param string $caption
	 * @return Controls\ResetButton
	 */
	public function addReset($name, $caption=NULL)
	{
		return $this[$name]=new Controls\ResetButton($caption);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param array $items
	 * @return Controls\CheckboxList
	 */
	public function addCheckboxList($name, $label=NULL, array $items=NULL)
	{
		return $this[$name]=new Controls\CheckboxList($label, $items);
	}
}

// radio list helper
RadioList::extensionMethod(
		'getItemsOuterLabel',
		function(RadioList $_this) {
			$items=array();
			foreach ($_this->items as $key => $value) {
				$html=$_this->getControl($key);
				$html[1]->addClass('radio');

				$items[$key]=$html[1] // label
						->add($html[0]); // control
				}

			return $items;
			}
		);

// radio list helper
RadioList::extensionMethod(
		'getFirstItemLabel',
		function(RadioList $_this) {
			$items=$_this->items;
			$first=key($items);

			$html=$_this->getControl($first);
			$html[1]->addClass('control-label');
			$html[1]->setText($_this->caption);

			return $html[1];
			}
		);
