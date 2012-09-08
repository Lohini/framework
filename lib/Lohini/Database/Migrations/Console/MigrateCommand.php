<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\Console;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Migrations\Tools\PackageMigration,
	Symfony\Component\Console\Input;

/**
 * Command for generating new migration classes
 */
class MigrateCommand
extends CommandBase
{
	/**
	 */
	protected function configure()
	{
        $this
			->setName('lohini:migrate')
			->setDescription('Migrates database.')
			->addArgument('package', Input\InputArgument::OPTIONAL, 'Name of the package, that will be migrated.')
			->addArgument('version', Input\InputArgument::OPTIONAL, 'Date to be migrated to.')
			->addOption('force', NULL, Input\InputArgument::REQUIRED, "Migration won't start, unless you force it.")
			->setHelp(<<<HELP
The <info>%command.name%</info> command migrates all packages or the given one:
    <info>%command.full_name% MyPackageName</info>

By specifying the <comment>version</comment>, the command migrates to the specified timestamp. When given only date, it migrates to the end of day.
    <info>%command.full_name% MyPackageName Y-m-d H:i:s</info>
    <info>%command.full_name% MyPackageName Y-m-d</info>

You can also migrate by one step only
    <info>%command.full_name% MyPackageName up</info>
    <info>%command.full_name% MyPackageName down</info>
HELP
			);
	}

	/**
	 * @param Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function execute(Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
	{
		$targetVersion= $this->package? $input->getArgument('version') : $input->getArgument('package');
		if ($targetVersion==='0') {
			$targetVersion=0;
			}
		elseif ($targetVersion===NULL) {
			$targetVersion=date('YmdHis');
			}

		$force=$input->getOption('force');
		if ($this->package) {
			try {
				$migration=new PackageMigration($this->migrationsManager, $this->package);
				$migration->run($targetVersion, $force);
				}
			catch (\Lohini\Database\Migrations\MigrationException $e) {
				$output->writeln('');
				$output->writeln('    '.$e->getMessage());
				}
			}
		else {
			foreach ($this->packageManager->getPackages() as $package) {
				try {
					$migration=new PackageMigration($this->migrationsManager, $package);
					$migration->run($targetVersion, $force);
					}
				catch (\Lohini\Database\Migrations\MigrationException $e) {
					$output->writeln("");
					$output->writeln('    '.$e->getMessage());
					}
				}
			}

		if (!$force) {
			$output->writeln('');
			$output->writeln('If everything looks fine, add the --force option.');
			}
	}
}
