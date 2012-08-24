<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Forms;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @todo: allow to limit loaded relations by id's
 *
 * @method \Lohini\Database\Doctrine\Forms\Form getForm(bool $need=TRUE)
 * @method \Lohini\Database\Doctrine\Forms\Form|\Lohini\Database\Doctrine\Forms\EntityContainer getParent()
 */
class CollectionContainer
extends \Lohini\Forms\Containers\Replicator
implements IObjectContainer
{
	/** @var string */
	public $containerClass='Lohini\Database\Doctrine\Forms\EntityContainer';
	/** @var EntityMapper */
	private $mapper;
	/** @var \Doctrine\Common\Collections\Collection */
	private $collection;
	/** @var \Nette\Callback */
	private $entityFactory;


	/**
	 * @param \Doctrine\Common\Collections\Collection $collection
	 * @param callable $factory
	 * @param EntityMapper $mapper
	 */
	public function __construct(\Doctrine\Common\Collections\Collection $collection, $factory, EntityMapper $mapper=NULL)
	{
		parent::__construct($factory);
		$this->monitor('Lohini\Database\Doctrine\Forms\Form');

		$this->collection=$collection;
		$this->mapper=$mapper;
	}

	/**
	 * function(object $parentEntity, CollectionContainer $container);
	 *
	 * @param callback $factory
	 */
	public function setEntityFactory($factory)
	{
		$this->entityFactory=callback($factory);
	}

	/**
	 * @return \Nette\Callback
	 */
	public function getEntityFactory()
	{
		return $this->entityFactory;
	}

	/**
	 * @param  \Nette\ComponentModel\IContainer
	 * @throws \Nette\InvalidStateException
	 */
	protected function validateParent(\Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);

		if (!$parent instanceof IObjectContainer && !$this->getForm(FALSE) instanceof IObjectContainer) {
			throw new \Nette\InvalidStateException(
				'Valid parent for Lohini\Database\Doctrine\Forms\EntityContainer '
				.'is only Lohini\Database\Doctrine\Forms\IObjectContainer, '
				.'instance of "'.get_class($parent).'" given'
				);
			}
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * @param bool $need
	 * @return \Nette\Application\UI\Presenter
	 */
	public function getPresenter($need=TRUE)
	{
		return $this->lookup('Nette\Application\UI\Presenter', $need);
	}

	/**
	 * @return \Lohini\Database\Doctrine\Forms\EntityMapper
	 */
	private function getMapper()
	{
		return $this->mapper ?: $this->getForm()->getMapper();
	}

	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		$this->initContainers();
		parent::attached($obj);
		$this->clearContainers();
	}

	/**
	 * Initialize entity containers from given collection
	 */
	protected function initContainers()
	{
		if (!$this->getPresenter(FALSE)) {
			return; // only if attached to presenter
			}

		$this->getMapper()->assignCollection($this->collection, $this);
		if ($this->getForm()->isSubmitted()) {
			return; // only if not submitted
			}

		foreach ($this->collection as $index => $entity) {
			$this->createOne($index);
			}
	}

	/**
	 * Clear containers, that were not submitted
	 */
	protected function clearContainers()
	{
		if (!$this->getPresenter(FALSE) || !$this->getForm()->isSubmitted()) {
			return; // only if attached to presenter & submitted
			}

		foreach ($this->collection->toArray() as $entity) {
			if (!$this->getMapper()->getComponent($entity)) {
				$this->getMapper()->remove($entity);
				}
			}
	}

	/**
	 * @param integer $index
	 * @return EntityContainer
	 */
	protected function createContainer($index)
	{
		if (!$this->getForm()->isSubmitted()) {
			return $this->createNewContainer($index);
			}

		if ($values=$this->getContainerValues($index)) {
			if ($entity=$this->getMapper()->getCollectionEntry($this, $values)) {
				$class=$this->containerClass;
				return new $class($entity);
				}
			}

		return $this->createNewContainer($index);
	}

	/**
	 * @param int $index
	 * @return EntityContainer
	 */
	private function createNewContainer($index)
	{
		if (!$this->collection->containsKey($index)) {
			$this->collection->set($index, $this->createNewEntity());
			}

		$class=$this->containerClass;
		return new $class($this->collection->get($index));
	}

	/**
	 * @return object|NULL
	 */
	protected function getParentEntity()
	{
		return $this->getParent()->getEntity();
	}

	/**
	 * @return string
	 */
	protected function getClassName()
	{
		return $this->getMapper()->getTargetClassName($this->getParentEntity(), $this->getName());
	}

	/**
	 * @return object
	 * @throws \Lohini\UnexpectedValueException
	 */
	protected function createNewEntity()
	{
		$className=$this->getClassName();
		if ($factory=$this->getEntityFactory()) {
			$parentEntity=$this->getParentEntity();
			$related=$factory($parentEntity, $this);
			if (!$related instanceof $className) {
				throw new \Lohini\UnexpectedValueException(
					'Factory of CollectionContainer '.$this->name
					." must return an instance of '$className', "
					.\Lohini\Utils\Tools::getType($related).' returned.'
					);
				}
			}
		else {
			$related=new $className();
			}

		return $related;
	}

	/**
	 * @param \Nette\Forms\Container|EntityContainer $container
	 * @param bool $cleanUpGroups
	 * @throws \Nette\InvalidArgumentException
	 */
	public function remove(\Nette\Forms\Container $container, $cleanUpGroups=FALSE)
	{
		if (!$container instanceof EntityContainer) {
			throw new \Nette\InvalidArgumentException('Given container is not instance of Lohini\Database\Doctrine\Forms\EntityContainer, instance of '.get_class($container).' given.');
			}

		$entity=$container->getEntity();
		parent::remove($container, $cleanUpGroups);
		$this->getMapper()->remove($entity);
	}
}
