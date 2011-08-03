<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;

use Nette\Caching\Cache;

/**
 * WebLoader cache storage.
 * @author Lopo <lopo@lohini.net>
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
