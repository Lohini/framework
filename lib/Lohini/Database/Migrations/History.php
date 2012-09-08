<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class History
extends \Nette\Object
implements \IteratorAggregate
{
	/** @var \Lohini\Utils\DoubleLinkedArray|Version[] */
	private $versions;
	/** @var string */
	private $current;
	/** @var PackageVersion */
	private $package;


	/**
	 * @param PackageVersion $package
	 * @param string $current
	 */
	public function __construct(PackageVersion $package, $current)
	{
		$this->versions=new \Lohini\Utils\DoubleLinkedArray;
		$this->package=$package;
		$this->current=VersionDatetime::from($current);
	}

	/**
	 * @return PackageVersion
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * @param Version|NULL $version
	 */
	public function setCurrent(Version $version=NULL)
	{
		$this->package->setVersion($version);
		$this->current=$this->package->getMigrationVersion();
	}

	/**
	 * @return Version|NULL
	 */
	public function getCurrent()
	{
		if ($this->current) {
			foreach ($this->versions as $version) {
				if ($version->getVersion()==$this->current) {
					return $version;
					}
				}
			}

		return NULL;
	}

	/**
	 * @param MigrationsManager $manager
	 * @param int $target
	 * @param bool $commit
	 * @return array
	 * @throws MigrationException
	 */
	public function migrate(MigrationsManager $manager, $target, $commit=TRUE)
	{
		$writer=$manager->getOutputWriter();

		// without registered migrations, there is no job to be done
		if (!$this->versions) {
			return array();
			}

		$sqls=array();
		try {
			$writer->writeln('');
			$result= ($this->current<=($closest=$this->calculateClosestVersion($target)))
				? $this->migrateUp($manager, $closest, $commit)
				: $this->migrateDown($manager, $closest, $commit);

			if (!$result) {
				$writer->writeln('');
				$writer->writeln('Nothing to be done.');
				return array();
				}

			list($totalTime, $totalSqls, $sqls)=$result;
			$writer->writeln('    <comment>------------------------</comment>');
			$writer->writeln('    <info>II</info> package migration finished in '.number_format($totalTime, 2, '.', ' ').' s');
			$writer->writeln('    <info>II</info> '.count($sqls).' migrations executed');
			$writer->writeln('    <info>II</info> '.$totalSqls.' sql queries');
			}
		catch (\Exception $exception) { }

		if ($commit) {
			$manager->savePackage($this->package);
			}

		if (isset($exception)) {
			throw $exception;
			}

		return $sqls;
	}

	/**
	 * @param MigrationsManager $manager
	 * @param string $target
	 * @param bool $commit
	 */
	private function migrateUp(MigrationsManager $manager, $target, $commit)
	{
		if ($this->isUpToDate()) {
			return NULL;
			}

		$writer=$manager->getOutputWriter();
		$packageName=$this->package->getName();

		$msg='    Migrating <comment>'.$packageName.'</comment> to <comment>'.$target.'</comment>';
		if (!$this->current) {
			$writer->writeln($msg);
			}
		else {
			$writer->writeln($msg.' from <comment>'.$this->getCurrent()->getVersion().'</comment>');
			}

		$sqls=array();
		$totalTime= $totalSqls= 0;
		$current= $this->getCurrent() ?: $this->getFirst();
		do {
			if ($current->getVersion()>$target) {
				break;
				}

			$sqls[(string)$current->getVersion()]= $lastSqls= $current->up($manager, $commit);

			$totalTime+=$current->getTime();
			$totalSqls+= is_array($lastSqls)? count($lastSqls) : (int)$lastSqls;
			} while ($current=$current->getNext());

		return array($totalTime, $totalSqls, $sqls);
	}

	/**
	 * @param MigrationsManager $manager
	 * @param string $target
	 * @param bool $commit
	 * @throws \Nette\InvalidStateException
	 */
	private function migrateDown(MigrationsManager $manager, $target, $commit)
	{
		if (!$this->current) {
			return NULL;
			}

		$packageName=$this->package->getName();
		$writer=$manager->getOutputWriter();
		if (!$current=$this->getCurrent()) {
			throw new \Nette\InvalidStateException('There is no current version.');
			}
		$writer->writeln('    Reverting <comment>'.$packageName.'</comment> to <comment>'.$target.'</comment> from <comment>'.$current->getVersion().'</comment>');

		$sqls=array();
		$totalTime= $totalSqls= 0;
		do {
			/** @var Version $prev */
			if ($current->getVersion()==$target) {
				break;
				}

			$sqls[(string)$current->getVersion()]= $lastSqls= $current->down($manager, $commit);

			$totalTime+=$current->getTime();
			$totalSqls+= is_array($lastSqls)? count($lastSqls) : (int)$lastSqls;
			} while ($current=$current->getPrevious());

		return array($totalTime, $totalSqls, $sqls);
	}

	/**
	 * Ensures the target is in range
	 *
	 * @param int $target
	 * @return VersionDatetime
	 */
	private function calculateClosestVersion($target)
	{
		$last=$this->getLast()->getVersion();
		$target=VersionDatetime::from($target);
		$unixEpoch=VersionDatetime::from(0);
		$lower= $last<$target? $last : $target;
		return $unixEpoch>$lower? $unixEpoch : $lower;
	}

	/**
	 * @param MigrationsManager $manager
	 * @param string $time
	 * @return array
	 */
	public function dumpSql(MigrationsManager $manager, $time)
	{
		return $this->migrate($manager, $time, FALSE);
	}

	/**
	 * @return bool
	 */
	public function isUpToDate()
	{
		return !($last=$this->getLast())
			|| $last->getVersion()===$this->package->getMigrationVersion();
	}

	/**
	 * @param string $migration
	 * @return Version
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Nette\InvalidStateException
	 */
	public function add($migration)
	{
		if (class_exists($migration)) {
			$version=new Version($this, $migration);
			}
		elseif (is_file($migration) && pathinfo($migration, PATHINFO_EXTENSION)==='sql') {
			$version=new SqlVersion($this, $migration);
			}
		else {
			throw new \Nette\InvalidArgumentException('Given migration is neither migration class or sql dump.');
			}

		$key='v'.(string)$version->getVersion();
		if (isset($this->versions[$key])) {
			throw new \Nette\InvalidStateException('Given version '.$version->getVersion().' is already registered.');
			}

		$this->versions[$key]=$version;
		return $version;
	}

	/**
	 * @return Version|NULL
	 */
	public function getFirst()
	{
		return $this->versions->getFirst();
	}

	/**
	 * @return Version|int
	 */
	public function getNext()
	{
		if ($current=$this->getCurrent()) {
			return $this->versions->getNextTo($current);
			}

		return $this->getFirst();
	}

	/**
	 * @param string $version
	 * @return NULL|object
	 */
	public function getNextTo($version)
	{
		if ($version instanceof Version) {
			return $this->versions->getNextTo($version);
		}
		return $this->versions->getNextToKey($version);
	}

	/**
	 * @return Version|int
	 */
	public function getPrevious()
	{
		if ($current=$this->getCurrent()) {
			return $this->versions->getPreviousTo($current);
			}

		return NULL;
	}

	/**
	 * @param string $version
	 * @return NULL|object
	 */
	public function getPreviousTo($version)
	{
		if ($version instanceof Version) {
			return $this->versions->getPreviousTo($version);
			}
		return $this->versions->getPreviousToKey($version);
	}

	/**
	 * @return Version|NULL
	 */
	public function getLast()
	{
		return $this->versions->getLast();
	}

	/**
	 * @return Version[]
	 */
	public function toArray()
	{
		return $this->versions->getValues();
	}

	/********************** \IteratorAggregate **********************/
	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return $this->versions->getIterator();
	}
}
