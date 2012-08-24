<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Console\Command;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Caching\Cache,
	Symfony\Component\Console,
	Symfony\Component\Console\Input\InputOption;

/**
 * Show information about mapped entities
 */
class CacheCommand
extends Console\Command\Command
{
	/**
	 */
	protected function configure()
	{
		$this
			->setName('lohini:clear-cache')
			->setDescription('Clears cache')
			->addOption('namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Namespace to invalidate')
			->addOption('tag', NULL, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Tags to invalidate')
			->setHelp("The <info>lohini:clear-cache</info> can invalidate cache, it's namespace or by tag.");
	}

	/**
	 * @param Console\Input\InputInterface $input
	 * @param Console\Output\OutputInterface $output
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		if ($input->getOption('namespace')!==NULL && $input->getOption('tag')!==array()) {
			throw new \Nette\InvalidArgumentException('Specify either tag or namespace, not both.');
			}

		$output->writeln('');
		if (($ns=$input->getOption('namespace'))!==NULL) {
			$this->clearNamespace($ns);
			$output->writeln("Cache namespace '$ns' has been invalidated.");
			}
		else {
			$tags=$input->getOption('tag') ?: NULL;
			foreach ($this->getStorages() as $storage) {
				$storage->clean($tags? array(Cache::TAGS => $tags) : array(Cache::ALL => TRUE));
				}

			if (is_array($tags)) {
				$output->writeln("Cache tags '".implode("', '", $tags)."' were invalidated.");
				}
			else {
				$output->writeln('Cache has been invalidated.');
				}
			}
		$output->writeln('');
	}

	/**
	 * @param string $ns
	 * @return bool
	 */
	private function clearNamespace($ns)
	{
		foreach (\Nette\Utils\Finder::find('*')->from($dir = $this->getNamespaceDir($ns))->childFirst() as $entry) {
			if ($entry->isDir()) {
				@rmdir($entry->getRealPath());
				continue;
				}
			@unlink($entry->getRealPath());
			}
		return @rmdir($dir);
	}

	/**
	 * Returns file name.
	 *
	 * @param string $namespace
	 * @return string
	 */
	private function getNamespaceDir($namespace)
	{
		$dir=urlencode($namespace);
		if ($a=strrpos($dir, $sep=urlencode(Cache::NAMESPACE_SEPARATOR))) {
			$dir=substr_replace($dir, '/_', $a, strlen($sep));
			}
		return $this->getContainer()->expand('%tempDir%/cache').'/_'.$dir;
	}

	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	private function getContainer()
	{
		return $this->getHelper('di')->getContainer();
	}

	/**
	 * @return \Nette\Caching\Storages\FileStorage[]
	 */
	private function getStorages()
	{
		return array(
			$this->getHelper('cacheStorage')->getStorage(),
			$this->getHelper('phpFileStorage')->getStorage(),
			);
	}
}
