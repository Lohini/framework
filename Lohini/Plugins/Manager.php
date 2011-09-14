<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Plugins;

/**
 * The plugins manager default implementation.
 * based on \Nette\DI\Container
 *
 * @author Lopo <lopo@lohini.net>
 */
class Manager
extends \Lohini\DI\Container
implements IManager
{
	/** @var array user parameters */
	public $params=array();
	/** @var array storage for shared objects */
	private $registry=array();
	/** @var array circular reference detector */
	private $creating;


	/**
	 * @param \Nette\DI\Container $context
	 */
	public function __construct(\Nette\DI\Container $context)
	{
		$this->addService('context', $context);
		foreach ($context->sqldb->getRepository('LE:Plugin')->findAll() as $plugin) {
			$this->addPlugin($plugin->name, $plugin);
			}
		$this->updateAvailability();
		$context->sqldb->getModelService('Lohini\Database\Models\Entities\Plugin')->disableUpdatedSources();
	}

	/** ** registry manipulation ** **/
	/**
	 * Adds the specified plugin or plugin factory to the manager.
	 * @param string $name
	 * @param mixed $plugin object of plugin entity
	 * @return Manager provides a fluent interface
	 */
	protected function addPlugin($name, $plugin)
	{
		$this->updating();
		if (!is_string($name) || $name==='') {
			throw new \Nette\InvalidArgumentException("Plugin name must be a non-empty string, '".gettype($name)."' given.");
			}
		if (isset($this->registry[$name])) {
			throw new \Nette\InvalidStateException("Plugin '$name' has already been registered.");
			}

		if (is_object($plugin) && $plugin instanceof \Lohini\Database\Models\Entities\Plugin) {
			$this->registry[$name]=$plugin;
			return $this;
			}
		throw PluginException::invalidObject($plugin);
	}

	/**
	 * Gets the plugin object by name.
	 * @param string $name
	 * @return object
	 */
	public function getPlugin($name)
	{
		if (isset($this->registry[$name])) {
			return $this->registry[$name]->plugin;
			}

		try {
			$plugine=$this->addPlugin($name, $this->getService('context')->sqldb->getRepository('LE:Plugin')->findByName($name));
			}
		catch (\Exception $e) {
			throw PluginException::missingType($name);
			}

		$this->registry[$name]=$plugine;
		return $plugine->plugin;
	}

	/**
	 * Exists the plugin?
	 * @param string $name plugin name
	 * @return bool
	 */
	public function hasPlugin($name)
	{
		return isset($this->registry[$name]);
	}

	/**
	 * @return array
	 */
	public function getPlugins()
	{
		return $this->registry;
	}

	/** ** functionality injecting ** **/
	/**
	 * Injects plugins routes into given router
	 * @param \Nette\Application\Routers\RouteList $router
	 */
	public function injectRoutes(\Nette\Application\IRouter $router)
	{
		foreach ($this->registry as $plugin) {
			if ($plugin->installed) {
				$plugin->injectRouter($router);
				}
			}
	}

	/**
	 * Injects plugins routes into given router
	 * @param \Lohini\Localization\ITranslator $translator
	 */
	public function injectTranslations(\Lohini\Localization\ITranslator $translator)
	{
		foreach ($this->registry as $plugin ) {
			if ($plugin->installed) {
				$plugin->injectTranslation($translator);
				}
			}
	}

	/** ** Object magic ** **/
	/**
	 * Gets the plugin object, shortcut for getPlugin().
	 * @param string $name
	 * @return object
	 */
	public function &__get($name)
	{
		if (!isset($this->registry[$name])) {
			$this->getPlugin($name);
			}
		return $this->registry[$name];
	}

	/**
	 * Adds the plugin, shortcut for addPlugin().
	 * @param string $name
	 * @param object $value
	 */
	public function __set($name, $value)
	{
		$this->addPlugin($name, $value);
	}

	/**
	 * Exists the plugin?
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return $this->hasPlugin($name);
	}

	/**
	 * Removes the plugin, shortcut for removePlugin().
	 */
	public function __unset($name)
	{
		$this->removePlugin($name);
	}

	/** ** Sources ** **/
	/**
	 * Finds available plugin sources
	 * @return array
	 */
	public function getSources()
	{
		$sources=array();
		foreach (\Nette\Utils\Finder::findDirectories('*')->in(APP_DIR.'/Plugins') as $dir) {
			$sources[]=$dir->getBaseName();
			}
		return $sources;
	}

	/**
	 * 
	 */
	public function updateAvailability()
	{
		$sources=$this->getSources();
		foreach ($this->registry as $plugin) {
			if (!in_array($plugin->name, $sources)) {
				$plugin->state=Plugin::STATE_DELETED;
				}
			}
		foreach ($sources as $name) {
			if (!$this->hasPlugin($name)) {
				$this->getService('context')->sqldb->getModelService('Lohini\Database\Models\Entities\Plugin')->create(array('name'=>$name));
				}
			}
	}
}
