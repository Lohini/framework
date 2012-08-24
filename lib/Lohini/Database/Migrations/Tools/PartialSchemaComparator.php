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

use Doctrine\ORM\EntityManager,
	Lohini\Database\Doctrine\Schema\SchemaTool,
	Nette\Utils\Strings;

/**
 */
class PartialSchemaComparator
extends \Nette\Object
{
	/** @var EntityManager */
	private $entityManager;
	/** @var \Doctrine\DBAL\Connection */
	private $connection;
	/** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
	private $platform;
	/** @var \Lohini\Database\Doctrine\Schema\SchemaTool */
	private $schemaTool;


	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager=$entityManager;
		$this->connection=$entityManager->getConnection();
		$this->platform=$this->connection->getDatabasePlatform();
		$this->schemaTool=new SchemaTool($this->entityManager);
	}

	/**
	 * @param \Lohini\Database\Doctrine\Mapping\ClassMetadata[] $metadata
	 * @return array
	 */
	public function compare(array $metadata)
	{
		/** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $sm */
		$sm=$this->connection->getSchemaManager();
		$fromSchema=$sm->createSchema();
		$toSchema=$this->schemaTool->getSchemaFromMetadata($this->getAllMetadata());

		$comparator=new \Doctrine\DBAL\Schema\Comparator;
		$schemaDiff=$comparator->compare($fromSchema, $toSchema);

		$allowedTables=$this->collectTables($metadata);

		foreach ($schemaDiff->newTables as $table => $tableDiff) {
			if (!in_array(Strings::lower($table), $allowedTables, TRUE)) {
				unset($schemaDiff->newTables[$table]);
				}
			}

		foreach ($schemaDiff->changedTables as $table => $tableDiff) {
			if (!in_array(Strings::lower($table), $allowedTables, TRUE)) {
				unset($schemaDiff->changedTables[$table]);
				}
			}

		foreach ($schemaDiff->removedTables as $table => $tableDiff) {
			if (!in_array(Strings::lower($table), $allowedTables, TRUE)) {
				unset($schemaDiff->removedTables[$table]);
				}
			}

		$sqls=$schemaDiff->toSql($this->platform);

		$evm=$this->entityManager->getEventManager();
		if ($evm->hasListeners(SchemaTool::onUpdateSchemaSql)) {
			$eventArgs=new \Lohini\Database\Doctrine\Schema\UpdateSchemaSqlEventArgs($this->entityManager, $metadata, $sqls, $toSchema);
			$evm->dispatchEvent(SchemaTool::onUpdateSchemaSql, $eventArgs);
			$sqls=$eventArgs->getSqls();
			}

		return $sqls;
	}

	/**
	 * @param \Lohini\Database\Doctrine\Mapping\ClassMetadata[] $metadata
	 * @return array
	 */
	protected function collectTables(array $metadata)
	{
		$tables=array();
		foreach ($metadata as $class) {
			$tables[]=Strings::lower($class->getTableName());
			foreach ($class->getAssociationMappings() as $assoc) {
				if (isset($assoc['joinTable']['name'])) {
					$tables[]=Strings::lower($assoc['joinTable']['name']);
					}
				}

			if ($class->isAudited()) {
				$tables[]=Strings::lower($class->getTableName()).'_audit'; // TODO: from configurator
				}
			}

		return array_unique($tables);
	}

	/**
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata[]
	 */
	protected function getAllMetadata()
	{
		return $this->entityManager->getMetadataFactory()->getAllMetadata();
	}

	/**
	 * @param EntityManager $em
	 * @param \Lohini\Packages\Package $package
	 * @param array $entities
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata[]
	 */
	public static function collectPackageMetadata(EntityManager $em, \Lohini\Packages\Package $package, array $entities=array())
	{
		$metadata=array();
		if ($entities) {
			$ns=$package->getNamespace().'\\Entity';
			foreach ($entities as $entity) {
				if ($entity[0]!=='\\') { // absolute
					$entity=$ns.'\\'.$entity;
					}

				$metadata[]=$class=$em->getClassMetadata($entity);
				foreach ($class->discriminatorMap as $className) {
					$metadata[]=$em->getClassMetadata($className);
					}
				}

			return array_unique($metadata);
			}

		foreach ($em->getMetadataFactory()->getAllMetadata() as $class) {
			foreach ($package->getEntityNamespaces() as $namespace) {
				if (strpos($class->getName(), $namespace)===0) {
					$metadata[]=$class;
					break;
					}
				}
			}

		return $metadata;
	}
}
