<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Packages;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Application\Application;

/**
 */
class PackagesContainer
extends \Nette\Object
implements \IteratorAggregate, \ArrayAccess
{
	/**
	 * @var Package[]
	 */
	private $packages=array();


	/**
	 * @param IPackageList|array $packages
	 * @throws \Nette\UnexpectedValueException
	 */
	public function __construct($packages)
	{
		if ($packages instanceof IPackageList) {
			$packages=$packages->getPackages();
			}

		/** @var \Lohini\Packages\Package[] $packages */
		foreach ($packages as $package) {
			$package= is_string($package)? new $package : $package;
			if (!$package instanceof Package) {
				throw new \Nette\UnexpectedValueException("Given object '".get_class($package)."' is not instanceof 'Lohini\\Packages\\Package'.");
				}

			$this->packages[$package->getName()]=$package;
			}
	}

	/**
	 * @return Package[]
	 */
	public function getPackages()
	{
		return $this->packages;
	}

	/**
	 * @param Application $application
	 */
	public function attach(Application $application)
	{
		$application->onStartup[]=array($this, 'startup');
		$application->onRequest[]=array($this, 'request');
		$application->onResponse[]=array($this, 'response');
		$application->onError[]=array($this, 'error');
		$application->onShutdown[]=array($this, 'shutdown');
	}

	/**
	 * @param \Nette\DI\Container $container
	 */
	public function setContainer(\Nette\DI\Container $container=NULL)
	{
		foreach ($this->packages as $package) {
			$package->setContainer($container);
			}
	}

	/**
	 * Occurs before the application loads presenter
	 */
	public function debug()
	{
		foreach ($this->packages as $package) {
			$package->debug();
			}
	}

	/**
	 * Occurs before the application loads presenter
	 *
	 * @param Application $application
	 */
	public function startup(Application $application)
	{
		foreach ($this->packages as $package) {
			$package->startup();
			}
	}

	/**
	 * Occurs when a new request is ready for dispatch
	 *
	 * @param Application $application
	 * @param \Nette\Application\Request $request
	 */
	public function request(Application $application, \Nette\Application\Request $request)
	{
		foreach ($this->packages as $package) {
			$package->request($request);
			}
	}

	/**
	 * Occurs when a new response is received
	 *
	 * @param Application $application
	 * @param \Nette\Application\IResponse $response
	 */
	public function response(Application $application, \Nette\Application\IResponse $response)
	{
		foreach ($this->packages as $package) {
			$package->response($response);
			}
	}

	/**
	 * Occurs when an unhandled exception occurs in the application
	 *
	 * @param Application $application
	 * @param \Exception $e
	 */
	public function error(Application $application, \Exception $e)
	{
		foreach ($this->packages as $package) {
			$package->error($e);
			}
	}

	/**
	 * Occurs before the application shuts down
	 *
	 * @param Application $application
	 * @param \Exception|NULL $e
	 */
	public function shutdown(Application $application, \Exception $e = NULL)
	{
		foreach ($this->packages as $package) {
			$package->shutdown($e);
			}
	}

	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 */
	public function compile(\Nette\Config\Configurator $config, \Nette\Config\Compiler $compiler)
	{
		$visited=array();
		foreach ($this->packages as $package) {
			$exts=(array)$package->compile($config, $compiler, $this);
			foreach ($exts as $name => $extension) {
				$compiler->addExtension($name, $extension);
				}
			$newExts=array_filter(
					$compiler->getExtensions(),
					function(\Nette\Config\CompilerExtension $compilerExt) use ($visited) {
						return !in_array($compilerExt, $visited)
								&& $compilerExt instanceof IPackageAware;
						}
					);
			$newExts=array_merge($newExts, $exts);

			/** @var \Nette\Config\CompilerExtension|\Lohini\Packages\IPackageAware $ext */
			foreach ($newExts as $ext) {
				$ext->setPackage($package);
				}

			$visited=array_merge($visited, $newExts);
			}
	}

	/**
	 * Returns list of available migrations
	 *
	 * @return array
	 */
	public function getMigrations()
	{
		$migrations=array();

		foreach ($this->packages as $package) {
			$migrations=array_merge($migrations, $package->getMigrations());
			}

		return $migrations;
	}

	/**
	 * Finds and registers Commands.
	 *
	 * Override this method if your bundle commands do not follow the conventions:
	 *
	 * * Commands are in the 'Command' sub-directory
	 * * Commands extend Symfony\Component\Console\Command\Command
	 *
	 * @param \Symfony\Component\Console\Application $app
	 */
	public function registerCommands(\Symfony\Component\Console\Application $app)
	{
		foreach ($this->packages as $package) {
			$package->registerCommands($app);
			}
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->packages);
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->packages[$offset]);
	}

	/**
	 * @param string $offset
	 * @return Package
	 * @throws \Nette\ArgumentOutOfRangeException
	 */
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset)) {
			throw new \Nette\ArgumentOutOfRangeException("Package $offset is not registered in PackagesContainer.");
			}
		return $this->packages[$offset];
	}

	/**
	 * @param string $offset
	 * @param mixed $value
	 * @throws \Nette\NotSupportedException
	 */
	public function offsetSet($offset, $value)
	{
		throw new \Nette\NotSupportedException;
	}

	/**
	 * @param string $offset
	 * @throws \Nette\NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new \Nette\NotSupportedException;
	}
}
