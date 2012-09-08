<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Audit;
/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Audit Manager grants access to metadata and configuration
 * and has a getter, similar to getRepository() on Entity Manager,
 * that returns Audit Reader for given class.
 */
class AuditManager
extends \Nette\Object
{
	/** @var \Lohini\Database\Doctrine\Audit\AuditConfiguration */
	private $config;
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	/** @var \Lohini\Database\Doctrine\Audit\ChangeLog */
	private $history;


	/**
	 * @param AuditConfiguration $config
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(AuditConfiguration $config, \Doctrine\ORM\EntityManager $em)
	{
		$this->config=$config;
		$this->em=$em;
	}

	/**
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadataFactory
	 */
	public function getMetadataFactory()
	{
		return $this->em->getMetadataFactory();
	}

	/**
	 * @return \Lohini\Database\Doctrine\Audit\AuditConfiguration
	 */
	public function getConfiguration()
	{
		return $this->config;
	}

	/**
	 * @param string $className
	 * @throws \Nette\NotImplementedException
	 */
	public function getAuditReader($className)
	{
		throw new \Nette\NotImplementedException;
	}

	/**
	 * @throws \Nette\NotImplementedException
	 */
	public function getChangeLog()
	{
		throw new \Nette\NotImplementedException;
	}
}
