<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\DataSources\PHPArray;
/**
 * @author Michael Moravec
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * An array data source
 */
class PHPArray
extends \Lohini\Database\DataSources\DataSource
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
	 * @param array $items
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $items)
	{
		if (empty($items)) {
			throw new \InvalidArgumentException('Empty array given');
			}
		$this->items=$this->source=$items;
	}

	public function filter($column, $operation=self::EQUAL, $value=NULL, $chainType=NULL)
	{
		throw new \NotImplementedException;
	}

	/**
	 * @param string $column
	 * @param string $order
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function sort($column, $order=self::ASCENDING)
	{
		if (!$this->hasColumn($column)) {
			throw new \InvalidArgumentException;
			}
		usort($this->items, function ($a, $b) use ($column, $order) {
				return $order===\Lohini\Database\DataSources\IDataSource::DESCENDING
						? -strcmp($a[$column], $b[$column])
						: strcmp($a[$column], $b[$column]);
				});
	}

	/**
	 * @param int $count
	 * @param int $start 
	 */
	public function reduce($count, $start=0)
	{
		$this->items=array_slice($this->items, $start, $count);
	}

	/**
	 * @return array 
	 */
	public function getColumns()
	{
		return array_keys(reset($this->source));
	}

	/**
	 * @param string $name
	 * @return bool 
	 */
	public function hasColumn($name)
	{
		return array_key_exists($name, reset($this->source));
	}

	public function getFilterItems($column)
	{
		throw new \NotImplementedException;
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}
}
