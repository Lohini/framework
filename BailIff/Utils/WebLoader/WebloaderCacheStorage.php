<?php // vim: ts=4 sw=4 ai:
namespace BailIff\WebLoader;

use Nette\Caching\Storages\FileStorage,
	Nette\Caching\Cache;
/**
 * WebLoader cache storage.
 * @author Lopo <lopo@losys.eu>
 */
class WebLoaderCacheStorage
extends FileStorage
{
	/** @var string */
	public $hint;

	/**
	 * Returns file name.
	 * @param string $key
	 * @return string
	 */
	protected function getCacheFile($key)
	{
		$key=substr_replace($key, trim(strtr($this->hint, '\\/@', '.._'), '.').'-', strpos($key, Cache::NAMESPACE_SEPARATOR)+1, 0);
		return parent::getCacheFile($key);
	}
}
