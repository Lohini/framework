<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\AbstractQuery,
	Doctrine\Common\Collections\Collection,
	Lohini\Persistence,
	Nette\ObjectMixin;

/**
 */
class Dao
extends \Doctrine\ORM\EntityRepository
implements Persistence\IDao, Persistence\IQueryExecutor, Persistence\IQueryable, Persistence\IObjectFactory
{
	/** @var \Lohini\Database\Doctrine\Mapping\ValuesMapper */
	private $entityMapper;


	/**
	 * @return \Lohini\Database\Doctrine\Mapping\ValuesMapper
	 */
	private function getEntityValuesMapper()
	{
		if ($this->entityMapper===NULL) {
			$this->entityMapper=new Mapping\ValuesMapper($this->_class, $this->_em);
			}

		return $this->entityMapper;
	}

	/**
	 * @param array $arguments Arguments for entity's constructor
	 * @param array $values Values to be set via mapper
	 * @return object
	 */
	public function createNew($arguments=array(), $values=array())
	{
		$class = $this->getEntityName();
		if (!$arguments) {
			$entity=new $class;
			}
		else {
			$reflection=new \Nette\Reflection\ClassType($class);
			$entity=$reflection->newInstanceArgs($arguments);
			}

		if ($values) {
			$this->getEntityValuesMapper()->load($entity, $values);
			}

		return $entity;
	}

	/**
	 * Persists given entities, but does not flush.
	 *
	 * @param object|array|\Doctrine\Common\Collections\Collection $entity
	 * @return object|array
	 * @throws \Nette\InvalidArgumentException
	 */
	public function add($entity)
	{
		if ($entity instanceof Collection) {
			return $this->add($entity->toArray());
			}
		if (is_array($entity)) {
			return array_map(array($this, 'add'), $entity);
			}
		if (!$entity instanceof $this->_entityName) {
			throw new \Nette\InvalidArgumentException("Entity is not instanceof $this->_entityName, instanceof '".get_class($entity)."' given.");
			}

		$this->getEntityManager()->persist($entity);
		return $entity;
	}

	/**
	 * Persists given entities and flushes all to the storage.
	 *
	 * @param object|array|\Doctrine\Common\Collections\Collection $entity
	 * @return object|array
	 */
	public function save($entity=NULL)
	{
		if ($entity!==NULL) {
			$result=$this->add($entity);
			$this->flush();
			return $result;
			}

		$this->flush();
	}

	/**
	 * Fetches all records like $key => $value pairs
	 *
	 * @param array $criteria
	 * @param string $value
	 * @param string $key
	 * @return array
	 */
	public function findPairs($criteria, $value=NULL, $key='id')
	{
		if (!is_array($criteria)) {
			$key= $value ?: 'id';
			$value=$criteria;
			$criteria=array();
			}

		$builder=$this->createQueryBuilder('e')
			->select("e.$key, e.$value");

		foreach ($criteria as $k => $v) {
			$builder->andWhere('e.'.$k.' = :prop'.$k)
				->setParameter('prop'.$k, $v);
			}
		$query=$builder->getQuery();

		try {
			$pairs=array();
			foreach ($res=$query->getResult(AbstractQuery::HYDRATE_ARRAY) as $row) {
				if (empty($row)) {
					continue;
					}

				$pairs[$row[$key]]=$row[$value];
				}

			return $pairs;
			}
		catch (\Exception $e) {
			return $this->handleException($e, $query);
			}
	}

	/**
	 * Fetches all records and returns an associative array indexed by key
	 *
	 * @param array $criteria
	 * @param string $key
	 * @return array
	 */
	public function findAssoc($criteria, $key=NULL)
	{
		if (!is_array($criteria)) {
			$key=$criteria;
			$criteria=array();
			}

		$query=$this->createQuery();
		try {
			$where= $params= array();
			foreach ($criteria as $k => $v) {
				$where[]="e.$k = :prop$k";
				$params["prop$k"]=$v;
				}

			$where= $where? 'WHERE '.implode(' AND ', $where) : NULL;
			$query->setDQL('SELECT e FROM '.$this->getEntityName()." e INDEX BY e.$key $where");
			$query->setParameters($params);
			return $query->getResult();
			}
		catch (\Exception $e) {
			return $this->handleException($e, $query);
			}
	}

	/**
	 * @param object|array|\Doctrine\Common\Collections\Collection $entity
	 * @param bool $withoutFlush
	 * @return NULL
	 * @throws \Nette\InvalidArgumentException
	 */
	public function delete($entity, $withoutFlush=Persistence\IDao::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->delete($entity->toArray(), $withoutFlush);
			}

		if (is_array($entity)) {
			$dao=$this;
			array_map(
				function($entity) use ($dao) {
					/** @var \Lohini\Database\Doctrine\Dao $dao */
					return $dao->delete($entity, Persistence\IDao::NO_FLUSH);
					},
				$entity
				);

			$this->flush($withoutFlush);
			return NULL;
			}

		if (!$entity instanceof $this->_entityName) {
			throw new \Nette\InvalidArgumentException("Entity is not instanceof $this->_entityName, ".get_class($entity).' given.');
			}

		$this->getEntityManager()->remove($entity);
		$this->flush($withoutFlush);
	}

	/**
	 * @param bool $withoutFlush
	 */
	protected function flush($withoutFlush=Persistence\IDao::FLUSH)
	{
		if ($withoutFlush===Persistence\IDao::FLUSH) {
			try {
				$this->getEntityManager()->flush();
				}
			catch (\PDOException $e) {
				$this->handleException($e);
				}
			}
	}

	/**
	 * @param string $alias
	 * @return \Lohini\Database\Doctrine\QueryBuilder $qb
	 */
	public function createQueryBuilder($alias=NULL)
	{
		$qb=new QueryBuilder($this->getEntityManager());

		if ($alias!==NULL) {
			$qb->select($alias)->from($this->getEntityName(), $alias);
			}

		return $qb;
	}

	/**
	 * @param string $dql
	 * @return \Doctrine\ORM\Query
	 */
	public function createQuery($dql = NULL)
	{
		$dql=implode(' ', func_get_args());
		return $this->getEntityManager()->createQuery($dql);
	}

	/**
	 * @param callable $callback
	 * @return mixed|bool
	 * @throws \Exception
	 */
	public function transactional($callback)
	{
		$connection=$this->getEntityManager()->getConnection();
		$connection->beginTransaction();

		try {
			$return=callback($callback)->invoke($this);
			$this->flush();
			$connection->commit();
			return $return ?: TRUE;
			}
		catch (\Exception $e) {
			$connection->rollback();
			throw $e;
			}
	}

	/**
	 * @param Persistence\IQueryObject|\Lohini\Database\Doctrine\QueryObjectBase $queryObject
	 * @return integer
	 */
	public function count(Persistence\IQueryObject $queryObject)
	{
		try {
			return $queryObject->count($this);
			}
		catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
			}
	}

	/**
	 * @param Persistence\IQueryObject|\Lohini\Database\Doctrine\QueryObjectBase $queryObject
	 * @return array|\Lohini\Database\Doctrine\ResultSet
	 */
	public function fetch(Persistence\IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetch($this);
			}
		catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
			}
	}

	/**
	 * @param Persistence\IQueryObject|\Lohini\Database\Doctrine\QueryObjectBase $queryObject
	 * @return object
	 * @throws \Nette\InvalidStateException
	 */
	public function fetchOne(Persistence\IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetchOne($this);
			}
		catch (\Doctrine\ORM\NoResultException $e) {
			return NULL;
			}
		catch (\Doctrine\ORM\NonUniqueResultException $e) { // this should never happen!
			throw new \Nette\InvalidStateException('You have to setup your query calling ->setMaxResult(1).', 0, $e);
			}
		catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
			}
	}

	/**
	 * @param Persistence\IQueryObject|\Lohini\Database\Doctrine\QueryObjectBase $queryObject
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	public function fetchPairs(Persistence\IQueryObject $queryObject, $key=NULL, $value=NULL)
	{
		try {
			$pairs=array();
			foreach ($queryObject->fetch($this, AbstractQuery::HYDRATE_ARRAY) as $row) {
				$offset= $key? $row[$key] : reset($row);
				$pairs[$offset]= $value? $row[$value] : next($row);
				}
			return array_filter($pairs); // todo: orly?
			}
		catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
			}
	}

	/**
	 * Fetches all records and returns an associative array indexed by key
	 *
	 * @param Persistence\IQueryObject|\Lohini\Database\Doctrine\QueryObjectBase $queryObject
	 * @param string $key
	 * @return array
	 * @throws \Exception
	 * @throws \Nette\InvalidStateException
	 */
	public function fetchAssoc(Persistence\IQueryObject $queryObject, $key=NULL)
	{
		try {
			/** @var \Lohini\Database\Doctrine\ResultSet|mixed $resultSet */
			$resultSet=$queryObject->fetch($this);
			if (!$resultSet instanceof ResultSet || !($result=iterator_to_array($resultSet->getIterator()))) {
				return NULL;
				}

			try {
				$meta=$this->_em->getClassMetadata(get_class(current($result)));
				}
			catch (\Exception $e) {
				throw new \Nette\InvalidStateException('Result of '.get_class($queryObject).' is not list of entities.');
				}

			$assoc=array();
			foreach ($result as $item) {
				$assoc[$meta->getFieldValue($item, $key)] = $item;
				}
			return $assoc;
			}
		catch (\Nette\InvalidStateException $e) {
			throw $e;
			}
		catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
			}
	}

	/**
	 * @param int|array $id
	 * @return \Doctrine\ORM\Proxy\Proxy
	 */
	public function getReference($id)
	{
		return $this->getEntityManager()->getReference($this->_entityName, $id);
	}

	/**
	 * @param \Exception $e
	 * @param \Lohini\Database\Doctrine\QueryObjectBase $queryObject
	 * @throws \Exception
	 */
	private function handleQueryException(\Exception $e, QueryObjectBase $queryObject)
	{
		$this->handleException($e, $queryObject->getLastQuery(), '['.get_class($queryObject).'] '.$e->getMessage());
	}

	/**
	 * @param \Exception $e
	 * @param AbstractQuery $query
	 * @param string $message
	 * @throws \Exception
	 * @throws \Lohini\Database\Doctrine\QueryException
	 * @throws \Lohini\Database\Doctrine\SqlException
	 */
	private function handleException(\Exception $e, AbstractQuery $query=NULL, $message=NULL)
	{
		if ($e instanceof \Doctrine\ORM\Query\QueryException) {
			throw new QueryException($e, $query, $message);
			}
		if ($e instanceof \PDOException) {
			throw new SqlException($e, $query, $message);
			}
		throw $e;
	}

	/**
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata
	 */
	public function getClassMetadata()
	{
		return parent::getClassMetadata();
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return parent::getEntityManager();
	}

	/**
	 * @param string $relation
	 * @return \Lohini\Database\Doctrine\Dao
	 */
	public function related($relation)
	{
		$meta=$this->getClassMetadata();
		$targetClass=$meta->getAssociationTargetClass($relation);
		return $this->getEntityManager()->getRepository($targetClass);
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
