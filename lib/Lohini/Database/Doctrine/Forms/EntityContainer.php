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
 * @method \Lohini\Database\Doctrine\Forms\Form getForm(bool $need=TRUE)
 * @method \Lohini\Database\Doctrine\Forms\Form|\Lohini\Database\Doctrine\Forms\EntityContainer|\Lohini\Database\Doctrine\Forms\CollectionContainer getParent()
 * @method void onSave(array $values, \Nette\Forms\Container $container)
 * @method void onLoad(array $values, object $entity)
 */
class EntityContainer
extends \Nette\Forms\Container
implements IObjectContainer
{
	/**
	 * Occurs when the entity values are being mapped to form
	 * @var array of function(array $values, object $entity);
	 */
	public $onLoad=array();
	/**
	 * Occurs when the form values are being mapped to entity
	 * @var array of function(array $values, Nette\Forms\Container $container);
	 */
	public $onSave=array();
	/** @var object */
	private $entity;
	/** @var EntityMapper */
	private $mapper;
	/** @var ContainerBuilder */
	private $builder;


	/**
	 * @param object $entity
	 * @param EntityMapper $mapper
	 */
	public function __construct($entity, EntityMapper $mapper=NULL)
	{
		parent::__construct();
		$this->monitor('Lohini\Database\Doctrine\Forms\Form');

		$this->entity=$entity;
		$this->mapper=$mapper;
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
	 * @param \Nette\ComponentModel\IContainer
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
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return EntityMapper
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
		parent::attached($obj);

		if ($obj instanceof Form) {
			foreach ($this->getMapper()->getIdentifierValues($this->entity) as $key => $id) {
				$this->addHidden($key)->setDefaultValue($id);
				}

			$this->getMapper()->assign($this->entity, $this);
			}
	}

	/**
	 * @param string $name
	 * @param object $entity
	 * @return \Lohini\Database\Doctrine\Forms\EntityContainer
	 */
	public function addOne($name, $entity=NULL)
	{
		return $this[$name]=new EntityContainer($entity ?: $this->getMapper()->getRelated($this, $name));
	}

	/**
	 * @param $name
	 * @param $factory
	 * @param int $createDefault
	 * @return CollectionContainer
	 */
	public function addMany($name, $factory, $createDefault=0)
	{
		$this[$name]= $container= new CollectionContainer($this->getMapper()->getCollection($this->entity, $name), $factory);
		$container->createDefault=$createDefault;
		return $container;
	}
}
