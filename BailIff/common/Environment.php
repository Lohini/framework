<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
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
	/** @var ArrayObject */
	private static $config;


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

	/**
	 * Loads global configuration from file and processes it
	 *
	 * @param string|Config file name or Config object
	 * @param bool|Config append new config to existing|given ?
	 * @return ArrayObject
	 */
	public static function loadConfig($file=NULL, $section=NULL, $append=NULL)
	{
		if (!$append) {
			return self::$config=NEnvironment::loadConfig($file!==NULL? $file : '%appDir%/config.neon', $section);
			}
		return self::$config=Configurator::mergeConfigs($append===TRUE? self::$config : $append, NEnvironment::loadConfig($file!==NULL? $file : '%appDir%/config.neon', $section));
	}
}
