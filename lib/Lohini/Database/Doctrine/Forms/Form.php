<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Forms;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class Form
extends \Lohini\Application\UI\Form
implements IObjectContainer
{
	/** @var bool */
	public $autoFlush=TRUE;
	/**
	 * Occurs when the entity values are being mapped to form
	 * @var array of function(array $values, object $entity);
	 */
	public $onLoad=array();
	/**
	 * Occurs when the form values are being mapped to entity
	 * @var array of function($values, Nette\Forms\Container $container);
	 */
	public $onSave=array();
	/** @var EntityMapper */
	private $mapper;
	/** @var \Lohini\Database\Doctrine\Registry */
	private $doctrine;
	/** @var object */
	private $entity;
	/** @var ContainerBuilder */
	private $builder;


	/**
	 * @param \Lohini\Database\Doctrine\Registry $doctrine
	 * @param object $entity
	 * @param EntityMapper|null $mapper
	 */
	public function __construct(\Lohini\Database\Doctrine\Registry $doctrine, $entity=NULL, EntityMapper $mapper=NULL)
	{
		$this->doctrine=$doctrine;
		$this->mapper= $mapper ?: new EntityMapper($doctrine);

		if (($this->entity=$entity)!==NULL) {
			$this->mapper->assign($entity, $this);
			}

		parent::__construct();
	}

	/**
	 * @return ContainerBuilder
	 */
	private function getBuilder()
	{
		if ($this->builder===NULL) {
			$class=$this->getMapper()->getMeta($this->getEntity());
			$this->builder=new ContainerBuilder($this, $class);
			}

		return $this->builder;
	}

	/**
	 * @param string $field
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function add($field)
	{
		$this->getBuilder()->addFields($fields=func_get_args());
		return $this[reset($fields)];
	}

	/**
	 * @return NULL|object
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return EntityMapper
	 */
	public function getMapper()
	{
		return $this->mapper;
	}

	/**
	 * @param \Nette\ComponentModel\IComponent $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof \Nette\Application\UI\Presenter) {
			$this->getMapper()->loadControlItems();

			if (!$this->isSubmitted()) {
				$this->getMapper()->load();
				}
			else {
				$this->getMapper()->save();
				}
			}
	}

	/**
	 * Fires send/click events.
	 */
	public function fireEvents()
	{
		$valid= $redirect= FALSE;

		/** @var \Nette\Forms\Controls\SubmitButton $button */
		if (!$button=$this->isSubmitted()) {
			return;
			}
		if ($button instanceof \Nette\Forms\ISubmitterControl) {
			if (!$button->getValidationScope() || ($valid=$this->isValid())) {
				/** @var EntityContainer $buttonContainer */
				$buttonContainer=$button->getParent();
				if ($buttonContainer instanceof IObjectContainer && method_exists($buttonContainer, 'getEntity')) {
					$clickedEntity=$buttonContainer->getEntity();
					}
				else {
					$clickedEntity=$this->getEntity();
					}

				if ($clickedEntity){
					$dao=$this->doctrine->getDao($clickedEntity);
					$redirect=$this->dispatchEvent($button->onClick, $button, $clickedEntity, $dao);
					}
				else {
					$redirect=$this->dispatchEvent($button->onClick, $button);
					}
				}
			else {
				$redirect=$this->dispatchEvent($button->onInvalidClick, $button);
				}
			}

		if ($redirect) {
			if ($valid || ($valid=$this->isValid())){
				$this->persistEntities($this->autoFlush);
				}

			$this->getPresenter()->terminate();
			}

		if ($valid || ($valid=$this->isValid())) {
			if ($entity=$this->getEntity()) {
				$dao=$this->doctrine->getDao($entity);
				$redirect=$this->dispatchEvent($this->onSuccess, $this, $entity, $dao);
				}
			else {
				$redirect=$this->dispatchEvent($this->onSuccess, $this);
				}
			}
		else {
			$redirect=$this->dispatchEvent($this->onError, $this);
			}

		if ($valid) {
			$this->persistEntities($this->autoFlush);
			}

		if ($redirect) {
			$this->getPresenter()->terminate();
			}
	}

	/**
	 * @param array|\Traversable $listeners
	 * @param mixed $arg
	 * @return bool whether or not to send terminate
	 */
	protected function dispatchEvent($listeners, $arg=NULL)
	{
		try {
			$args=func_get_args();
			call_user_func_array('parent::dispatchEvent', $args);
			return FALSE;
			}
		catch (\Nette\Application\AbortException $e) {
			return TRUE;
			}
	}

	/**
	 * Crawls mapped component, persists all entities and optionally flushes them.
	 *
	 * @param bool $flush
	 */
	public function persistEntities($flush=FALSE)
	{
		$entities= $this->mapper? $this->mapper->getEntities() : array();
		foreach ($this->getComponents(TRUE, 'Lohini\Database\Doctrine\Forms\EntityContainer') as $container) {
			/** @var EntityContainer $container */
			if (!in_array($entity=$container->getEntity(), $entities, TRUE)) {
				$entities[]=$entity;
				}
			}

		$em=$this->doctrine->getEntityManager();
		foreach ($entities as $entity) {
			$em->persist($entity);
			}

		if ($flush) {
			$em->flush();
			}
	}

	/**
	 * @param string $name
	 * @param object $entity
	 * @return EntityContainer
	 */
	public function addOne($name, $entity=NULL)
	{
		$entity= $entity ?: $this->getMapper()->getRelated($this, $name);
		return $this[$name]=new EntityContainer($entity);
	}

	/**
	 * @param $name
	 * @param $factory
	 * @param int $createDefault
	 * @return CollectionContainer
	 */
	public function addMany($name, $factory, $createDefault=0)
	{
		$collection=$this->getMapper()->getCollection($this->entity, $name);
		$this[$name]=$container=new CollectionContainer($collection, $factory);
		$container->createDefault=$createDefault;
		return $container;
	}
}

EntityMapper::registerAliasMethod();
EntityMapper::registerMapperMethod();
