<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Forms\Replicator;
/**
 * @author Filip Procházka <filip@prochazka.su>
 * @author Jan Tvrdík
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Forms\Controls\SubmitButton,
	Nette\Forms\Container;

/**
 * @method \Nette\Application\UI\Form getForm()
 */
class Replicator
extends Container
{
	/** @var bool */
	public $forceDefault;
	/** @var int */
	public $createDefault;
	/** @var callable */
	protected $factoryCallback;
	/** @var bool */
	private $submittedBy=FALSE;
	/** @var array */
	private $created=array();
	/** @var \Nette\Http\IRequest */
	private $httpRequest;
	/** @var array */
	private $httpPost;
	/** @var bool */
	private static $registered=FALSE;


	/**
	 * @param callable $factory
	 * @param int $createDefault
	 * @param bool $forceDefault
	 * @throws \Nette\InvalidArgumentException
	 */
	public function __construct($factory, $createDefault=0, $forceDefault=FALSE)
	{
		parent::__construct();
		$this->monitor('Nette\Application\UI\Presenter');

		try {
			$this->factoryCallback = callback($factory);
			}
		catch (\Nette\InvalidArgumentException $e) {
			$type= is_object($factory)? 'instanceof '.get_class($factory) : gettype($factory);
			throw new \Nette\InvalidArgumentException('Replicator requires callable factory, '.$type.' given.', 0, $e);
			}

		$this->createDefault=(int)$createDefault;
		$this->forceDefault=$forceDefault;
	}

	/**
	 * @param callable $factory
	 */
	public function setFactory($factory)
	{
		$this->factoryCallback=callback($factory);
	}

	/**
	 * Magical component factory
	 *
	 * @param \Nette\ComponentModel\IContainer
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof \Nette\Application\UI\Presenter) {
			return;
			}

		$this->loadHttpData();
		$this->createDefault();
	}

	/**
	 * @param bool $recursive
	 * @return \ArrayIterator
	 */
	public function getContainers($recursive=FALSE)
	{
		return $this->getComponents($recursive, 'Nette\Forms\Container');
	}

	/**
	 * @param bool $recursive
	 * @return \ArrayIterator
	 */
	public function getButtons($recursive=FALSE)
	{
		return $this->getComponents($recursive, 'Nette\Forms\ISubmitterControl');
	}

	/**
	 * Magical component factory
	 *
	 * @param string $name
	 * @return \Nette\Forms\Container
	 */
	protected function createComponent($name)
	{
		$container=$this->createContainer($name);
		$container->currentGroup=$this->currentGroup;
		$this->addComponent($container, $name, $this->getFirstControlName());

		$this->factoryCallback->invoke($container);

		return $this->created[$container->name]=$container;
	}

	/**
	 * @return string
	 */
	private function getFirstControlName()
	{
		$controls=iterator_to_array($this->getComponents(FALSE, 'Nette\Forms\IControl'));
		$firstControl=reset($controls);
		return $firstControl? $firstControl->name : NULL;
	}

	/**
	 * @param string $name
	 * @return Container
	 */
	protected function createContainer($name)
	{
		return new Container;
	}

	/**
	 * @return bool
	 */
	public function isSubmittedBy()
	{
		if ($this->submittedBy) {
			return TRUE;
			}

		foreach ($this->getButtons(TRUE) as $button) {
			if ($button->isSubmittedBy()) {
				return $this->submittedBy=TRUE;
				}
			}

		return FALSE;
	}

	/**
	 * Create new container
	 *
	 * @param string|int $name
	 * @return Container
	 * @throws \Nette\InvalidArgumentException
	 */
	public function createOne($name=NULL)
	{
		if ($name===NULL) {
			$names=array_keys($this->getContainers()->getArrayCopy());
			$name= $names? max($names)+1 : 0;
			}

		// Container is overriden, therefore every request for getComponent($name, FALSE) would return container
		if (isset($this->created[$name])) {
			throw new \Nette\InvalidArgumentException("Container with name '$name' already exists.");
			}

		return $this[$name];
	}

	/**
	 * 
	 * @param array|\Traversable $values
	 * @param bool $erase
	 * @return \Nette\Forms\Container|Replicator
	 */
	public function setValues($values, $erase=FALSE)
	{
		foreach ($values as $name => $value) {
			if ((is_array($value) || $value instanceof \Traversable) && !$this->getComponent($name, FALSE)) {
				$this->createOne($name);
				}
			}
		return parent::setValues($values, $erase);
	}

	/**
	 * Loads data received from POST
	 * @internal
	 */
	protected function loadHttpData()
	{
		if (!$this->getForm()->isSubmitted()) {
			return;
			}

		$this->setValues((array)$this->getHttpData());
	}

	/**
	 * Creates default containers
	 * @internal
	 */
	protected function createDefault()
	{
		if (!$this->createDefault) {
			return;
			}
		if (!$this->getForm()->isSubmitted()) {
			foreach (range(0, $this->createDefault-1) as $key) {
				$this->createOne($key);
				}
			}
		elseif ($this->forceDefault) {
			while (iterator_count($this->getContainers())<$this->createDefault) {
				$this->createOne();
				}
			}
	}

	/**
	 * @param string $name
	 * @return array|NULL
	 */
	protected function getContainerValues($name)
	{
		$post=$this->getHttpData();
		return isset($post[$name])? $post[$name] : NULL;
	}

	/**
	 * @return mixed|NULL
	 */
	private function getHttpData()
	{
		if ($this->httpPost===NULL) {
			$path=explode(self::NAME_SEPARATOR, $this->lookupPath('Nette\Forms\Form'));
			$this->httpPost=\Nette\Utils\Arrays::get($this->getForm()->getHttpData(), $path, NULL);
			}
		return $this->httpPost;
	}

	/**
	 * @internal
	 * @param \Nette\Application\Request $request
	 * @return Replicator (fluent)
	 */
	public function setRequest(\Nette\Application\Request $request)
	{
		$this->httpRequest=$request;
		return $this;
	}

	/**
	 * @return \Nette\Application\Request
	 */
	private function getRequest()
	{
		if ($this->httpRequest!==NULL) {
			return $this->httpRequest;
			}

		return $this->httpRequest=$this->getForm()->getPresenter()->getRequest();
	}

	/**
	 * @param Container $container
	 * @param bool $cleanUpGroups
	 * @throws \Nette\InvalidArgumentException
	 */
	public function remove(Container $container, $cleanUpGroups=FALSE)
	{
		if (!$container->parent===$this) {
			throw new \Nette\InvalidArgumentException("Given component $container->name is not children of $this->name.");
			}

		// to check if form was submitted by this one
		foreach ($container->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $button) {
			if ($button->isSubmittedBy()) {
				$this->submittedBy=TRUE;
				break;
				}
			}

		// get components
		$components=$container->getComponents(TRUE);
		$this->removeComponent($container);

		// reflection is required to hack form groups
		$groupRefl=\Nette\Reflection\ClassType::from('Nette\Forms\ControlGroup');
		$controlsProperty=$groupRefl->getProperty('controls');
		$controlsProperty->setAccessible(TRUE);

		// walk groups and clean then from removed components
		$affected=array();
		foreach ($this->getForm()->getGroups() as $group) {
			$groupControls=$controlsProperty->getValue($group);

			foreach ($components as $control) {
				if ($groupControls->contains($control)) {
					$groupControls->detach($control);

					if (!in_array($group, $affected, TRUE)) {
						$affected[]=$group;
						}
					}
				}
			}

		// remove affected & empty groups
		if ($cleanUpGroups && $affected) {
			foreach ($this->getForm()->getComponents(FALSE, 'Nette\Forms\Container') as $container) {
				if ($index=array_search($container->currentGroup, $affected, TRUE)) {
					unset($affected[$index]);
					}
				}

			foreach ($affected as $group) {
				if (!$group->getControls() && in_array($group, $this->getForm()->getGroups(), TRUE)) {
					$this->getForm()->removeGroup($group);
					}
				}
			}
	}

	/**
	 * Counts filled values, filtered by given names
	 *
	 * @param array $components
	 * @param array $subComponents
	 * @return int
	 */
	public function countFilledWithout(array $components=array(), array $subComponents=array())
	{
		$httpData=array_diff_key((array)$this->getHttpData(), array_flip($components));

		if (!$httpData) {
			return 0;
			}

		$rows=array();
		$subComponents=array_flip($subComponents);
		foreach ($httpData as $item) {
			$rows[]= array_filter(array_diff_key($item, $subComponents)) ?: FALSE;
			}

		return count(array_filter($rows));
	}

	/**
	 * @param array $exceptChildren
	 * @return bool
	 */
	public function isAllFilled(array $exceptChildren=array())
	{
		$components=array();
		foreach ($this->getComponents(FALSE, 'Nette\Forms\IControl') as $control) {
			$components[]=$control->getName();
			}

		foreach ($this->getContainers() as $container) {
			foreach ($container->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $button) {
				$exceptChildren[]=$button->getName();
				}
			}

		$filled=$this->countFilledWithout($components, array_unique($exceptChildren));
		return $filled===iterator_count($this->getContainers());
	}

	/**
	 * @param string $methodName
	 * @throws \Nette\MemberAccessException
	 */
	public static function register($methodName='addDynamic')
	{
		if (self::$registered) {
			Container::extensionMethod(
					self::$registered,
					function() { throw new \Nette\MemberAccessException; }
					);
			}
		Container::extensionMethod(
				$methodName,
				function(Container $_this, $name, $factory, $createDefault=0) {
					return $_this[$name]=new Replicator($factory, $createDefault);
					}
				);

		if (self::$registered) {
			return;
			}

		SubmitButton::extensionMethod(
				'addRemoveOnClick',
				function(SubmitButton $_this, $callback=NULL) {
					$replicator=$_this->lookup(__NAMESPACE__.'\Replicator');
					$_this->setValidationScope(FALSE);
					$_this->onClick[]=function(SubmitButton $button) use ($replicator, $callback) {
						/** @var Replicator $replicator */
						if (is_callable($callback)) {
							callback($callback)->invoke($replicator, $button->parent);
							}
						$replicator->remove($button->parent);
						};
					return $_this;
					}
				);

		SubmitButton::extensionMethod(
				'addCreateOnClick',
				function(SubmitButton $_this, $allowEmpty=FALSE, $callback=NULL) {
					$replicator=$_this->lookup(__NAMESPACE__.'\Replicator');
					$_this->onClick[]=function(SubmitButton $button) use ($replicator, $allowEmpty, $callback) {
						/** @var Replicator $replicator */
						if (!is_bool($allowEmpty)) {
							$callback=callback($allowEmpty);
							$allowEmpty=FALSE;
							}
						if ($allowEmpty===FALSE && $replicator->isAllFilled()===FALSE) {
							return;
							}
						$newContainer=$replicator->createOne();
						if (is_callable($callback)) {
							callback($callback)->invoke($replicator, $newContainer);
							}
						};
					return $_this;
					}
				);
		self::$registered = $methodName;
	}
}

class_alias(__NAMESPACE__.'\Replicator', 'Lohini\Forms\Containers\Replicator');
Replicator::register();
