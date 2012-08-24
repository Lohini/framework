<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Schema;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class CreateSchemaSqlEventArgs
extends \Doctrine\Common\EventArgs
{
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	/** @var array|\Doctrine\ORM\Mapping\ClassMetadata[] */
	private $classes;
	/** @var array */
	private $sqls;
	/** @var \Doctrine\DBAL\Schema\Schema */
	private $targetSchema;


	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Doctrine\ORM\Mapping\ClassMetadata[] $classes
	 * @param array $sqls
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 */
	public function __construct(\Doctrine\ORM\EntityManager $entityManager, array $classes, array $sqls, \Doctrine\DBAL\Schema\Schema $targetSchema=NULL)
	{
		$this->em=$entityManager;
		$this->classes=$classes;
		$this->sqls=$sqls;
		$this->targetSchema=$targetSchema;
	}

	/**
	 * @return array|\Doctrine\ORM\Mapping\ClassMetadata[]
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->em;
	}

	/**
	 * @param array $sqls
	 */
	public function addSqls(array $sqls)
	{
		$this->sqls=array_merge(
			$this->sqls,
			array_map(
				function($sql) { return (string)$sql; },
				$sqls
				)
			);
	}

	/**
	 * @return array
	 */
	public function getSqls()
	{
		return $this->sqls;
	}

	/**
	 * @return \Doctrine\DBAL\Schema\Schema
	 */
	public function getTargetSchema()
	{
		return $this->targetSchema;
	}
}
