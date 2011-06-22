<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\DataGrid\DataSources\Dibi;

/**
 * An dibi data source for DataGrid
 * @author Lopo <lopo@losys.eu>
 */
class Dibi
extends \BailIff\Components\DataGrid\DataSources\DataSource
{
	/** @var array */
	private $items;
	/** @var array */
	private $source;
	/** @var array */
	private $filters;
	/** @var array */
	private $sorting;
	/** @var array */
	private $reducing;

	/**
	 * @param \DibiDataSource $dds 
	 */
	public function __construct(\DibiDataSource $dds)
	{
		$this->source=$dds;
	}

	/**
	 * @param string $column
	 * @param string $operation
	 * @param mixed $value
	 * @param string $chainType
	 * @return \BailIff\Components\DataGrid\DataSources\IDataSource
	 */
	public function filter($column, $operation=self::EQUAL, $value=NULL, $chainType=NULL)
	{
		$s=clone $this->source;
		return $s->where("$column $operation $value");
	}

	/**
	 * @param string $column
	 * @param string $order
	 * @return \BailIff\Components\DataGrid\DataSources\IDataSource
	 * @throws \InvalidArgumentException
	 */
	public function sort($column, $order=self::ASCENDING)
	{
		if (!$this->hasColumn($column)) {
			throw new \InvalidArgumentException;
			}
		$s=clone $this->source;
		return $s->orderBy($column, $order);
	}

	/**
	 * @param int $count
	 * @param int $start 
	 */
	public function reduce($count, $start=0)
	{
		$s=clone $this->source;
		$s->applyLimit($count, $start);
	}

	/**
	 * @return array
	 */
	public function getColumns()
	{
		$s=clone $this->source;
		$row=$s->select('*')->applyLimit(1)->fetch();
		return array_keys((array)$row);
	}

	/**
	 * @param string $name
	 * @return bool 
	 */
	public function hasColumn($name)
	{
		return in_array($name, $this->getColumns());
	}

	public function getFilterItems($column)
	{
		throw new \NotImplementedException;
	}

	/**
	 * @return \Traversable
	 */
	public function getIterator()
	{
		$s=clone $this->source;
		return $s->getIterator();
	}

	/**
	 * @return int
	 */
	public function count()
	{
		$s=clone $this->source;
		return (int)$s->count();
	}
}
