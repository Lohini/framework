<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader;

use Nette\Caching\Cache;

/**
 * WebLoader cache storage.
 * @author Lopo <lopo@losys.eu>
 */
class WebLoaderCacheStorage
extends \Nette\Caching\Storages\FileStorage
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
		if (($pos=strpos($key, Cache::NAMESPACE_SEPARATOR))===FALSE) { //whole namespace
			return parent::getCacheFile($key);
			}
		return parent::getCacheFile(
			substr_replace(
				$key,
				trim(strtr($this->hint, '\\/@', '.._'), '.').'-',
				$pos+1,
				0
				)
			);
	}
}
