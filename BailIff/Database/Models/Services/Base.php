<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Models\Services;

use Nette\Environment as NEnvironment,
	BailIff\Database\Models\IEntity;

/**
 * BaseService
 *
 * @author Lopo <lopo@losys.sk>
 */
abstract class Base
extends \Nette\Object
{
	/** @var \BailIff\Database\Doctrine\BaseContainer */
	private $container;
	/** @var string */
	private $entityClass;


	public function __construct(\BailIff\Database\Doctrine\BaseContainer $container, $entityClass)
	{
		if (!class_exists($entityClass)) {
			throw new \Nette\InvalidArgumentException("Entity '$entityClass' does not exist");
			}
		elseif (!\Nette\Reflection\ClassType::from($entityClass)->implementsInterface('BailIff\Database\Models\IEntity')) {
			throw new \Nette\InvalidArgumentException(
					"Entity '$entityClass' isn't valid entity (must implements BailIff\\Database\\Models\\IEntity)"
				);
			}

		$this->container=$container;
		$this->entityClass=$entityClass;
	}

	/**
	 * @return \BailIff\Database\Doctrine\BaseContainer
	 */
	final public function getContainer()
	{
		return $this->container;
	}

	/**
	 * @return string
	 */
	final public function getEntityClass()
	{
		return $this->entityClass;
	}

	/**
	 * @return mixed
	 */
	protected function createEntityPrototype()
	{
		$class=$this->getEntityClass();
		return new $class;
	}

	/**
	 * @param \BailIff\Database\Models\IEntity
	 * @param array|\Traversable
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function fillData(IEntity $entity, $values)
	{
		if (!is_array($values) && !$values instanceof \Traversable) {
			throw new \Nette\InvalidArgumentException('Values must be array or Traversable');
			}

		foreach ($values as $key => $value) {
			$method='set'.ucfirst($key);
			if (method_exists($entity, $method)) {
				$entity->$method($value);
				}
			}
	}

	/**
	 * @param array|\Traversable
	 * @return \BailIff\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function create($values)
	{
		if (!is_array($values) && !$values instanceof \Traversable) {
			throw new \Nette\InvalidArgumentException('Values must be array or Traversable');
			}

		$entity=$this->createEntityPrototype();
		$this->fillData($entity, $values);
		return $entity;
	}

	/**
	 * @param \BailIff\Database\Models\IEntity
	 * @param array|\Traversable
	 * @return \BailIff\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function update(IEntity $entity, $values)
	{
		$this->fillData($entity, $values);
		return $entity;
	}
}
