<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Audit\Listener;
/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\DBAL\Schema,
	Doctrine\DBAL\Platforms,
	Lohini\Database\Doctrine\Audit\AuditConfiguration,
	Lohini\Database\Doctrine\Schema\SchemaTool;

/**
 */
class CreateSchemaListener
extends \Nette\Object
implements \Doctrine\Common\EventSubscriber
{
	/** @var AuditConfiguration */
	private $config;
	/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadataFactory */
	private $metadataFactory;
	/** @var \Doctrine\Common\Annotations\Reader */
	private $reader;


	/**
	 * @param \Lohini\Database\Doctrine\Audit\AuditManager $auditManager
	 * @param \Doctrine\Common\Annotations\Reader $reader
	 */
	public function __construct(\Lohini\Database\Doctrine\Audit\AuditManager $auditManager, \Doctrine\Common\Annotations\Reader $reader)
	{
		$this->config=$auditManager->getConfiguration();
		$this->metadataFactory=$auditManager->getMetadataFactory();
		$this->reader=$reader;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			\Doctrine\ORM\Tools\ToolEvents::postGenerateSchemaTable,
			\Doctrine\ORM\Events::loadClassMetadata,
			SchemaTool::onCreateSchemaSql,
			SchemaTool::onUpdateSchemaSql,
			SchemaTool::onDropSchemaSql,
			);
	}

	/**
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(\Doctrine\ORM\Event\LoadClassMetadataEventArgs $args)
	{
		/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadata $meta */
		$meta=$args->getClassMetadata();
		if ($meta->rootEntityName && $meta->rootEntityName!==$meta->name) {
			$meta=$args->getEntityManager()->getClassMetadata($meta->rootEntityName);
			}

		/** @var \Lohini\Database\Doctrine\Audit\AuditedEntity $audited */
		$classRefl=\Nette\Reflection\ClassType::from($meta->name);
		$audited=$this->reader->getClassAnnotation($classRefl, 'Lohini\Database\Doctrine\Audit\AuditedEntity');

		if ($audited) {
			$meta->setAudited((bool)$audited);
			$meta->auditRelations=array_merge((array)$meta->auditRelations, (array)$audited->related);
			}
	}

	/**
	 * @param \Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs $eventArgs
	 * @throws \Nette\NotImplementedException
	 */
	public function postGenerateSchemaTable(\Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs $eventArgs)
	{
		/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadata $class */
		$class=$eventArgs->getClassMetadata();
		if (!$this->metadataFactory->isAudited($class->name)) {
			return;
			}

		if ($class->auditRelations) {
			throw new \Nette\NotImplementedException('Sorry bro.');
			}

		$schema=$eventArgs->getSchema();
		if (!$schema->hasTable($revisionTableName=$this->getClassAuditTableName($class))) {
			$this->doCreateRevisionTable($eventArgs->getClassTable(), $schema, $revisionTableName);
			}
	}

	/**
	 * @param Schema\Table $entityTable
	 * @param Schema\Schema $schema
	 * @param string $revisionTableName
	 */
	private function doCreateRevisionTable(Schema\Table $entityTable, Schema\Schema $schema, $revisionTableName)
	{
		$historyTable=new Schema\Table(
			'db_audit_revisions',
			array(new Schema\Column('id', \Lohini\Database\Doctrine\Type::getType('integer')))
			);

		$revisionTable=$schema->createTable($revisionTableName);
		foreach ($entityTable->getColumns() AS $column) {
			/* @var $column \Doctrine\DBAL\Schema\Column */
			$revisionTable->addColumn(
				$column->getName(),
				$column->getType()->getName(),
				array_merge(
					$column->toArray(),
					array('notnull' => FALSE, 'autoincrement' => FALSE)
					)
				);
			}

		// revision id
		$revisionTable->addColumn(AuditConfiguration::REVISION_ID, 'bigint', array('notnull' => TRUE));
		$revisionTable->addColumn(AuditConfiguration::REVISION_PREVIOUS, 'bigint', array('notnull' => FALSE));

		// primary
		$pkColumns=$entityTable->getPrimaryKey()->getColumns();
		$pkColumns[]=AuditConfiguration::REVISION_ID;
		$revisionTable->setPrimaryKey($pkColumns);

		// revision fk
		$revisionTable->addForeignKeyConstraint( // todo: config/constants
			$historyTable,
			array(AuditConfiguration::REVISION_ID),
			array('id')
			);

		// previous revision index & fk
		$revisionTable->addIndex(array(AuditConfiguration::REVISION_ID), 'idx_rev_id');
		$revisionTable->addIndex(array(AuditConfiguration::REVISION_PREVIOUS), 'idx_previous_rev');
		$revisionTable->addForeignKeyConstraint(
			$revisionTable,
			array(AuditConfiguration::REVISION_PREVIOUS),
			array(AuditConfiguration::REVISION_ID)
			);
	}

	/**
	 * @param \Lohini\Database\Doctrine\Schema\CreateSchemaSqlEventArgs $args
	 */
	public function onCreateSchemaSql(\Lohini\Database\Doctrine\Schema\CreateSchemaSqlEventArgs $args)
	{
		$args->addSqls($this->generateTriggers(
			$args->getEntityManager(),
			$args->getClasses(),
			$args->getTargetSchema()
			));
	}

	/**
	 * @param \Lohini\Database\Doctrine\Schema\UpdateSchemaSqlEventArgs $args
	 */
	public function onUpdateSchemaSql(\Lohini\Database\Doctrine\Schema\UpdateSchemaSqlEventArgs $args)
	{
		$args->addSqls($this->generateTriggers(
			$args->getEntityManager(),
			$args->getClasses(),
			$args->getTargetSchema()
			));
	}

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param array $classes
	 * @param Schema\Schema $targetSchema
	 * @return array
	 */
	private function generateTriggers(\Doctrine\ORM\EntityManager $em, array $classes, Schema\Schema $targetSchema)
	{
		$connection=$em->getConnection();
		$platform=$connection->getDatabasePlatform();
		if (!$platform instanceof Platforms\MySqlPlatform) {
			return array();
			}

		$sqls=array();
		foreach ($classes as $class) {
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
				}

			$generator=new \Lohini\Database\Doctrine\Audit\TriggersGenerator\MysqlTriggersGenerator($em, $this->config);
			foreach ($generator->generate($class, $targetSchema) as $trigger) {
				$sqls[]=$trigger->getDropSql();
				$sqls[]=(string)$trigger;
				}
			}

		return $sqls;
	}

	/**
	 * @param \Lohini\Database\Doctrine\Schema\DropSchemaSqlEventArgs $args
	 */
	public function onDropSchemaSql(\Lohini\Database\Doctrine\Schema\DropSchemaSqlEventArgs $args)
	{
		$platform=$args->getEntityManager()->getConnection()->getDatabasePlatform();
		if (!$platform instanceof Platforms\MySqlPlatform) {
			return;
			}

		$sqls=array();
		foreach ($args->getClasses() as $class) {
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
				}

			//$sqls[]='DROP TRIGGER IF EXISTS ' . $prefix . '_';
			}

		$args->addSqls($sqls);
	}

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $class
	 * @return string
	 */
	private function getClassAuditTableName(\Doctrine\ORM\Mapping\ClassMetadata $class)
	{
		return $this->config->prefix.$class->getTableName().$this->config->suffix;
	}
}
