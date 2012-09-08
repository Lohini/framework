<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class SqlInsertQuery
extends \Nette\Object
implements \Countable
{
	/** @var array */
	private $values=array();
	/** @var string */
	private $table;
	/** @var \Doctrine\DBAL\Connection */
	private $connection;


	/**
	 * @param string $table
	 * @param \Doctrine\DBAL\Connection $connection
	 * @param array $values
	 */
	public function __construct($table, \Doctrine\DBAL\Connection $connection, array $values=array())
	{
		$this->table=$table;
		$this->connection=$connection;
		$this->values=$values;
	}

	/**
	 * @param array $row
	 */
	public function addRow($row)
	{
		$this->values[]=$row;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->values);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		try {
			$firstRowKeys=array_keys(reset($this->values));
			$sql="INSERT INTO `$this->table` (`".implode('`, `', $firstRowKeys).'`) VALUES ';
			foreach ($i=new \Nette\Iterators\CachingIterator($this->values) as $row) {
				$values=array_map(array($this->connection, 'quote'), $row);
				$sql.='('.implode(', ', $values).')';

				if (!$i->isLast()) {
					$sql.=', ';
					}
				}

			return $sql;
			}
		catch (\Exception $e) {
			\Nette\Diagnostics\Debugger::toStringException($e);
			}

		return NULL;
	}
}
