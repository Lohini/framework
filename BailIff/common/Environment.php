<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff;

use Nette\Environment as NEnvironment,
	BailIff\Configurator;

/**
 * BailIff Environment
 *
 * @author Lopo <lopo@losys.eu>
 */
final class Environment
{
	/** @var \BailIff\Configurator */
	private static $configurator;
	/** @var ArrayObject */
	private static $config;

	
	/**
	 * Gets "class behind Environment" configurator.
	 * @return \BailIff\Configurator
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
		return NEnvironment::getCache('BailIff'.(empty($namespace)? NULL : ".$namespace"));
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
