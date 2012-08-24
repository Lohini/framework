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

use Lohini\Database\Migrations\Writers,
	Lohini\Database\Migrations\Tools,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputOption;

/**
 * Command for generating new migration classes
 *
 * @todo: automatically open in IDE
 */
class GenerateCommand extends CommandBase
{
	/**
	 */
	protected function configure()
	{
        $this
			->setName('lohini:generate:migration')
			->setDescription('Generate a migration class.')
			->addArgument('package', InputArgument::REQUIRED, 'Name of the package, that will be command working with.')
			->addArgument('entity', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'List of entities, that will be command working with.')
			->addOption('sql', NULL, InputOption::VALUE_NONE, 'Instead of creating migration class, dump everything to sql file. This kind of migration is automatically irreversible.')
			->addOption('dump-rows', 'r', InputOption::VALUE_NONE, 'For creating INSERT commands for rows in table of the given entity.')
			->addOption('append', 'a', InputOption::VALUE_NONE, 'Instead of creating new file, migration will be appended to the latest one.')
			->setHelp(<<<HELP
The <info>%command.name%</info> command generates a migration class by comparing your current database to your mapping information:
    <info>%command.full_name% MyPackageName</info>

When one or more <comment>entities</comment> are specified, the <info>%command.name%</info> command will take them into consideration,
and all options will work not with all entities, but only with the specified ones:
    <info>%command.full_name% MyPackageName Article Tag Comment</info>

By specifying the <comment>--sql</comment> option, the migration will be dumped to <comment>.sql</comment> file, instead of <comment>migration class</comment>
    <info>%command.full_name% --sql MyPackageName</info>

The <comment>--dump-rows</comment> option will make command write the rows of specified entities (or all entities of specified package) to generated migration:
    <info>%command.full_name% -r MyPackageName</info>
    <info>%command.full_name% -r MyPackageName Article Tag Comment</info>

The <comment>--append</comment> option will make the command append your migration to latest created migration, no matter if <comment>migration class</comment> or <comment>.sql</comment> file.
    <info>%command.full_name% -a MyPackageName</info>
HELP
			);
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @throws \Nette\InvalidStateException
	 */
	protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
	{
		$appendQueries=$input->getOption('append');
		if (($preferSql=$input->getOption('sql')) && $appendQueries) {
			throw new \Nette\InvalidStateException('Please do not provide both --sql and --append options at same time. Whether or not to use sql will be autodetected, when appending.');
			}

		// create writer
		$writer=$this->createWriter($preferSql, $appendQueries);
		$wasWritten=FALSE;

		// optionally remove previous version of migration
		if (!$appendQueries) {
			$writer->removeExisting();
			}

		// collect package metadata
		$metadata=Tools\PartialSchemaComparator::collectPackageMetadata(
			$this->entityManager,
			$this->package,
			$input->getArgument('entity')
			);

		// write schema or dump
		if ($input->getOption('dump-rows')) {
			foreach ($tables=new Tools\TableDumper($this->entityManager, $metadata) as $row) {
				$writer->write(array($row));
				}

			if (isset($row)) {
				$package=$this->package->getName();
				$output->writeln("  Dump of package <info>$package</info> was written to <info>".basename($writer->getFile()).'</info>.');
				$wasWritten=TRUE;
				}
			}
		else {
			$comparator=new Tools\PartialSchemaComparator($this->entityManager);
			if ($writer->write($comparator->compare($metadata))) {
				$output->writeln('  Schema was written to <info>'.basename($writer->getFile()).'</info>.');
				$wasWritten=TRUE;
				}
			}

		if (!$wasWritten) {
			$output->writeln('Nothing to generate.');
			}
	}

	/**
	 * @param bool $sql
	 * @param bool $append
	 * @return \Lohini\Database\Migrations\QueryWriter
	 * @throws \Nette\InvalidStateException
	 */
	protected function createWriter($sql=FALSE, $append=FALSE)
	{
		if ($append) {
			$migrations=$this->package->getMigrations();
			sort($migrations, SORT_ASC);
			$migration=end($migrations);

			if (substr($migration, -4)==='.sql') {
				$migration=substr(basename($migration), 0, -4);
				$sql=TRUE;
				}
			elseif (FALSE!==($pos=strrpos($migration, '\\'))) {
				$migration=substr($migration, $pos+1);
				}

			if (!$migration) {
				$package=$this->package->getName();
				throw new \Nette\InvalidStateException("There are no existing migrations in package $package. Please remove option --append.");
				}
			}
		else {
			$migration='Version'.date('YmdHis');
			}

		if ($sql) {
			return new Writers\SqlWriter($migration, $this->package);
			}
		return new Writers\ClassWriter($migration, $this->package);
	}
}
