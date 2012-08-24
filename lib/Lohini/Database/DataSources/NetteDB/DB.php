<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\DataSources\NetteDB;
/**
 * @author Dušan Jakub, FIT VUT Brno
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\DataSources\IDataSource,
	Nette\Database\Table;

/**
 */
class DB
extends \Lohini\Database\DataSources\Mapped
{
    /** @var Table\Selection */
	private $selection;
	/** @var array fetched data */
//	private $data;
	/** @var int Total data count */
	private $count;


	/**
	 * Store given selection
	 *
	 * @param Table\Selection $sel
	 */
	public function __construct(Table\Selection $sel)
	{
		$this->selection=$sel;
	}

	/**
	 * @param array $mapping
	 */
	public function setMapping(array $mapping)
	{
		parent::setMapping($mapping);
		foreach ($mapping as $k => $m) {
			$this->selection->select("$m AS `$k`");
			}
	}

	/**
	 * Add filtering onto specified column
	 *
	 * @param string column name
	 * @param string filter
	 * @param string|array operation mode
	 * @param string chain type (if third argument is array)
	 * @return IDataSource (fluent)
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
			}
		else {
			$operation=array($operation);
			}

		if (empty($operation)) {
			throw new \Nette\InvalidArgumentException('Operation cannot be empty.');
			}

		$conds=array();
		$values=array();
		foreach ($operation as $o) {
			$this->validateFilterOperation($o);

			$c=$this->mapping[$column]." $o";
			if ($o!==self::IS_NULL && $o!==self::IS_NOT_NULL) {
				$c.=' ?';
				$values[]= ($o===self::LIKE || $o===self::NOT_LIKE)
						? DataSources\Utils\WildcardHelper::formatLikeStatementWildcards($value)
						: $value;
				}
			$conds[]=$c;
			}

		$conds=implode(" ( $chainType ) ", $conds); // "(cond1) OR (cond2) ..."  -- outer braces missing for now
		$this->selection->where("( $conds )", $values);

		return $this;
	}

	/**
	 * Adds ordering to specified column
	 *
	 * @param string column name
	 * @param string one of ordering types
	 * @return IDataSource (fluent)
	 * @throws \Nette\InvalidArgumentException
	 */
	public function sort($column, $order=IDataSource::ASCENDING)
	{
		if (!$this->hasColumn($column)) {
			throw new \Nette\InvalidArgumentException('Trying to sort data source by unknown column.');
			}

		$this->selection->order($this->mapping[$column].' '.($order===self::ASCENDING? 'ASC' : 'DESC'));

		return $this;
	}

	/**
	 * Reduce the result starting from $start to have $count rows
	 *
	 * @param int the number of results to obtain
	 * @param int the offset
	 * @return IDataSource (fluent)
	 * @throws \OutOfRangeException
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
			throw new \OutOfRangeException;
			}

		$this->selection->limit($count, $start);
		return $this;
	}

	/**
	 * Get iterator over data source items
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return $this->selection;
	}

	/**
	 * Fetches and returns the result data.
	 *
	 * @return array
	 */
	public function fetch()
	{
		throw $this->selection->fetch();
		//return $this->data = $this->df->fetchAll();
	}

	/**
	 * Count items in data source
	 *
	 * @return int
	 */
	public function count()
	{
		$query=clone $this->selection;
		$this->count=$query->count('*');

		return $this->count;
	}

	/**
	 * Return distinct values for a selectbox filter
	 *
	 * @param string $column name
	 * @return array
	 */
	public function getFilterItems($column)
	{
		throw new \Nette\NotImplementedException;
	}

	/**
	 * Clone selection instance
	 */
	public function __clone()
	{
		$this->selection=clone $this->selection;
	}
}
