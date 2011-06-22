<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\DataGrid\DataSources\Dibi;
/**
 * @author Pavel Kučera
 * @author Michael Moravec
 * @author Štěpán Svoboda
 * @author Petr Morávek
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */
use BailIff\Components\DataGrid\DataSources\IDataSource,
	BailIff\Components\DataGrid\DataSources\Utils\WildcardHelper;

/**
 * Dibi data source based data source
 */
class DataSource
extends \BailIff\Components\DataGrid\DataSources\DataSource
{
	/** @var \DibiDataSource Dibi data source instance */
	private $ds;
	/** @var array Fetched data */
	private $data;


	/**
	 * Stores given dibi data source instance
	 * @param \DibiDataSource
	 * @return \BailIff\Components\DataGrid\DataSources\IDataSource
	 */
	public function __construct(\DibiDataSource $ds)
	{
		$this->ds=$ds;
	}

	/**
	 * Gets list of columns available in datasource
	 * @return array
	 */
	public function getColumns()
	{
		$ds=clone $this->ds;
		return array_keys((array)$ds->select('*')->applyLimit(1)->fetch());
	}

	/**
	 * Does datasource have column of given name?
	 * @return bool
	 */
	public function hasColumn($name)
	{
		throw new \NotSupportedException;
	}

	/**
	 * Returns distinct values for a selectbox filter
	 * @param string $column name
	 * @return array
	 */
	public function getFilterItems($column)
	{
		$ds=clone $this->ds;
		return $ds->applyLimit(NULL)->toFluent()->removeClause('select')->select()->distinct($column)->fetchPairs($column, $column);
	}

	/**
	 * Adds filtering onto specified column
	 * @param string $column name
	 * @param string $operation filter
	 * @param string|array $value operation mode
	 * @param string $chainType (if third argument is array)
	 * @return \BailIff\Components\DataGrid\DataSources\IDataSource
	 * @throws \InvalidArgumentException
	 */
	public function filter($column, $operation=IDataSource::EQUAL, $value=NULL, $chainType=NULL)
	{
		if (is_array($operation)) {
			if ($chainType!==self::CHAIN_AND && $chainType!==self::CHAIN_OR) {
				throw new \InvalidArgumentException('Invalid chain operation type.');
				}
			$conds=array();
			foreach ($operation as $t) {
				$this->validateFilterOperation($t);
				if ($t===self::IS_NULL || $t===self::IS_NOT_NULL) {
					$conds[]=array('%n', $column, $t);
					}
				else {
					$modifier= is_double($value)? \dibi::FLOAT : \dibi::TEXT;
					if ($operation===self::LIKE || $operation===self::NOT_LIKE) {
						$value=WildcardHelper::formatLikeStatementWildcards($value);
						}
					$conds[]=array('%n', $column, $t, '%'.$modifier, $value);
					}
				}

			if ($chainType===self::CHAIN_AND) {
				foreach ($conds as $cond) {
					$this->ds->where($cond);
					}
				}
			elseif ($chainType===self::CHAIN_OR) {
				$this->ds->where('%or', $conds);
				}
			}
		else {
			$this->validateFilterOperation($operation);

			if ($operation===self::IS_NULL || $operation===self::IS_NOT_NULL) {
				$this->ds->where('%n', $column, $operation);
				}
			else {
				$modifier= is_double($value)? \dibi::FLOAT : \dibi::TEXT;
				if ($operation===self::LIKE || $operation===self::NOT_LIKE) {
					$value=WildcardHelper::formatLikeStatementWildcards($value);
					}
				$this->ds->where('%n', $column, $operation, '%' . $modifier, $value);
				}
			}
	}

	/**
	 * Adds ordering to specified column
	 * @param string $column name
	 * @param string $order one of ordering types
	 * @return \BailIff\Components\DataGrid\DataSources\IDataSource
	 */
	public function sort($column, $order=IDataSource::ASCENDING)
	{
		$this->ds->orderBy($column, $order===self::ASCENDING? 'ASC' : 'DESC');
		return $this;
	}

	/**
	 * Reduces the result starting from $start to have $count rows
	 * @param int $count the number of results to obtain
	 * @param int $start the offset
	 * @return \BailIff\Components\DataGrid\DataSources\IDataSource
	 * @throws \OutOfRangeException
	 */
	public function reduce($count, $start=0)
	{
		if ($count!=NULL && $count<=0) { //intentionally !=
			throw new \OutOfRangeException;
			}

		if ($start!=NULL && ($start<0 || $start>count($this))) {
			throw new \OutOfRangeException;
			}

		$this->ds->applyLimit($count==NULL? NULL : $count, $start==NULL? NULL : $start);
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
		return $this->data=$this->ds->fetchAll();
	}

	/**
	 * Counts items in data source
	 * @return int
	 */
	public function count()
	{
		return (int)$this->ds->count();
	}

	/**
	 * Clones dibi datasource instance
	 */
	public function __clone()
	{
		$this->ds=clone $this->ds;
	}
}
