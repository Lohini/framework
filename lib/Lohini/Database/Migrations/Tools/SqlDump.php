<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\Tools;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class SqlDump
extends \Nette\Object
implements \Iterator
{
	/** @var string */
	private $file;
	/** @var resource */
	private $resource;
	/** @var string */
	private $currentSql;
	/** @var array */
	private $sqls=array();
	/** @var int */
	private $count=0;


	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->file=$file;
		@set_time_limit(0); // intentionally @
	}

	/**
	 * @return array
	 */
	public function getSqls()
	{
		if ($this->sqls) {
			return $this->sqls;
			}

		foreach ($this as $sql) {
			$this->sqls[]=$sql;
			}
		return $this->sqls;
	}

	/**
	 * @return string
	 */
	private function fetchOne()
	{
		$this->currentSql=$sql=NULL;
		while (!feof($this->resource())) {
			if (substr($s=fgets($this->resource()), 0, 2)==='--') {
				continue;
				}

			$sql.=$s;
			if (substr(rtrim($s), -1)===';') {
				$this->currentSql=trim($sql);
				$this->count++;
				break;
				}
			}

		if (!$this->currentSql) {
			@fclose($this->resource);
			}

		return $this->currentSql;
	}

	/**
	 * @return resource
	 * @throws \Lohini\FileNotFoundException
	 */
	private function resource()
	{
		if ($this->resource !== NULL) {
			return $this->resource;
			}

		$this->resource=@fopen($this->file, 'r'); // intentionally @
		if (!$this->resource) {
			throw new \Lohini\FileNotFoundException("Cannot open file '$this->file'.");
			}

		return $this->resource;
	}

	/**
	 * Closes the file.
	 */
	public function __destruct()
	{
		@fclose($this->resource);
	}

	/****************** \Iterator ******************/
	/**
	 * Rewinds to the beginning of the file
	 */
	public function rewind()
	{
		@fseek($this->resource(), 0);
		$this->fetchOne();
	}

	/**
	 * @return string
	 */
	public function current()
	{
		return $this->currentSql;
	}

	/**
	 * @return int
	 */
	public function key()
	{
		return $this->count-1;
	}

	/**
	 * @return string
	 */
	public function next()
	{
		return $this->fetchOne();
	}

	/**
	 * @return boolean
	 */
	public function valid()
	{
		return (bool)$this->currentSql;
	}
}
