<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\AbstractQuery,
	Doctrine\ORM\NativeQuery,
	Lohini\Persistence\IQueryable;

/**
 */
abstract class QueryObjectBase
extends \Nette\Object
implements \Lohini\Persistence\IQueryObject
{
	/** @var AbstractQuery */
	private $lastQuery;
	/** @var ResultSet */
	private $lastResult;


	/**
	 */
	public function __construct()
	{
	}

	/**
	 * @param IQueryable $repository
	 * @return AbstractQuery|\Doctrine\ORM\QueryBuilder
	 */
	protected abstract function doCreateQuery(IQueryable $repository);

	/**
	 * @param IQueryable $repository
	 * @return \Doctrine\ORM\Query|NativeQuery
	 * @throws \Lohini\UnexpectedValueException
	 */
	private function getQuery(IQueryable $repository)
	{
		$query=$this->doCreateQuery($repository);
		if ($query instanceof \Doctrine\ORM\QueryBuilder) {
			$query=$query->getQuery();
			}

		if (!$query instanceof AbstractQuery) { // Query|NativeQuery
			$class=$this->getReflection()->getMethod('doCreateQuery')->getDeclaringClass();
			throw new \Lohini\UnexpectedValueException("Method $class".'::doCreateQuery() must return'
				.' instanceof Doctrine\ORM\Query or instanceof Doctrine\ORM\QueryBuilder or instanceof Doctrine\ORM\NativeQuery, '
				.\Lohini\Utils\Tools::getType($query).' given.'
				);
			}

		if ($this->lastQuery) {
			if (($query instanceof \Doctrine\ORM\Query && $this->lastQuery->getDQL()===$query->getDQL())
				|| ($query instanceof NativeQuery && $this->lastQuery->getSQL()===$query->getSQL())
				) {
				$query=$this->lastQuery;
				}
			}

		if ($this->lastQuery!==$query) {
			$this->lastResult=new ResultSet($query);
			}

		return $this->lastQuery=$query;
	}

	/**
	 * @param IQueryable $repository
	 * @return int
	 * @throws \Nette\InvalidStateException
	 */
	public function count(IQueryable $repository)
	{
		if ($this->getQuery($repository) instanceof NativeQuery) {
			$class=$this->getReflection()->getMethod('doCreateQuery')->getDeclaringClass();
			throw \Nette\InvalidStateException("Can't use $class".'::count() when using NativeQuery.');
			}
		return $this->fetch($repository)
			->getTotalCount();
	}

	/**
	 * @param IQueryable $repository
	 * @param int $hydrationMode
	 * @return \Lohini\Database\Doctrine\ResultSet|array
	 */
	public function fetch(IQueryable $repository, $hydrationMode=AbstractQuery::HYDRATE_OBJECT)
	{
		if (!($query=$this->getQuery($repository)) instanceof NativeQuery) {
			$query
				->setFirstResult(NULL)
				->setMaxResults(NULL);
			}

		return $hydrationMode!==AbstractQuery::HYDRATE_OBJECT
			? $query->execute(array(), $hydrationMode)
			: $this->lastResult;
	}

	/**
	 * @param IQueryable $repository
	 * @return object
	 */
	public function fetchOne(IQueryable $repository)
	{
		if (!($query=$this->getQuery($repository)) instanceof NativeQuery) {
			$query
				->setFirstResult(NULL)
				->setMaxResults(1);
			}

		return $query->getSingleResult();
	}

	/**
	 * @internal For Debugging purposes only!
	 * @return \Doctrine\ORM\Query|NativeQuery
	 */
	public function getLastQuery()
	{
		return $this->lastQuery;
	}
}
