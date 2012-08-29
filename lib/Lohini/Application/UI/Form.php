<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\UI;

use Lohini\Forms\Controls,
	Nette\Forms\Controls\RadioList;

/**
 * Extended UI Form
 * - preregistered translator
 * - ajax-ed
 * 
 * @author Lopo <lopo@lohini.net>
 * 
 * @property callable $validateThatControlsAreRendered
 * @method \Lohini\Forms\Controls\CheckboxList addCheckboxList(string $name, string $label=NULL, array $items=NULL)
 * @method \Lohini\Forms\Controls\DateTimeInput addDate(string $name, string $label=NULL)
 * @method \Lohini\Forms\Controls\DateTimeInput addTime(string $name, string $label=NULL)
 * @method \Lohini\Forms\Controls\DateTimeInput addDatetime(string $name, string $label=NULL)
 * @method \Lohini\Forms\Containers\Replicator addDynamic(string $name, callback $factory, int $default)
 */
class Form
extends \Nette\Application\UI\Form
{
	/** @var bool When flag is TRUE, iterates over form controls and if some are rendered and some are not, triggers notice. */
	public $checkRendered=TRUE;


	/**
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param string $name
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->configure();
	}

	protected function configure()
	{
//		$this->setRenderer(new \Lohini\Forms\Rendering\FormRenderer);
	}

	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if ($obj instanceof \Nette\Application\IPresenter) {
			$this->attachHandlers();

			$app=$this->getPresenter()->getApplication();
			$app->onShutdown[]=$this->validateThatControlsAreRendered;
			}
		parent::attached($obj);
//		$ctx=$this->getPresenter()->getContext();
//		if ($ctx && $ctx->hasService('translator')) {
//			$this->setTranslator($ctx->translator);
//			}
	}

	/**
	 * @internal
	 */
	public function validateThatControlsAreRendered()
	{
		if (\Nette\Diagnostics\Debugger::$productionMode || $this->checkRendered!==TRUE) {
			return;
			}

		$notRendered= $rendered= array();
		foreach ($this->getControls() as $control) {
			/** @var \Nette\Forms\Controls\BaseControl $control */
			if (!$control instanceof \Nette\Forms\Controls\BaseControl) {
				continue;
				}
			if ($control->getOption('rendered', FALSE)) {
				$rendered[]=$control;
				}
			else {
				$notRendered[]=$control;
				}
			}

		if ($rendered && $notRendered) {
			$names=array_map(
					function(BaseControl $control) {
						return get_class($control).'('.$control->lookupPath('Nette\Forms\Form').')';
						},
					$notRendered
					);

			trigger_error(
				'Some form controls of '.$this->getUniqueId().' were not rendered: '.implode(', ', $names),
				E_USER_NOTICE
				);
			}
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
			$this->onSuccess[]=callback($this, 'handleSuccess');
			}
		if (method_exists($this, 'handleError')) {
			$this->onError[]=callback($this, 'handleError');
			}
		if (method_exists($this, 'handleValidate')) {
			$this->onValidate[]=callback($this, 'handleValidate');
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
				$submitControl->onClick[]=callback($this, 'handle'.$name.'Click');
				}
			if (method_exists($this, 'handle'.$name.'InvalidClick')) {
				$submitControl->onInvalidClick[]=callback($this, 'handle'.$name.'InvalidClick');
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
				callback($handler)->invokeArgs($args);
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
	 * @param string $label
	 * @return Controls\CBox3S
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
	 * @return Controls\DatePicker
	 */
	public function addDatePicker($name, $label=NULL, $cols=NULL, $maxLenght=NULL)
	{
		return $this[$name]=new Controls\DatePicker($label, $cols, $maxLenght);
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
}

Controls\CheckboxList::register();
Controls\DateTimeInput::register();
\Lohini\Forms\Containers\Replicator::register();

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
