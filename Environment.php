<?php // vim: ts=4 sw=4 ai:
namespace BailIff;

use Nette\Object,
	Nette\Environment as NEnvironment;

class Environment
extends Object
{
	static public function loadConfig($file=NULL)
	{
		$config=NEnvironment::loadConfig($file);
//		NEnvironment::getSession()->start();
		return $config;
	}

	static public function getApplication()
	{
		return NEnvironment::getApplication();
	}

	/**
	 * @param string $namespace
	 * @return Nette\Caching\Cache
	 */
	static public function getCache($namespace="")
	{
		return NEnvironment::getCache("BailIff".(empty($namespace)? NULL : ".$namespace"));
	}

	/**
	 * @return Nette\ITranslator
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
		$data=NEnvironment::getConfig("bailiff");
		if (empty($data) || !isset($data->$key))
			return $default;
		return $data->$key;
	}
}
