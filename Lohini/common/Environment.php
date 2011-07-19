<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini;

use Nette\Environment as NEnvironment,
	Lohini\Configurator;

/**
 * Lohini Environment
 *
 * @author Lopo <lopo@lohini.net>
 */
final class Environment
{
	/** @var \Lohini\Configurator */
	private static $configurator;
	/** @var ArrayObject */
	private static $config;

	
	/**
	 * Gets "class behind Environment" configurator.
	 * @return \Lohini\Configurator
	 */
	public static function getConfigurator()
	{
		if (self::$configurator===NULL) {
			self::$configurator=Configurator::$instance ?: new Configurator;
			}
		return self::$configurator;
	}

	/**
	 * @return \Nette\Application\Application
	 */
	static public function getApplication()
	{
		return NEnvironment::getApplication();
	}

	/**
	 * @param string $namespace
	 * @return \Nette\Caching\Cache
	 */
	static public function getCache($namespace='')
	{
		return NEnvironment::getCache('Lohini'.(empty($namespace)? NULL : ".$namespace"));
	}

	/**
	 * @return \Nette\Localization\ITranslator
	 */
	static public function getTranslator()
	{
		return NEnvironment::getService('translator');
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	static public function getConfig($key=NULL, $default=NULL)
	{
		if (func_num_args()) {
			return isset(self::$config[$key]) ? self::$config[$key] : \Nette\Environment::getConfig($key, $default);
			}
		else {
			return self::$config;
			}
	}
}
