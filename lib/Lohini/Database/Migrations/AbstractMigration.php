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

use Doctrine\DBAL\Schema\Schema;

/**
 */
abstract class AbstractMigration
extends \Nette\Object
{
	/** @var \Lohini\Database\Migrations\Version */
	private $version;
	/** @var \Doctrine\DBAL\Schema\AbstractSchemaManager */
	protected $schemaManager;
	/** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
	protected $platform;
	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $outputWriter;


	/**
	 * @param Version $version
	 * @param \Symfony\Component\Console\Output\OutputInterface $writer
	 */
	final public function __construct(Version $version, \Symfony\Component\Console\Output\OutputInterface $writer)
	{
		$this->version=$version;
		$this->outputWriter=$writer;
	}

	/**
	 * @param \Doctrine\DBAL\Connection $connection
	 */
	final public function setConnection(\Doctrine\DBAL\Connection $connection=NULL)
	{
		$this->schemaManager= $connection? $connection->getSchemaManager() : NULL;
		$this->platform= $connection? $connection->getDatabasePlatform() : NULL;
	}

	/**
	 * @param string $sql
	 * @param array $params
	 */
	protected function addSql($sql, array $params=array())
	{
		$this->version->addSql($sql, $params);
	}

	/**
	 * @param string $message
	 */
	protected function message($message)
	{
		$this->outputWriter->writeln('    '.$message);
	}

	/**
	 * @param Schema $schema
	 */
	public function preUp(Schema $schema)
	{
	}

	/**
	 * @param Schema $schema
	 */
	abstract public function up(Schema $schema);

	/**
	 * @param Schema $schema
	 */
	public function postUp(Schema $schema)
	{
	}

	/**
	 * @param Schema $schema
	 */
	public function preDown(Schema $schema)
	{
	}

	/**
	 * @param Schema $schema
	 */
	public function down(Schema $schema)
	{
	}

	/**
	 * @param Schema $schema
	 */
	public function postDown(Schema $schema)
	{
	}

	/**
	 * Print a warning message if the condition evalutes to TRUE.
	 *
	 * @param bool $condition
	 * @param string $message
	 */
	public function warnIf($condition, $message='')
	{
		$message= $message ?: 'Unknown Reason';
		if ($condition===TRUE) {
			$this->message('<warning>Warning: '.$message.'</warning>');
			}
	}

	/**
	 * Abort the migration if the condition evalutes to TRUE.
	 *
	 * @param bool $condition
	 * @param string $message
	 * @throws AbortException
	 */
	public function abortIf($condition, $message='')
	{
		$message= $message ?: 'Unknown Reason';
		if ($condition===TRUE) {
			throw new AbortException($message);
			}
	}

	/**
	 * Skip this migration (but not the next ones) if condition evalutes to TRUE.
	 *
	 * @param bool $condition
	 * @param string $message
	 * @throws SkipException
	 */
	public function skipIf($condition, $message='')
	{
		$message= $message ?: 'Unknown Reason';
		if ($condition===TRUE) {
			throw new SkipException($message);
		 }
	}
}
