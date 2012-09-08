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
class SqlVersion
extends Version
{
	/** @var string */
	private $file;


	/**
	 * @param History $history
	 * @param string $file
	 */
	public function __construct(History $history, $file)
	{
		parent::__construct($history, substr(pathinfo($file, PATHINFO_FILENAME), -14));
		$this->file=$file;
	}

	/**
	 * @return mixed
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return bool
	 */
	public function isReversible()
	{
		return FALSE;
	}

	/**
	 * @param MigrationsManager $manager
	 * @param bool $commit
	 * @return array
	 * @throws MigrationException
	 */
	public function up(MigrationsManager $manager, $commit=TRUE)
	{
		$dump=new Tools\SqlDump($this->file);

		$this->setOutputWriter($manager->getOutputWriter());
		$connection=$manager->getConnection();
		$connection->beginTransaction();

		try {
			$start=microtime(TRUE);
			$this->message('<comment>-></comment> executing sql dump');

			// migration
			foreach ($dump as $query) {
				if ($commit) {
					$connection->executeQuery($query);
					}
				}

			$this->markMigrated($commit);
			$this->time=microtime(TRUE)-$start;

			$time=number_format($this->time*1000, 1, '.', ' ');
			$this->message('<info>++</info> migrated <comment>'.$this->getVersion().'</comment> in '.$time.' ms');

			$connection->commit();
			return array();
			}
		catch (\Exception $e) {
			$this->message('<error>Migration '.$this->getVersion().' failed. '.$e->getMessage().'</error>');
			$connection->rollback();
			throw $e;
			}
	}

	/**
	 * @param MigrationsManager $manager
	 * @param bool $commit
	 * @throws MigrationException
	 */
	public function down(MigrationsManager $manager, $commit=TRUE)
	{
		throw new MigrationException('Version '.$this->getVersion().' is irreversible.');
	}

	/**
	 * @param MigrationsManager $manager
	 * @param boolean $up
	 * @return array
	 */
	public function dump(MigrationsManager $manager, $up=TRUE)
	{
		if (!$up) {
			$this->down($manager);
			}

		$dump=new Tools\SqlDump($this->file);
		return $dump->getSqls();
	}

	/**
	 * @param string $sql
	 * @param array $params
	 * @param array $types
	 * @throws \Nette\NotSupportedException
	 */
	public function addSql($sql, array $params=array(), array $types=array())
	{
		throw new \Nette\NotSupportedException;
	}
}
