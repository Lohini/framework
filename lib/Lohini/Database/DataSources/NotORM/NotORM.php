<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\DataSources\NotORM;

use Lohini\Database\DataSources\IDataSource;

/**
 * @author Lopo <lopo@lohini.net>
 */
class NotORM
extends \Lohini\Database\DataSources\Mapped
{
	/** @var \NotORM_Result */
	private $result;
	/** @var int Total data count */
	private $count;


	/**
	 * @param \NotORM_Result $result
	 */
	public function __construct(\NotORM_Result $result)
	{
		$this->result=$result;
	}

	/**
	 * @param array $mapping
	 */
	public function setMapping(array $mapping)
	{
		parent::setMapping($mapping);
		$selects=array();
		foreach ($mapping as $k => $m) {
			$selects[]="$m AS `$k`";
			}
		$this->result->select(implode(',', $selects));
	}

	/**
	 * Add filtering onto specified column
	 *
	 * @param string $column column name
	 * @param string $operation filter
	 * @param string|array $value operation mode
	 * @param string $chainType chain type (if third argument is array)
	 * @return NotORM (fluent)
	 * @throws \Nette\InvalidArgumentException
	 */
	public function filter($column, $operation=IDataSource::EQUAL, $value=NULL, $chainType=NULL)
	{
		$col=$column;
		if ($this->hasColumn($column)) {
			$col=$this->mapping[$column];
			}

		if (is_array($operation)) {
			if ($chainType!==self::CHAIN_AND && $chainType!==self::CHAIN_OR) {
				throw new \Nette\InvalidArgumentException('Invalid chain operation type.');
				}
			}
		else {
			$operation = array($operation);
			}

		if (empty($operation)) {
			throw new \Nette\InvalidArgumentException('Operation cannot be empty.');
			}

		$conds=array();
		$values=array();
		foreach ($operation as $o) {
			$this->validateFilterOperation($o);

			$c="$col $o";
			if ($o!==self::IS_NULL && $o!==self::IS_NOT_NULL) {
				$c.=' ?';

				$values[]= ($o===self::LIKE || $o===self::NOT_LIKE)
					? \Lohini\Database\DataSources\Utils\WildcardHelper::formatLikeStatementWildcards($value)
					: $value;
				}

			$conds[]=$c;
			}

		$conds=implode(" ( $chainType ) ", $conds); // "(cond1) OR (cond2) ..."  -- outer braces missing for now
		$this->result->where("( $conds )", $values);

		return $this;
	}

	/**
	 * Adds ordering to specified column
	 *
	 * @param string $column column name
	 * @param string $order one of ordering types
	 * @return NotORM (fluent)
	 */
	public function sort($column, $order=IDataSource::ASCENDING)
	{
		if (!$this->hasColumn($column)) {
			$this->result->order($column. ' '.($order===self::ASCENDING? 'ASC' : 'DESC'));
			}
		else {
			$this->result->order($this->mapping[$column].' '.($order===self::ASCENDING? 'ASC' : 'DESC'));
			}

		return $this;
	}

	/**
	 * Reduce the result starting from $start to have $count rows
	 *
	 * @param int $count the number of results to obtain
	 * @param int $start the offset
	 * @return NotORM (fluent)
	 * @throws \Nette\OutOfRangeException
	 */
	public function reduce($count, $start=0)
	{
		// Delibearately skipping check agains count($this)
		if ($count===NULL) {
			$count=0;
			}
		if ($start===NULL) {
			$start=0;
			}

		if ($start<0 || $count<0) {
			throw new \Nette\OutOfRangeException;
			}

		$this->result->limit($count, $start);
		return $this;
	}

	/**
	 * Get iterator over data source items
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return $this->result;
	}

	/**
	 * Fetches and returns the result data.
	 *
	 * @return array
	 */
	public function fetch()
	{
		throw $this->result->fetch();
	}

	/**
	 * Count items in data source
	 *
	 * @return int
	 */
	public function count()
	{
		$query=clone $this->result;
		$this->count=$query->count('*');

		return $this->count;
	}

	/**
	 * Return distinct values for a selectbox filter
	 *
	 * @param string $column Column name
	 * @return array
	 */
	public function getFilterItems($column)
	{
		$query=clone $this->result;
		return $query->select($column)->group($column)->fetchPairs($column, $column);
	}

	/**
	 * Clone instance
	 */
	public function __clone()
	{
		$this->result=clone $this->result;
	}
}

