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
 * @ORM\Table(name="db_packages", uniqueConstraints={
 *	@ORM\UniqueConstraint(columns={"name"})
 * })
 */
class PackageVersion
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	const STATUS_PRESENT='present';
	const STATUS_INSTALLED='installed';

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $className;
	/**
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var \Datetime
	 */
	private $migrationVersion;
	/**
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var \DateTime
	 */
	private $lastUpdate;
	/**
	 * @ORM\OneToMany(targetEntity="MigrationLog", mappedBy="package", cascade={"persist"})
	 * @var MigrationLog[]
	 */
	private $log;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $status=self::STATUS_PRESENT;


	/**
	 * @param \Lohini\Packages\Package $package
	 */
	public function __construct(\Lohini\Packages\Package $package)
	{
		$this->name=$package->getName();
		$this->className=get_class($package);
		$this->lastUpdate=new \DateTime;
		$this->log=new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return VersionDatetime
	 */
	public function getMigrationVersion()
	{
		return $this->migrationVersion? clone $this->migrationVersion : NULL;
	}

	/**
	 * @return \DateTime
	 */
	public function getLastUpdate()
	{
		return $this->lastUpdate;
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setStatus($status)
	{
		$constant='static::STATUS_' . strtoupper($status);
		if (!defined($constant)) {
			throw new \Nette\InvalidArgumentException("Invalid PackageVersion status '$status' was given.");
			}

		$this->status=constant($constant);
	}

	/**
	 * @return History
	 */
	public function createHistory()
	{
		return new History($this, $this->migrationVersion);
	}

	/**
	 * @param Version|NULL $version
	 * @throws MigrationException
	 */
	public function setVersion(Version $version=NULL)
	{
		if ($version===NULL) {
			$this->log[]=new MigrationLog($this, $version);
			$this->migrationVersion=NULL;
			$this->lastUpdate=new \DateTime();
			return;
			}

		if ($version->getVersion()==$this->migrationVersion) {
			return;
			}

		/** @var History $history */
		$history=$version->getHistory();
		if ($history->getPackage()!==$this) {
			$packageClass=$history->getPackage()->getClassName();
			throw new MigrationException(
				'Package of given version '.get_class($version)
				." is not '$this->className', '$packageClass' given."
				);
			}

		$this->log[]=new MigrationLog($this, $version);
		$this->migrationVersion=$version->getVersion();
		$this->lastUpdate=new \DateTime;
	}

	/**
	 * @return MigrationLog[]
	 */
	public function getMigrationsLog()
	{
		return $this->log->toArray();
	}
}
