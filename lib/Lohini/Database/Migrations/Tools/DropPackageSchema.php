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

use Symfony\Component\Console\Output;

/**
 */
class DropPackageSchema
extends \Nette\Object
{
	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;
	/** @var Output\OutputInterface */
	private $outputWriter;
	/** @var \Lohini\Packages\Package */
	private $package;


	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \Lohini\Packages\Package $package
	 */
	public function __construct(\Doctrine\ORM\EntityManager $em, \Lohini\Packages\Package $package)
	{
		$this->entityManager=$em;
		$this->package=$package;
	}

	/**
	 * @param boolean $commit
	 * @throws \Exception
	 */
	public function create($commit=FALSE)
	{
		// metadata
		$metadata=PartialSchemaComparator::collectPackageMetadata(
			$this->entityManager,
			$this->package
			);

		$connection=$this->entityManager->getConnection();
		$connection->beginTransaction();

		$this->message('');
		$this->message('Migrating <comment>'.$this->package->getName().'</comment>');
		$this->message('No migrations are available, will only drop schema.');

		try {
			$start=microtime(TRUE);

			$schemaTool=new \Lohini\Database\Doctrine\Schema\SchemaToolTest($this->entityManager);
			foreach ($schemaTool->getDropSchemaSQL($metadata) as $query) {
				$this->message('<comment>-></comment> '.\Nette\Utils\Strings::replace($query, array('~[\n\r\t ]+~' => ' ')));

				if ($commit) {
					$connection->executeQuery($query);
					}
				}

			if (isset($query)) {
				$time=number_format((microtime(TRUE)-$start)*1000, 1, '.', ' ');
				$this->message('<info>--</info> tables dropped in '.$time.' ms');
				}
			else {
				$this->message('<info>SS</info> schema is already up to date');
				}

			$connection->commit();
			}
		catch (\Exception $e) {
			$this->message('<error>Dropping of tables for package '.$this->package->getName().' failed. '.$e->getMessage().'</error>');

			$connection->rollback();
			throw $e;
			}
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
	 * @param string $message
	 */
	protected function message($message)
	{
		if ($this->outputWriter) {
			$this->outputWriter->writeln('    '.$message);
			}
	}
}
