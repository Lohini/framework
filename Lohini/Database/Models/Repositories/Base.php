<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Repositories;

use Nette\ObjectMixin;

/**
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 * @property-read \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
 */
class Base
extends \Doctrine\ORM\EntityRepository
//implements \Kdyby\Validation\IStorage
{
	/**
	 * @param object $entity
	 */
	public function save($entity)
	{
		if (!$entity instanceof $this->_entityName) {
			throw new \Nette\InvalidArgumentException("Entity is not instanceof $this->_entityName, ".get_class($entity).' given.');
			}

		$this->_em->persist($entity);
		$this->_em->flush();
	}

	/**
	 * Create a new QueryBuilder instance that is prepopulated for this entity name
	 *
	 * @param string $alias
	 * @return \Lohini\Database\Models\QueryBuilder $alias
	 */
	public function createQueryBuilder($alias)
	{
		return $this->doCreateQueryBuilder()
				->select($alias)
				->from($this->_entityName, $alias);
	}

	/**
	 * @return \Lohini\Database\Models\QueryBuilder
	 */
	protected function doCreateQueryBuilder()
	{
		return new QueryBuilder($this->getEntityManager());
	}

	/**
	 * Does an entity with a key equal to value exist?
	 *
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	public function doesExistByColumn($key, $value)
	{
		$res=$this->findOneBy(array($key => $value));
		return !empty($res);
	}

	/**
	 * Does an entity with key equal to value exist and is not same as given entity id?
	 *
	 * @param string
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	public function isColumnUnique($id, $key, $value)
	{
		$res=$this->findOneBy(array($key => $value));
		return empty($res) ?: $res->id==$id;
	}

	/**
	 * Fetches all records that correspond to ids of a given array
	 *
	 * @param array
	 * @return array
	 */
	public function findByIds(array $ids)
	{
		$arr=array();
		$qb=$this->createQueryBuilder('uni');
		$qb->where($qb->expr()->in('uni.id', $ids));
		foreach ($qb->getQuery()->getResult() as $res) {
			$arr[$res->id]=$res;
			}

		return $arr;
	}

	/**
	 * Fetches all records like $key => $value pairs
	 *
	 * @param string
	 * @param string
	 * @return array
	 */
	public function fetchPairs($key=NULL, $value=NULL)
	{
		$res=$this->createQueryBuilder('uni')
				->select("uni.$key, uni.$value")
			->getQuery()->getResult();

		$arr=array();
		foreach ($res as $row) {
			$arr[$row[$key]]=$row[$value];
			}

		return $arr;
	}

	/**
	 * Fetches all records and returns an associative array indexed by key
	 *
	 * @param string
	 * @return array
	 */
	public function fetchAssoc($key)
	{
		$res=$this->findAll();

		$arr=array();
		foreach ($res as $doc) {
			if (array_key_exists($doc->$key, $arr)) {
				throw new \Nette\InvalidStateException("Key value {$doc->{'get'.ucfirst($key)}} is duplicit in fetched associative array. Try to use different associative key");
				}
			$arr[$doc->{'get'.ucfirst($key)}()]=$doc;
			}

		return $arr;
	}

	/********************* \Lohini\Validation\IStorage *********************/
	/**
	 * @param string $attribute
	 * @param mixed $value
	 * @return int
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
			throw new \Lohini\Database\Doctrine\QueryException($e->getMessage(), $this->qb->getQuery(), $e);
			}
	}

	/********************* \Nette\Object behaviour ****************d*g**/
	/**
	 * Call to undefined method
	 *
	 * @param string $name method name
	 * @param array $args arguments
	 * @return mixed
	 * @throws \Nette\MemberAccessException
	 */
	public function __call($name, $args)
	{
		try {
			return parent::__call($name, $args);
			}
		catch (\BadMethodCallException $e) {
			return ObjectMixin::call($this, $name, $args);
			}
	}

	/**
	 * Call to undefined static method
	 *
	 * @param string $name method name (in lower case!)
	 * @param array $args arguments
	 * @return mixed
	 * @throws \Nette\MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		return ObjectMixin::callStatic(get_called_class(), $name, $args);
	}

	/**
	 * Adding method to class
	 *
	 * @param string $name method name
	 * @param mixed $callback or closure
	 * @return mixed
	 */
	public static function extensionMethod($name, $callback=NULL)
	{
		if (strpos($name, '::')===FALSE) {
			$class=get_called_class();
			}
		else {
			list($class, $name)=explode('::', $name);
			}
		$class=new \Nette\Reflection\ClassType($class);
		if ($callback===NULL) {
			return $class->getExtensionMethod($name);
			}
		else {
			$class->setExtensionMethod($name, $callback);
			}
	}

	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new \Nette\Reflection\ClassType(get_called_class());
	}

	/**
	 * @param string $name
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
