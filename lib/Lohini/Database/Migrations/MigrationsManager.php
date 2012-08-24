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

use Symfony\Component\Console\Output;

/**
 */
class MigrationsManager
extends \Nette\Object
{
	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;
	/** @var \Lohini\Packages\PackageManager */
	private $packageManager;
	/** @var \Doctrine\DBAL\Connection */
	private $connection;
	/** @var Output\OutputInterface */
	private $outputWriter;
	/** @var bool */
	private $schemaOk=FALSE;


	/**
	 * @param \Lohini\Database\Doctrine\Registry $doctrine
	 * @param \Lohini\Packages\PackageManager $packageManager
	 */
	public function __construct(\Lohini\Database\Doctrine\Registry $doctrine, \Lohini\Packages\PackageManager $packageManager)
	{
		$this->entityManager=$doctrine->getEntityManager();
		$this->packageManager=$packageManager;
		$this->connection=$this->entityManager->getConnection();
	}

	/**
	 * @param Output\OutputInterface $writer
	 */
	public function setOutputWriter(Output\OutputInterface $writer)
	{
		$this->outputWriter=$writer;
	}

	/**
	 * @return Output\OutputInterface
	 */
	public function getOutputWriter()
	{
		if ($this->outputWriter===NULL) {
			$this->outputWriter=new Output\ConsoleOutput;
			}

		return $this->outputWriter;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * @param string $entityClass
	 */
	private function ensureSchema($entityClass)
	{
		/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadata $class */
		$class=$this->entityManager->getClassMetadata($entityClass);
		/** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $sm */
		$sm=$this->connection->getSchemaManager();
		if ($sm->tablesExist($class->getTableName())) {
			return;
			}

		$schemaTool=new \Lohini\Database\Doctrine\Schema\SchemaTool($this->entityManager);
		foreach ($schemaTool->getCreateSchemaSql(array($class)) as $sql) {
			$this->connection->executeQuery($sql);
			}
	}

	/**
	 * @return \Lohini\Database\Doctrine\Dao
	 */
	protected function getPackages()
	{
		if (!$this->schemaOk) {
			$this->ensureSchema('Lohini\Database\Migrations\PackageVersion');
			$this->ensureSchema('Lohini\Database\Migrations\MigrationLog');
			$this->schemaOk=TRUE;
			}

		return $this->entityManager->getRepository('Lohini\Database\Migrations\PackageVersion');
	}

	/**
	 * @param string $packageName
	 * @return \Lohini\Database\Migrations\PackageVersion
	 */
	public function getPackageVersion($packageName)
	{
		$package=$this->getPackages()->findOneBy(array('name' => $packageName));
		if (!$package) {
			$package=new PackageVersion($this->packageManager->getPackage($packageName));
			$this->getPackages()->save($package);
			}
		return $package;
	}

	/**
	 * @param \Lohini\Database\Migrations\PackageVersion $package
	 */
	public function savePackage(PackageVersion $package)
	{
		$this->getPackages()->save($package);
	}

	/**
	 * @param string $packageName
	 * @return \Lohini\Database\Migrations\History
	 */
	public function getPackageHistory($packageName)
	{
		$history=$this->getPackageVersion($packageName)->createHistory();
		foreach ($this->packageManager->getPackage($packageName)->getMigrations() as $migration) {
			$history->add($migration);
			}
		return $history;
	}

	/**
	 * @param string $packageName
	 * @return \Lohini\Database\Migrations\History
	 */
	public function install($packageName)
	{
		$history=$this->getPackageHistory($packageName);
		$history->migrate($this, date('YmdHis'));

		return $history;
	}

	/**
	 * @param string $packageName
	 * @return \Lohini\Database\Migrations\History
	 */
	public function uninstall($packageName)
	{
		$history=$this->getPackageHistory($packageName);
		$history->migrate($this, 0);

		return $history;
	}
}
