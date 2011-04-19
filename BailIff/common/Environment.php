<?php // vim: set ts=4 sw=4 ai:
namespace BailIff;

use Nette\Environment as NEnvironment,
	BailIff\DI\Configurator;

/**
 * BailIff Environment
 *
 * @author Lopo <lopo@losys.eu>
 */
final class Environment
{
	/** @var ArrayObject */
	private static $config;


	/**
	 * Loads global configuration from file and processes it
	 *
	 * @param string|Config file name or Config object
	 * @param bool|Config append new config to existing|given ?
	 * @return ArrayObject
	 */
	public static function loadConfig($file=NULL, $append=NULL)
	{
//		NEnvironment::getSession()->start();
		if ($append===NULL || $append===FALSE) {
			return self::$config=NEnvironment::getConfigurator()->loadConfig($file!==NULL? $file : '%appDir%/config.neon');
			}
		return self::$config=Configurator::mergeConfigs($append===TRUE? self::$config : $append, NEnvironment::getConfigurator()->loadConfig($file!==NULL? $file : '%appDir%/config.neon'));
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
		return NEnvironment::getService('Nette\Localization\ITranslator');
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	static public function getConfig($key=NULL, $default=NULL)
	{
		if (func_num_args()) {
			return isset(self::$config[$key]) ? self::$config[$key] : $default;
			}
		else {
			return self::$config;
			}
	}

	/**
	 * @return string
	 */
	static public function getRootLink()
	{
		foreach (NEnvironment::getApplication()->getRouter() as $r) {
			if ($r->getMask()=='index.php'
//				|| $r->constructUrl(new PresenterRequest(NULL, NULL, array()), new Uri)===NULL // Route::ONE_WAY
				) {
				$d=$r->getDefaults();
				return ":{$d['module']}:{$d['presenter']}:{$d['action']}";
				}
			}
		return '/';
	}
}
