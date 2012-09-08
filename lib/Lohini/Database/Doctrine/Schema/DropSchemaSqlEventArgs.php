<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Schema;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class DropSchemaSqlEventArgs
extends \Doctrine\Common\EventArgs
{
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	/** @var array|\Doctrine\ORM\Mapping\ClassMetadata[] */
	private $classes;
	/** @var array */
	private $sqls;


	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Doctrine\ORM\Mapping\ClassMetadata[] $classes
	 * @param array $sqls
	 */
	public function __construct(\Doctrine\ORM\EntityManager $entityManager, array $classes, array $sqls)
	{
		$this->em=$entityManager;
		$this->classes=$classes;
		$this->sqls=$sqls;
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
}
