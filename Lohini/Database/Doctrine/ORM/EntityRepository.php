<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ORM;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\ObjectMixin;

class EntityRepository
extends \Doctrine\ORM\EntityRepository
{
	/**
	 * @param object $entity
	 * @param bool $validate
	 * @throws \Nette\InvalidArgumentException
	 */
	public function save($entity, $validate=TRUE)
	{
		if (!$entity instanceof $this->_entityName) {
			throw new \Nette\InvalidArgumentException("Entity is not instanceof $this->_entityName, ".get_class($entity).' given.');
			}

		// TODO: validate

		$this->_em->persist($entity);
		$this->_em->flush(); // TODO: orly?
	}

	/**
	 * @param object $entity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function delete($entity)
	{
		if (!$entity instanceof $this->_entityName) {
			throw new \Nette\InvalidArgumentException("Entity is not instanceof $this->_entityName, ".get_class($entity).' given.');
			}

		$this->_em->remove($entity);
		$this->_em->flush(); // TODO: orly?
	}

	/**
	 * Creates a new QueryBuilder instance that is prepopulated for this entity name
	 *
	 * @param string $alias
	 * @return QueryBuilder $qb
	 */
	public function createQueryBuilder($alias)
	{
		return $this->doCreateQueryBuilder()
				->select($alias)
				->from($this->_entityName, $alias);
	}

	/**
	 * @return \Lohini\Database\Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQueryBuilder()
	{
		return new QueryBuilder($this->getEntityManager());
	}

	/**
	 * @param string $attribute
	 * @param mixed $value
	 * @return int
	 * @throws \Lohini\Database\Doctrine\ORM\QueryException
	 */
	public function countByAttribute($attribute, $value)
	{
		$qb=$this->createQueryBuilder('e')
			->select('count(e) fullcount')
			->where("e.$attribute = :value")
			->setParameter('value', $value);

		try {
			return (int)$qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);
			}
		catch (\Doctrine\ORM\ORMException $e) {
			throw new QueryException($e->getMessage(), $this->qb->getQuery(), $e);
			}
	}

	/********************* Nette\Object behaviour ****************d*g**/
	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new \Nette\Reflection\ClassType(get_called_class());
	}

	/**
	 * @param string property name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}
}
