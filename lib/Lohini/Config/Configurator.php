<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Config;

use Nette\Diagnostics\Debugger;

/**
 * Lohini Configurator
 * 
 * @author Lopo <lopo@lohini.net>
 */
class Configurator
extends \Nette\Config\Configurator
{
	/** @var array */
	public $parameters=array(
		'email' => NULL
		);
	/** @var bool */
	private $initialized=FALSE;
	/** @var \Nette\DI\Container */
	private $container;


	/**
	 * Gets initial instance of context
	 *
	 * @param array $parameters
	 * @throws \Lohini\DirectoryNotWritableException
	 */
	public function __construct($parameters=NULL)
	{
		// path defaults
		$this->parameters=static::defaultPaths($parameters)+$this->parameters;

		// check if temp dir is writable
		if (!is_writable($this->parameters['tempDir'])) {
			throw new \Lohini\DirectoryNotWritableException("Temp directory '".$this->parameters['tempDir']."' is not writable");
			}

		// debugger defaults
		$this->setupDebugger(array('debugMode' => FALSE, 'consoleMode' => PHP_SAPI==='cli'));

		// environment
		$this->setDebugMode();
		$this->setEnvironment($this->parameters['productionMode']? 'prod' : 'dev');
	}

	/**
	 * @return \Nette\Config\Configurator 
	 */
	public function createConfigurator()
	{
		$config=new \Nette\Config\Configurator;
		$config->addParameters($this->parameters);
		$config->setTempDirectory($this->parameters['tempDir']);
		return $config;
	}

	/**
	 * @param string $name
	 * @return Configurator (fluent)
	 */
	public function setEnvironment($name)
	{
		$this->parameters['environment']=$name;
		$this->parameters['consoleMode']= $name==='console' ?: PHP_SAPI==='cli';
		return $this;
	}

	/**
	 * When given NULL, the debug mode gets detected automatically
	 *
	 * @param bool|NULL $value
	 * @return Configurator (fluent)
	 */
	public function setDebugMode($value=NULL)
	{
		$this->parameters['debugMode']= is_bool($value)
				? $value
				: \Nette\Config\Configurator::detectDebugMode($value);
		$this->parameters['productionMode']=!$this->parameters['debugMode'];
		$this->parameters['lohini']['debug']=$this->parameters['debugMode'];
		return $this;
	}

	private function startup()
	{
		if ($this->initialized) {
			return;
			}

		// Last call for debugger
		$this->setupDebugger();

		// configure
		$configurator=$this->createConfigurator();

		// robot loader autoRebuild
		foreach (\Nette\Loaders\AutoLoader::getLoaders() as $loader) {
			if ($loader instanceof \Nette\Loaders\RobotLoader) {
				/** @var \Nette\Loaders\RobotLoader $loader */
				$loader->autoRebuild=$this->parameters['debugMode'];
				$loader->setCacheStorage(new \Nette\Caching\Storages\FileStorage($this->parameters['tempDir'].'/cache'));
				}
			}

		// create container
		$configurator->addConfig($configFile=$this->getConfigFile(), \Nette\Config\Configurator::NONE);
		if (is_file($localConfig=str_replace('.neon', '.local.neon', $configFile))) {
			$configurator->addConfig($localConfig, \Nette\Config\Configurator::NONE);
			}
		$this->container=$configurator->createContainer();

		$this->initialized=TRUE;
	}

	/**
	 * @return string
	 * @throws \Nette\InvalidStateException
	 */
	public function getConfigFile()
	{
		$appDir=$this->parameters['appDir'];
		$environment=$this->parameters['environment'];

		if (is_file($config="$appDir/config.neon")) {
			return $config;
			}
		if (is_file($config="$appDir/config/config_$environment.neon")) {
			return $config;
			}
		if (is_file($config="$appDir/config/config.neon")) {
			return $config;
			}
		throw new \Nette\InvalidStateException('No config file found.');
	}

	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	public function getContainer()
	{
		$this->startup();
		return $this->container;
	}

	/* ******************** service factories ******************** */
	/**
	 * Prepares the absolute filesystem paths
	 *
	 * @param array|string $params
	 * @return array
	 */
	protected static function defaultPaths($params)
	{
		// public root
		if ($params===NULL) {
			$params= isset($_SERVER['SCRIPT_FILENAME'])? dirname($_SERVER['SCRIPT_FILENAME']) : NULL;
			}

		if (!is_array($params)) {
			$params=array('wwwDir' => $params);
			}

		// application root
		if (!isset($params['rootDir'])) {
			$params['rootDir']=realpath($params['wwwDir'].'/..');
			}

		// application directory
		if (!isset($params['appDir'])) {
			$params['appDir']=$params['rootDir'].'/app';
			}

		// temp directory
		if (!isset($params['varDir'])) {
			$params['varDir']=$params['rootDir'].'/var';
			}
		if (!isset($params['tempDir'])) {
			$params['tempDir']=$params['varDir'].'/temp';
			}

		// log directory
		if (!isset($params['logDir'])) {
			$params['logDir']=$params['varDir'].'/log';
			}

		return $params;
	}

	/**
	 * Setups the Debugger defaults
	 *
	 * @param array $params
	 * @throws \Lohini\DirectoryNotWritableException
	 */
	protected function setupDebugger($params=array())
	{
		$params=$params+$this->parameters;
		if (!is_dir($logDir=$params['logDir'])) {
			@mkdir($logDir, 0777);
			}

		// check if log dir is writable
		if (!is_writable($logDir)) {
			throw new \Lohini\DirectoryNotWritableException("Logging directory '$logDir' is not writable.");
			}

		Debugger::$strictMode=TRUE;
		Debugger::enable(!$params['debugMode'], $logDir, $params['email']);
		Debugger::$consoleMode=$params['consoleMode'];
	}

	/**
	 * @param string $appDir
	 * @param string $environment
	 * @return Configurator
	 * @throws \Nette\IOException
	 */
	public static function scriptInit($appDir, $environment='console')
	{
		if (!is_dir($appDir)) {
			throw new \Nette\IOException('Given path is not a directory.');
			}

		// arguments
		$config=new static(array(
					'appDir' => $appDir,
					'wwwDir' => $appDir.'/../htdocs',
				));

		$config->setEnvironment($environment);
		$config->setDebugMode(FALSE);
		return $config;
	}
}
