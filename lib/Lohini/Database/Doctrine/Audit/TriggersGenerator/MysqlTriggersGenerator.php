<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Audit\TriggersGenerator;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\Schema\Trigger;

/**
 */
class MysqlTriggersGenerator
extends \Nette\Object
{
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	/** @var \Lohini\Database\Doctrine\Audit\AuditConfiguration */
	private $config;
	/** @var \Doctrine\DBAL\Connection */
	private $conn;
	/** @var \Doctrine\DBAL\Platforms\MySqlPlatform */
	private $platform;


	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Lohini\Database\Doctrine\Audit\AuditConfiguration $config
	 */
	public function __construct(\Doctrine\ORM\EntityManager $entityManager, \Lohini\Database\Doctrine\Audit\AuditConfiguration $config)
	{
		$this->em=$entityManager;
		$this->config=$config;
		$this->conn=$entityManager->getConnection();
		$this->platform=$this->conn->getDatabasePlatform();
	}

	/**
	 * @param \Lohini\Database\Doctrine\Mapping\ClassMetadata $class
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 * @return Trigger[]
	 * @throws \Nette\NotImplementedException
	 */
	public function generate(\Lohini\Database\Doctrine\Mapping\ClassMetadata $class, \Doctrine\DBAL\Schema\Schema $targetSchema)
	{
		if ($class->auditRelations) {
			throw new \Nette\NotImplementedException('Sorry bro.');
			}

		$triggers=array();
		$entityTable=$class->getTableName();
		$auditTable=$this->config->prefix . $class->getTableName() . $this->config->suffix;
		$idCol=$class->getSingleIdentifierColumnName();

		// before insert
		$triggers[]= $ai= Trigger::afterInsert($class->getTableName(), 'audit')
			->declare('audit_revision', 'BIGINT')
			->insert(
				'db_audit_revisions',
				array(
					'type' => 'INS',
					'className' => $class->name,
					'entityId%sql' => "NEW.`$idCol`",
					'createdAt%sql' => 'NOW()',
					'author%sql' => '@lohini_current_user',
					'comment%sql' => '@lohini_action_comment'
					)
				)
			->set('audit_revision', 'LAST_INSERT_ID()')
			->insertSelect(
				$auditTable,
				$targetSchema->getTable($entityTable),
				array(
					'values' => array('_revision%sql' => '@audit_revision'),
					'where' => "`$idCol` = NEW.`$idCol`"
					)
				);

		$versionUpdate=function(Trigger $trigger, $action) use ($class, $idCol, $targetSchema, $auditTable, $entityTable) {
			/** @var Schema $targetSchema */
			return $trigger->declare('audit_revision', 'BIGINT')
				->insert(
					'db_audit_revisions',
					array(
						'type' => $action,
						'className' => $class->name,
						'entityId%sql' => "OLD.`$idCol`",
						'createdAt%sql' => 'NOW()',
						'author%sql' => '@lohini_current_user',
						'comment%sql' => '@lohini_action_comment'
						)
					)
				->set('audit_revision', 'LAST_INSERT_ID()')
				->set('audit_revision_previous', '(SELECT MAX(_revision) FROM `'."$auditTable` WHERE `$idCol` = OLD.`$idCol`)")
				->insertSelect(
					$auditTable,
					$targetSchema->getTable($entityTable),
					array(
						'values' => array(
							'_revision%sql' => '@audit_revision',
							'_revision_previous%sql' => '@audit_revision_previous'
							),
						'where' => "`$idCol` = OLD.`$idCol`"
						)
					);
			};

		// before update
		$triggers[]= $bu= $versionUpdate(Trigger::beforeUpdate($class->getTableName(), 'audit'), 'UPD');

		// before delete
		$triggers[]= $bd= $versionUpdate(Trigger::beforeDelete($class->getTableName(), 'audit'), 'DEL');

		return $triggers;
	}
}
