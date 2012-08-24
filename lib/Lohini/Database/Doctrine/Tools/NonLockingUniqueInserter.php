<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Tools;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 */
class NonLockingUniqueInserter
extends \Nette\Object
{
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	/** @var \Doctrine\DBAL\Connection */
	private $connection;
	/** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
	private $platform;


	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(\Doctrine\ORM\EntityManager $em)
	{
		$this->em=$em;
		$this->connection=$em->getConnection();
		$this->platform=$this->connection->getDatabasePlatform();
	}

	/**
	 * When entity have columns for required associations, this will fail.
	 * Calls $em->flush().
	 *
	 * @todo fix error codes! PDO is returning database-specific codes
	 * @param object $entity
	 */
	public function persist($entity)
	{
		$this->connection->beginTransaction();

		try {
			$this->doInsert($entity);
			$this->connection->commit();
			return TRUE;
			}
		catch (\PDOException $e) {
			$this->connection->rollback();

			if ($e->getCode()==23000) { // unique fail
				return FALSE;
				}
			// other fail
			throw $e;
			}
		catch (\Exception $e) {
			$this->connection->rollback();
			throw $e;
			}
	}

	/**
	 * @param object $entity
	 */
	private function doInsert($entity)
	{
		// get entity metadata
		$meta=$this->em->getClassMetadata(get_class($entity));

		// fields that have to be inserted
		$fields=$this->getUniqueAndRequiredFields($meta);

		// read values to insert
		$values=$this->getInsertValues($meta, $entity, $fields);

		// prepare statement && execute
		$this->prepareInsert($meta, $values)->execute();

		// assign ID to entity
		if ($idGen=$meta->idGenerator) {
			if ($idGen->isPostInsertGenerator()) {
				$id=$idGen->generate($this->em, $entity);
				$identifierFields=$meta->getIdentifierFieldNames();
				$meta->setFieldValue($entity, reset($identifierFields), $id);
				}
			}

		// entity is now safely inserted to database, merge now
		$this->em->merge($entity);
		$this->em->flush();
	}

	/**
	 * @param ClassMetadata $meta
	 * @param array $values
	 * @param array $types
	 * @return \Doctrine\DBAL\Driver\Statement
	 */
	private function prepareInsert(ClassMetadata $meta, array $values)
	{
		// construct sql
		$columns=array_map(callback($meta, 'getColumnName'), array_keys($values));
		$insertSql='INSERT INTO '.$meta->getQuotedTableName($this->platform)
			.' ('.implode(', ', $columns).')'
			.' VALUES ('.implode(', ', array_fill(0, count($columns), '?')).')';

		// create statement
		$statement=$this->connection->prepare($insertSql);

		// fetch column types
		$types=$this->getColumnsTypes($meta, array_keys($values));

		// bind values
		$paramIndex=1;
		foreach ($values as $field => $value) {
			$statement->bindValue($paramIndex++, $value, $types[$field]);
			}

		return $statement;
	}

	/**
	 * @param ClassMetadata $meta
	 * @return array
	 */
	private function getUniqueAndRequiredFields(ClassMetadata $meta)
	{
		$fields=array();
		foreach ($meta->getFieldNames() as $fieldName) {
			$mapping=$meta->getFieldMapping($fieldName);
			if (!empty($mapping['id'])) { // not an id
				continue;
				}

			if (empty($mapping['nullable'])) { // is not nullable
				$fields[]=$fieldName;
				continue;
				}

			if (!empty($mapping['unique'])) { // is unique
				$fields[]=$fieldName;
				continue;
				}
			}
		return $fields;
	}

	/**
	 * @param ClassMetadata $meta
	 * @param object $entity
	 * @param array $fields
	 * @return array
	 */
	private function getInsertValues(ClassMetadata $meta, $entity, array $fields)
	{
		$values=array();
		foreach ($fields as $fieldName) {
			$values[$fieldName]=$meta->getFieldValue($entity, $fieldName);
			}
		return $values;
	}

	/**
	 * @param ClassMetadata $meta
	 * @param array $fields
	 * @return array
	 */
	private function getColumnsTypes(ClassMetadata $meta, array $fields)
	{
		$columnTypes=array();
		foreach ($fields as $fieldName) {
			$columnTypes[$fieldName]=\Doctrine\DBAL\Types\Type::getType($meta->fieldMappings[$fieldName]['type']);
			}
		return $columnTypes;
	}
}
