<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ORM;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Models\IEntity;

/**
 * @method \Lohini\Database\Doctrine\ORM\Container getContainer()
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 * @property-read \Doctrine\ORM\EntityRepository $repository
 * @property-read \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
 */
class BaseService
extends \Lohini\Database\Models\Services\Base
implements \Lohini\Database\Models\IService
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
	 * @param \Lohini\Database\Models\IEntity
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
			elseif (method_exists($entity, $method='get'.ucfirst($key)) && (is_array($value) || $value instanceof \Traversable)) {
				$entity->$method()->clear();
				foreach ($value as $item) {
					$entity->$method()->add($item);
					}
				}
			}
	}

	/**
	 * @param \PDOException
	 * @throws \Lohini\Database\Doctrine\Exception
	 * @throws \Lohini\Database\Doctrine\EmptyValueException
	 * @throws \Lohini\Database\Doctrine\DuplicateEntryException
	 */
	protected function processPDOException(\PDOException $e)
	{
		$info=$e->errorInfo;
		if ($info[0]==23000 && $info[1]==1062) { // unique fail
			// @todo how to detect column name ?
			throw new \Lohini\Database\Doctrine\DuplicateEntryException($e->getMessage(), NULL, $e);
			}
		elseif ($info[0]==23000 && $info[1]==1048) { // notnull fail
			// @todo convert table column name to entity column name
			$name=substr($info[2], strpos($info[2], "'")+1);
			$name=substr($name, 0, strpos($name, "'"));
			throw new \Lohini\Database\Doctrine\EmptyValueException($e->getMessage(), $name, $e);
			}
		else { // other fail
			throw new \Lohini\Database\Doctrine\Exception($e->getMessage(), 0, $e);
			}
	}

	/**
	 * @param array|\Traversable
	 * @param bool
	 * @return \Lohini\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Lohini\Database\Models\Exception
	 * @throws \Lohini\Database\Models\EmptyValueException
	 * @throws \Lohini\Database\Models\DuplicateEntryException
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
	 * @param \Lohini\Database\Models\IEntity
	 * @param array|\Traversable
	 * @param bool
	 * @return \Lohini\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Lohini\Database\Models\Exception
	 * @throws \Lohini\Database\Models\EmptyValueException
	 * @throws \Lohini\Database\Models\DuplicateEntryException
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
	 * @param \Lohini\Database\Models\IEntity
	 * @param bool
	 * @return \Lohini\Database\Models\IEntity
	 * @throws \Lohini\Database\Models\Exception
	 * @throws \Lohini\Database\Models\EmptyValueException
	 * @throws \Lohini\Database\Models\DuplicateEntryException
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
