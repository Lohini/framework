<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Utils\Filesystem;

/**
 */
class Configurator
extends \Lohini\Config\Configurator
{
	/** @var Configurator */
	private static $configurator;

	/**
	 * @param array $params
	 * @param \Lohini\Packages\IPackageList $packages
	 */
	public function __construct($params, \Lohini\Packages\IPackageList $packages)
	{
		parent::__construct($params, $packages);
		$this->setEnvironment('test');
		$this->setDebugMode(TRUE);
		static::$configurator=$this;

		// delete exception reports from last run
		foreach ($this->findDiagnosticsFiles() as $file) {
			/** @var \SplFileInfo $file */
			@unlink($file->getRealpath());
			}
	}

	/**
	 * @return \Nette\Utils\Finder|array
	 */
	protected function findDiagnosticsFiles()
	{
		return \Nette\Utils\Finder::findFiles('exception*.html', '*.log', 'dump*.html', '*.latte')
			->in($this->parameters['logDir']);
	}

	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	public static function getTestsContainer()
	{
		return static::$configurator->getContainer();
	}

	/**
	 * @param string $testsDir
	 * @param \Lohini\Packages\IPackageList $packages
	 * @return \Lohini\Testing\Configurator
	 */
	public static function testsInit($testsDir, \Lohini\Packages\IPackageList $packages=NULL)
	{
		if (!is_dir($testsDir)) {
			throw new \Nette\IOException('Given path is not a directory.');
			}

		// arguments
		$params=array(
			'wwwDir' => $testsDir,
			'appDir' => $testsDir,
			'logDir' => $testsDir.'/log',
			'tempDir' => $testsDir.'/temp',
			);
		$packages= $packages ?: \Lohini\Core::createPackagesList();

		// cleanup directories
		Filesystem::cleanDir($params['tempDir'].'/cache');
		Filesystem::cleanDir($params['tempDir'].'/classes');
		Filesystem::cleanDir($params['tempDir'].'/entities');
		Filesystem::cleanDir($params['tempDir'].'/proxies');
		Filesystem::rm($params['tempDir'].'/btfj.dat', FALSE);

		// create container
		return new static($params, $packages);
	}
}
