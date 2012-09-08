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
class PackageMigration
extends \Nette\Object
{
	/** @var array */
	private static $formats=array(
		'YmdHis',
		'Y-m-d H:i:s',
		'Y-m-d H:i',
		'Y-m-d H',
		'Y-m-d',
		);
	/** @var \Lohini\Database\Migrations\MigrationsManager */
	private $migrationsManager;
	/** @var \Lohini\Packages\Package */
	private $package;
	/** * @var \Lohini\Database\Migrations\History */
	private $history;


	/**
	 * @param \Lohini\Database\Migrations\MigrationsManager $migrationsManager
	 * @param \Lohini\Packages\Package $package
	 */
	public function __construct(\Lohini\Database\Migrations\MigrationsManager $migrationsManager, \Lohini\Packages\Package $package)
	{
		$this->migrationsManager=$migrationsManager;
		$this->package=$package;
		$this->history=$migrationsManager->getPackageHistory($package->getName());
	}

	/**
	 * @param $targetVersion
	 * @param bool $force
	 * @throws \Lohini\Database\Migrations\MigrationException
	 */
	public function run($targetVersion, $force=FALSE)
	{
		$packageName=$this->package->getName();

		if (in_array($targetVersion, array('up', 'apply'), TRUE)) {
			if ($nextVersion=$this->history->getNext()) {
				$targetVersion=$nextVersion->getVersion();
				}
			else {
				throw new \Lohini\Database\Migrations\MigrationException("Next version for <comment>$packageName</comment> not found");
				}
			}
		elseif (in_array($targetVersion, array('down', 'revert'), TRUE)) {
			$targetVersion= $this->history->getCurrent()
				? (($prevVersion=$this->history->getPrevious())? $prevVersion->getVersion() : 0)
				: $targetVersion=0;
			}
		elseif (strlen((string)$targetVersion)>10 && ($date=\Lohini\Utils\DateTime::tryFormats(static::$formats, $targetVersion))) {
			$targetVersion=$date->format('YmdHis');
			}

		if (!$this->history->getFirst()) { // no migrations
			if ($targetVersion !== 0) {
				$schema=new CreatePackageSchema($this->migrationsManager->getEntityManager(), $this->package);
				$schema->setOutputWriter($this->migrationsManager->getOutputWriter());
				$schema->create($force);
				}
			else {
				$schema=new DropPackageSchema($this->migrationsManager->getEntityManager(), $this->package);
				$schema->setOutputWriter($this->migrationsManager->getOutputWriter());
				$schema->create($force);
				}

			return;
			}

		if ($this->isUpToDate($targetVersion)) {
			throw new \Lohini\Database\Migrations\MigrationException("Package <comment>$packageName</comment> is up to date.");
			}

		$this->history->migrate($this->migrationsManager, $targetVersion, $force);
	}

	/**
	 * @param string $targetVersion
	 * @return bool
	 */
	private function isUpToDate($targetVersion)
	{
		return ($this->history->isUpToDate()
			&& (!($curr=$this->history->getCurrent()) || $targetVersion >= $curr->getVersion()))
			|| $this->history->getPackage()->getMigrationVersion() === $targetVersion;
	}
}
