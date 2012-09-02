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
	Lohini\Persistence\IQueryable;

/**
 */
abstract class QueryObjectBase
extends \Nette\Object
implements \Lohini\Persistence\IQueryObject
{
	/** @var \Doctrine\ORM\Query */
	private $lastQuery;
	/** @var \Lohini\Database\Doctrine\ResultSet */
	private $lastResult;


	/**
	 */
	public function __construct()
	{
	}

	/**
	 * @param IQueryable $repository
	 * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
	 */
	protected abstract function doCreateQuery(IQueryable $repository);

	/**
	 * @param IQueryable $repository
	 * @return \Doctrine\ORM\Query
	 * @throws \Lohini\UnexpectedValueException
	 */
	private function getQuery(IQueryable $repository)
	{
		$query=$this->doCreateQuery($repository);
		if ($query instanceof \Doctrine\ORM\QueryBuilder) {
			$query=$query->getQuery();
			}

		if (!$query instanceof \Doctrine\ORM\Query) {
			$class=$this->getReflection()->getMethod('doCreateQuery')->getDeclaringClass();
			throw new \Lohini\UnexpectedValueException("Method $class".'::doCreateQuery() must return'
				.' instanceof Doctrine\ORM\Query or instanceof Doctrine\ORM\QueryBuilder, '
				.\Lohini\Utils\Tools::getType($query).' given.'
				);
			}

		if ($this->lastQuery && $this->lastQuery->getDQL()===$query->getDQL()) {
			$query=$this->lastQuery;
			}

		if ($this->lastQuery!==$query) {
			$this->lastResult=new ResultSet($query);
			}

		return $this->lastQuery=$query;
	}

	/**
	 * @param IQueryable $repository
	 * @return int
	 */
	public function count(IQueryable $repository)
	{
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
		$query=$this->getQuery($repository)
			->setFirstResult(NULL)
			->setMaxResults(NULL);

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
		$query=$this->getQuery($repository)
			->setFirstResult(NULL)
			->setMaxResults(1);

		return $query->getSingleResult();
	}

	/**
	 * @internal For Debugging purposes only!
	 * @return \Doctrine\ORM\Query
	 */
	public function getLastQuery()
	{
		return $this->lastQuery;
	}
}
