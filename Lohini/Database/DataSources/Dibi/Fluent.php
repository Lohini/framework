<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\DataSources\Dibi;
/**
 * @author Pavel Kučera
 * @author Michael Moravec
 * @author Štěpán Svoboda
 * @author Petr Morávek
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\DataSources\IDataSource,
	Lohini\Database\DataSources\Utils\WildcardHelper;

/**
 * Dibi fluent based data source
 */
class Fluent
extends \Lohini\Database\DataSources\Mapped
{
	/** @var \DibiFluent instance */
	private $df;
	/** @var array fetched data */
	private $data;
	/** @var int total data count */
	private $count;


	/**
	 * Stores given dibi data fluent instance
	 * @param \DibiFluent $df
	 * @return IDataSource
	 */
	public function __construct(\DibiFluent $df)
	{
		$this->df=$df;
	}

	/**
	 * Adds filtering onto specified column
	 * @param string $column name
	 * @param string $operation filter
	 * @param string|array $value operation mode
	 * @param string $chainType (if third argument is array)
	 * @return IDataSource
	 * @throws \Nette\InvalidArgumentException
	 */
	public function filter($column, $operation=IDataSource::EQUAL, $value=NULL, $chainType=NULL)
	{
		if (!$this->hasColumn($column)) {
			throw new \Nette\InvalidArgumentException('Trying to filter data source by unknown column.');
			}

		if (is_array($operation)) {
			if ($chainType!==self::CHAIN_AND && $chainType!==self::CHAIN_OR) {
				throw new \Nette\InvalidArgumentException('Invalid chain operation type.');
				}
			$conds=array();
			foreach ($operation as $t) {
				$this->validateFilterOperation($t);
				if ($t===self::IS_NULL || $t===self::IS_NOT_NULL) {
					$conds[]=array('%n', $this->mapping[$column], $t);
					}
				else {
					$modifier= is_double($value)? dibi::FLOAT : dibi::TEXT;
					if ($operation===self::LIKE || $operation===self::NOT_LIKE) {
						$value=WildcardHelper::formatLikeStatementWildcards($value);
						}

					$conds[]=array('%n', $this->mapping[$column], $t, "%$modifier", $value);
					}
				}

			if ($chainType===self::CHAIN_AND) {
				foreach ($conds as $cond) {
					$this->df->where($cond);
					}
				}
			elseif ($chainType===self::CHAIN_OR) {
				$this->df->where('(%or)', $conds);
				}
			}
		else {
			$this->validateFilterOperation($operation);

			if ($operation===self::IS_NULL || $operation===self::IS_NOT_NULL) {
				$this->qb->where('%n', $this->mapping[$column], $operation);
				}
			else {
				$modifier= is_double($value)? dibi::FLOAT : dibi::TEXT;
				if ($operation===self::LIKE || $operation===self::NOT_LIKE) {
					$value=WildcardHelper::formatLikeStatementWildcards($value);
					}

				$this->df->where('%n', $this->mapping[$column], $operation, "%$modifier", $value);
				}
			}

		return $this;
	}

	/**
	 * Adds ordering to specified column
	 * @param string $column name
	 * @param string $order one of ordering types
	 * @return IDataSource
	 * @throws \Nette\InvalidArgumentException
	 */
	public function sort($column, $order=IDataSource::ASCENDING)
	{
		if (!$this->hasColumn($column)) {
			throw new \Nette\InvalidArgumentException('Trying to sort data source by unknown column.');
			}

		$this->df->orderBy($this->mapping[$column], $order===self::ASCENDING? 'ASC' : 'DESC');

		return $this;
	}

	/**
	 * Reduces the result starting from $start to have $count rows
	 * @param int $count the number of results to obtain
	 * @param int $start the offset
	 * @return IDataSource
	 * @throws \OutOfRangeException
	 */
	public function reduce($count, $start=0)
	{
		if ($count==NULL || $count>0) { //intentionally ==
			$this->df->limit($count==NULL? 0 : $count);
			}
		else {
			throw new \OutOfRangeException;
			}

		if ($start==NULL || ($start>0 && $start<count($this))) {
			$this->df->offset($start == NULL ? 0 : $start);
			}
		else {
			throw new \OutOfRangeException;
			}

		return $this;
	}

	/**
	 * Gets iterator over data source items
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fetch());
	}

	/**
	 * Fetches and returns the result data.
	 * @return array
	 */
	public function fetch()
	{
		return $this->data=$this->df->fetchAll();
	}

	/**
	 * Counts items in data source
	 * @return int
	 * @todo: if there is a group by clause in the query, count it correctly
	 */
	public function count()
	{
		$query=clone $this->df;

		$query->removeClause('select')
				->removeClause('limit')
				->removeClause('offset')
				->removeClause('order by')
				->select('count(*)');

		return $this->count=(int)$query->fetchSingle();
	}

	public function getFilterItems($column)
	{
		throw new \Nette\NotImplementedException;
	}

	/**
	 * Clones dibi fluent instance
	 */
	public function __clone()
	{
		$this->df=clone $this->df;
	}
}
