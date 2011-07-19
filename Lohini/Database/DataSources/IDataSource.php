<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\DataSources;
/**
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * An interface which provides main data logic
 */
interface IDataSource
extends \Countable, \IteratorAggregate
{
	/** #@+ ordering types */
	const ASCENDING='ASC';
	const DESCENDING='DESC';
	/** #@- */

	/** #@+ filter operations */
	const EQUAL='=';
	const NOT_EQUAL='!=';
	const GREATER='>';
	const GREATER_OR_EQUAL='>=';
	const LESS='<';
	const LESS_OR_EQUAL='<=';
	const LIKE='LIKE';
	const NOT_LIKE='NOT LIKE';
	const IS_NULL='IS NULL';
	const IS_NOT_NULL='IS NOT NULL';
	/** #@- */

	/** #@+ filter chain types */
	const CHAIN_AND='AND';
	const CHAIN_OR='OR';
	/** #@- */


	/**
	 * Get list of columns available in datasource
	 * @return array
	 */
	function getColumns();
	/**
	 * Does datasource have column of given name?
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasColumn($name);
	/**
	 * Returns distinct values for a selectbox filter
	 * @param string $column name
	 * @return array
	 */
	function getFilterItems($column);
	/**
	 * Adds filtering onto specified column
	 * @param string $column name
	 * @param string $operation filter
	 * @param string|array $value operation mode
	 * @param string $chainType (if third argument is array)
	 * @return IDataSource
	 */
	function filter($column, $operation=IDataSource::EQUAL, $value=NULL, $chainType=NULL);
	/**
	 * Adds ordering to specified column
	 * @param string $column name
	 * @param string $order one of ordering types
	 * @return IDataSource
	 */
	function sort($column, $order=IDataSource::ASCENDING);
	/**
	 * Reduces the result starting from $start to have $count rows
	 * @param int $count the number of results to obtain
	 * @param int $start the offset
	 * @return IDataSource
	 */
	function reduce($count, $start=0);
}
