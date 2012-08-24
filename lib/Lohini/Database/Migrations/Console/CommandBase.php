<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\Console;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Symfony\Component\Console,
	Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generating new migration classes
 */
abstract class CommandBase
extends Console\Command\Command
{
	/** @var \Lohini\Packages\PackageManager */
	protected $packageManager;
	/** @var \Lohini\Database\Migrations\MigrationsManager */
	protected $migrationsManager;
	/** @var \Doctrine\ORM\EntityManager */
	protected $entityManager;
	/** @var \Lohini\Packages\Package */
	protected $package;


	/**
	 * @param Console\Input\InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function initialize(Console\Input\InputInterface $input, OutputInterface $output)
	{
		/** @var \Lohini\Console\PackageManagerHelper $pmh */
		$pmh=$this->getHelper('packageManager');
		$this->packageManager=$pmh->getPackageManager();

		/** @var \Lohini\Database\Migrations\Console\MigrationsManagerHelper $mmh */
		$mmh=$this->getHelper('migrationsManager');
		$this->migrationsManager=$mmh->getMigrationsManager();
		$this->migrationsManager->setOutputWriter($output);

		/** @var \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper $emh */
		$emh=$this->getHelper('entityManager');
		$this->entityManager=$emh->getEntityManager();

		// find package
		if ($package=$input->getArgument('package')) {
			if (!\Nette\Utils\Strings::match($package, '~^[a-z][a-z0-9]*$~i')) {
				return;
				}
			try {
				$this->package=$this->packageManager->getPackage($package);
				}
			catch (\Exception $e) { }
			}

		if ($exit=$this->validateSchema($output)) {
			exit($exit);
		}
	}

	/**
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function validateSchema(OutputInterface $output)
	{
		$validator=new \Doctrine\ORM\Tools\SchemaValidator($this->entityManager);
		$errors=$validator->validateMapping();

		$exit=0;
		if ($errors) {
			foreach ($errors AS $className => $errorMessages) {
				$output->write("<error>[Mapping]  FAIL - The entity-class '$className' mapping is invalid:</error>\n");
				foreach ($errorMessages AS $errorMessage) {
					$output->write("* $errorMessage\n");
					}
				$output->write("\n");
				}
			$exit+=1;
			}

		return $exit;
	}

	/**
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata[]
	 */
	protected function getAllMetadata()
	{
		return $this->entityManager->getMetadataFactory()->getAllMetadata();
	}
}
