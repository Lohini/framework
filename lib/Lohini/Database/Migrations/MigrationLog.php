<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="db_migration_log")
 */
class MigrationLog
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	/**
	 * @ORM\ManyToOne(targetEntity="PackageVersion", inversedBy="log")
	 * @var PackageVersion
	 */
	private $package;
	/**
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var \Datetime
	 */
	private $version;
	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	private $date;
	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	private $up;


	/**
	 * @param PackageVersion $package
	 * @param Version $version
	 */
	public function __construct(PackageVersion $package, Version $version=NULL)
	{
		$this->package=$package;
		$this->version= $version? $version->getVersion() : NULL;
		$this->date=new \DateTime;
		$this->up= $version!==NULL && $package->getMigrationVersion()<$version->getVersion();
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date
			? clone $this->date
			: NULL;
	}

	/**
	 * @return PackageVersion
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * @return \DateTime
	 */
	public function getVersion()
	{
		return $this->version
			? clone $this->version
			: NULL;
	}

	/**
	 * @return bool
	 */
	public function isUp()
	{
		return $this->up;
	}
}
