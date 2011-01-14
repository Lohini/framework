<?php // vim: ts=4 sw=4 ai:
namespace BailIff;

use Nette\Object,
	Nette\Environment as NEnvironment;

/**
 * BailIff Environment
 *
 * @author Lopo <lopo@losys.eu>
 */
class Environment
extends Object
{
	/** @var ArrayObject */
	private static $config;


	/**
	 * Load global configuration from file and process it.
	 * @param string|Config file name or Config object
	 * @return ArrayObject
	 */
	public static function loadConfig($file=NULL)
	{
//		NEnvironment::getSession()->start();
		return self::$config=NEnvironment::getConfigurator()->loadConfig($file!==NULL? $file : '%appDir%/config.ini');
	}

	static public function getApplication()
	{
		return NEnvironment::getApplication();
	}

	/**
	 * @param string $namespace
	 * @return Cache
	 */
	static public function getCache($namespace='')
	{
		return NEnvironment::getCache('BailIff'.(empty($namespace)? NULL : ".$namespace"));
	}

	/**
	 * @return ITranslator
	 */
	static public function getTranslator()
	{
		return NEnvironment::getService('Nette\ITranslator');
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	static public function getConfig($key, $default=NULL)
	{
		if (func_num_args()) {
			return isset(self::$config[$key]) ? self::$config[$key] : $default;
			}
		else {
			return self::$config;
			}
	}
}
