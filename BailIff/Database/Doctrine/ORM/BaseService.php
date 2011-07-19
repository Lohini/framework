<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author Patrik Votoček
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use BailIff\Database\Models\IEntity;

/**
 * @method \BailIff\Database\Doctrine\ORM\Container getContainer()
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 * @property-read \Doctrine\ORM\EntityRepository $repository
 * @property-read \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
 */
class BaseService
extends \BailIff\Database\Models\Services\Base
implements \BailIff\Database\Models\IService
{
	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->getContainer()->getEntityManager();
	}

	/**
	 * @return Repository
	 */
	public function getRepository()
	{
		return $this->getEntityManager()->getRepository($this->getEntityClass());
	}

	/**
	 * @return \Doctrine\ORM\Mapping\ClassMetadata
	 */
	public function getClassMetadata()
	{
		return $this->getEntityManager()->getClassMetadata($this->getEntityClass());
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
			if (method_exists($entity, 'get'.ucfirst($key)) && (is_array($value) || $value instanceof \Traversable)) {
				$entity->$method()->clear();
				foreach ($value as $item) {
					$entity->$method()->add($item);
					}
				}
			elseif (method_exists($entity, $method)) {
				$entity->$method($value);
				}
			}
	}

	/**
	 * @param \PDOException
	 * @throws \BailIff\Database\Models\Exception
	 * @throws \BailIff\Database\Models\EmptyValueException
	 * @throws \BailIff\Database\Models\DuplicateEntryException
	 */
	protected function processPDOException(\PDOException $e)
	{
		$info=$e->errorInfo;
		if ($info[0]==23000 && $info[1]==1062) { // unique fail
			// @todo how to detect column name ?
			throw new \BailIff\Database\Models\DuplicateEntryException($e->getMessage(), NULL, $e);
			}
		elseif ($info[0]==23000 && $info[1]==1048) { // notnull fail
			// @todo convert table column name to entity column name
			$name=substr($info[2], strpos($info[2], "'")+1);
			$name=substr($name, 0, strpos($name, "'"));
			throw new \BailIff\Database\Models\EmptyValueException($e->getMessage(), $name, $e);
			}
		else { // other fail
			throw new \BailIff\Database\Models\Exception($e->getMessage(), 0, $e);
			}
	}

	/**
	 * @param array|\Traversable
	 * @param bool
	 * @return \BailIff\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 * @throws \BailIff\Database\Models\Exception
	 * @throws \BailIff\Database\Models\EmptyValueException
	 * @throws \BailIff\Database\Models\DuplicateEntryException
	 */
	public function create($values, $withoutFlush=FALSE) {
		try {
			$entity=parent::create($values);
			$em=$this->getEntityManager();
			$em->persist($entity);
			if (!$withoutFlush) {
				$em->flush();
				}
			return $entity;
			}
		catch (\PDOException $e) {
			$this->processPDOException($e);
			}
	}

	/**
	 * @param \BailIff\Database\Models\IEntity
	 * @param array|\Traversable
	 * @param bool
	 * @return \BailIff\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 * @throws \BailIff\Database\Models\Exception
	 * @throws \BailIff\Database\Models\EmptyValueException
	 * @throws \BailIff\Database\Models\DuplicateEntryException
	 */
	public function update(IEntity $entity, $values=NULL, $withoutFlush=FALSE)
	{
		try {
			if ($values) {
				parent::update($entity, $values);
				}
			$em=$this->getEntityManager();
			//$em->persist($entity); // maybe need
			if (!$withoutFlush) {
				$em->flush();
				}
			return $entity;
			}
		catch (\PDOException $e) {
			$this->processPDOException($e);
			}
	}

	/**
	 * @param \BailIff\Database\Models\IEntity
	 * @param bool
	 * @return \BailIff\Database\Models\IEntity
	 * @throws \BailIff\Database\Models\Exception
	 * @throws \BailIff\Database\Models\EmptyValueException
	 * @throws \BailIff\Database\Models\DuplicateEntryException
	 */
	public function delete(IEntity $entity, $withoutFlush=FALSE) {
		try {
			$em=$this->getEntityManager();
			$em->remove($entity);
			if (!$withoutFlush) {
				$em->flush();
				}
			return $entity;
			}
		catch (\PDOException $e) {
			$this->processPDOException($e);
			}
	}
}
