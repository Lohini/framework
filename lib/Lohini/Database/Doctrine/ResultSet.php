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

use Doctrine\ORM,
	Nette\Utils\Strings;

/**
 */
class ResultSet
extends \Nette\Object
implements \Countable, \IteratorAggregate
{
	/** @var int */
	private $totalCount;
	/** @var \Doctrine\ORM\Query */
	private $query;
	/** @var \Doctrine\ORM\Tools\Pagination\Paginator */
	private $paginatedQuery;


	/**
	 * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ORM\Query $query
	 * @throws \Nette\InvalidArgumentException
	 */
	public function __construct($query)
	{
		if ($query instanceof ORM\QueryBuilder) {
			$this->query=$query->getQuery();
			}
		elseif ($query instanceof ORM\AbstractQuery) {
			$this->query=$query;
			}
		else {
			throw new \Nette\InvalidArgumentException('Given argument is not instanceof Query or QueryBuilder.');
			}
	}

	/**
	 * @param string|array $columns
	 * @return \Lohini\Database\Doctrine\ResultSet
	 * @throws \Nette\InvalidStateException
	 */
	public function applySorting($columns)
	{
		if ($this->paginatedQuery!==NULL) {
			throw new \Nette\InvalidStateException('Cannot modify result set, that was fetched from storage.');
			}
		if ($this->query instanceof ORM\NativeQuery) {
			throw new \Nette\InvalidStateException("Can't call ".__CLASS__.'::applySorting() when using NativeQuery.');
			}

		$sorting=array();
		foreach (is_array($columns)? $columns : func_get_args() as $column) {
			$lColumn=Strings::lower($column);
			if (!Strings::endsWith($lColumn, ' desc') && !Strings::endsWith($lColumn, ' asc')) {
				$column.=' ASC';
				}
			$sorting[]=$column;
			}

		if ($sorting) {
			$dql=$this->query->getDQL();
			$dql.=!$this->query->contains('ORDER BY') ? ' ORDER BY ' : ', ';
			$dql.=implode(', ', $sorting);
			$this->query->setDQL($dql);
			}

		return $this;
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 * @return \Lohini\Database\Doctrine\ResultSet
	 * @throws \Nette\InvalidStateException
	 */
	public function applyPaging($offset, $limit)
	{
		if ($this->paginatedQuery!==NULL) {
			throw new \Nette\InvalidStateException('Cannot modify result set, that was fetched from storage.');
			}
		if ($this->query instanceof ORM\NativeQuery) {
			throw new \Nette\InvalidStateException("Can't call ".__CLASS__.'::applyPaging() when using NativeQuery.');
			}

		$this->query->setFirstResult($offset);
		$this->query->setMaxResults($limit);
		return $this;
	}

	/**
	 * @param \Nette\Utils\Paginator $paginator
	 * @return \Lohini\Database\Doctrine\ResultSet
	 */
	public function applyPaginator(\Nette\Utils\Paginator $paginator)
	{
		$this->applyPaging($paginator->getOffset(), $paginator->getLength());
		return $this;
	}

	/**
	 * @return bool
	 * @throws \Nette\InvalidStateException
	 */
	public function isEmpty()
	{
		if ($this->query instanceof ORM\NativeQuery) {
			throw new \Nette\InvalidStateException("Can't call ".__CLASS__.'::isEmpty() when using NativeQuery.');
			}
		$count=$this->getTotalCount();
		$offset=$this->query->getFirstResult();
		return $count <= $offset;
	}

	/**
	 * @return int
	 * @throws \Nette\InvalidStateException
	 * @throws \Lohini\Database\Doctrine\QueryException
	 */
	public function getTotalCount()
	{
		if ($this->query instanceof ORM\NativeQuery) {
			throw new \Nette\InvalidStateException("Can't call ".__CLASS__.'::getTotalCount() when using NativeQuery.');
			}
		if ($this->totalCount===NULL) {
			try {
				$this->totalCount=$this->getPaginatedQuery()->count();
				}
			catch (\Doctrine\ORM\ORMException $e) {
				throw new QueryException($e, $this->query, $e->getMessage());
				}
			}

		return $this->totalCount;
	}

	/**
	 * @return \ArrayIterator
	 * @throws \Lohini\Database\Doctrine\QueryException
	 */
	public function getIterator()
	{
		try {
			return new \ArrayIterator($this->query->execute());
			}
		catch (\Doctrine\ORM\ORMException $e) {
			throw new QueryException($e, $this->query, $e->getMessage());
			}
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return $this->getTotalCount();
	}

	/**
	 * @return \Doctrine\ORM\Tools\Pagination\Paginator
	 */
	private function getPaginatedQuery()
	{
		if ($this->paginatedQuery===NULL) {
			$this->paginatedQuery=new \Doctrine\ORM\Tools\Pagination\Paginator($this->query);
			}

		return $this->paginatedQuery;
	}
}
