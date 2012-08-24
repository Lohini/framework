<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;
/**
 * @author Patrik Votoček
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Config\Configurator;

/**
 * @method onStartup(Application $sender)
 * @method onShutdown(Application $sender, \Exception $e=NULL)
 * @method onRequest(Application $sender, \Nette\Application\Request $request)
 * @method onResponse(Application $sender, \Nette\Application\IResponse $response)
 * @method onError(Application $sender, \Exception $e)
 */
class Application
extends \Nette\Application\Application
{
	/** @var \Lohini\Config\Configurator */
	private $configurator;
	/** @var \Lohini\Packages\PackageManager */
	private $packageManager;
	/** @var \Lohini\Packages\PackagesContainer */
	private $packages;


	/**
	 * @param array|string|\Nette\Config\Configurator $params
	 * @param string $environment
	 * @param string $productionMode
	 */
	public function __construct($params=NULL, $environment=NULL, $productionMode=NULL)
	{
		$this->configurator= ($params instanceof Configurator)
			? $params
			: $this->createConfigurator($params);

		// environment
		if ($environment!==NULL) {
			$this->configurator->setEnvironment($environment);
			}

		// production mode
		if ($productionMode!==NULL) {
			$this->configurator->setDebugMode(!$productionMode);
			}

		// inject application instance
		$container=$this->configurator->getContainer();
		$container->configureService('application', $this);

		// dependencies
		$this->initialize($container);

		// wire events
		$this->packages=$this->configurator->getPackages();
		$this->packages->setContainer($container);
		$this->packages->attach($this);

		// activate packages
		$this->packageManager->setActive($this->packages);
	}

	/**
	 * @param \Nette\DI\Container|\SystemContainer $container
	 */
	protected function initialize(\Nette\DI\Container $container)
	{
		$this->packageManager=$container->lohini->packageManager;

		parent::__construct(
			$container->nette->presenterFactory,
			$container->router,
			$container->httpRequest,
			$container->httpResponse,
			$container->session
			);
	}

	/**
	 * When debugger is not in production mode, call ->debug() on packages
	 */
	public function run()
	{
		if (\Nette\Diagnostics\Debugger::$productionMode===FALSE) {
			$this->packages->debug();
			}

		parent::run();
	}

	/**
	 * @param array $params
	 * @return \Lohini\Config\Configurator
	 */
	protected function createConfigurator($params)
	{
		return new Configurator($params);
	}

	/**
	 * @return \Lohini\Config\Configurator
	 */
	public function getConfigurator()
	{
		return $this->configurator;
	}

	/* ******************** Packages ******************** */
	/**
	 * Checks if a given class name belongs to an active package.
	 *
	 * @param string $class
	 * @return bool
	 */
	public function isClassInActivePackage($class)
	{
		return $this->packageManager->isClassInActivePackage($class);
	}

	/**
	 * @see \Lohini\Packages\PackageManager::locateResource()
	 *
	 * @param string $name A resource name to locate
	 * @return string|array
	 */
	public function locateResource($name)
	{
		return $this->packageManager->locateResource($name);
	}
}