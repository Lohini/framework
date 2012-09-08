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
class TableDumper
extends \Nette\Object
implements \Iterator
{
	const ROWS_COUNT=3;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;
	/** @var \Doctrine\DBAL\Connection */
	private $connection;
	/** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
	private $platform;
	/** @var \Lohini\Database\Doctrine\Schema\SchemaTool */
	private $schemaTool;
	/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadata[] */
	private $metadata;
	/** @var array */
	private $tablesLeft;
	/** @var resource|\Doctrine\DBAL\Driver\Statement */
	private $tableData;
	/** @var string */
	private $current;
	/** @var int */
	private $rows=0;


	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Lohini\Database\Doctrine\Mapping\ClassMetadata[] $metadata
	 */
	public function __construct(\Doctrine\ORM\EntityManager $entityManager, array $metadata)
	{
		$this->entityManager=$entityManager;
		$this->connection=$entityManager->getConnection();
		$this->platform=$this->connection->getDatabasePlatform();
		$this->schemaTool=new \Lohini\Database\Doctrine\Schema\SchemaTool($this->entityManager);

		$this->metadata=$metadata;
	}

	/**
	 * @param \Lohini\Database\Doctrine\Mapping\ClassMetadata[] $metadata
	 * @return array
	 */
	protected function collectTables(array $metadata)
	{
		$tables=array();
		foreach ($metadata as $class) {
			$tables[]=$class->getTableName();
			foreach ($class->getAssociationMappings() as $assoc) {
				if (isset($assoc['joinTable'])) {
					$tables[]=$assoc['joinTable']['name'];
					}
				}
			}

		return array_unique($tables);
	}

	/**
	 * @return string
	 */
	protected function fetchOne()
	{
		if (!$this->tablesLeft) {
			return $this->current=NULL;
			}

		$table=reset($this->tablesLeft);
		if (!$this->tableData) {
			$this->tableData=$this->connection->prepare("SELECT * FROM `$table`");
			$this->tableData->execute();
			}

		$insert=new SqlInsertQuery($table, $this->connection);
		while ($row=$this->tableData->fetch(\PDO::FETCH_ASSOC)) {
			$insert->addRow($row);
			if (count($insert)===static::ROWS_COUNT) {
				break;
				}
			}

		if (!$row) {
			$this->tableData=NULL; // no more results
			array_shift($this->tablesLeft);
			}

		$this->rows++;
		return $this->current=(string)$insert;
	}

	/**
	 */
	public function rewind()
	{
		$this->tablesLeft=$this->collectTables($this->metadata);
		$this->tableData=NULL;
		$this->rows=0;
		$this->fetchOne();
	}

	/**
	 * @returns string
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 */
	public function next()
	{
		return $this->fetchOne();
	}

	/**
	 */
	public function key()
	{
		return $this->rows;
	}

	/**
	 */
	public function valid()
	{
		return (bool)$this->current;
	}
}
