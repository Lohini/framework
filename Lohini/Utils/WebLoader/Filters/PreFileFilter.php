<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters;

use Nette\Caching\Cache,
	Lohini\WebLoader\WebLoader,
	Lohini\Environment;
/**
 * Base class for PreFile filters
 * @author Lopo <lopo@lohini.net>
 */
abstract class PreFileFilter
{
	/**#@+ cache content */
	const CONTENT='content';
	const KEY='key';
	const FILTER='filter';
	const FILE='file';
	/**#@-*/
	/** @var Cache */
	protected static $cache=NULL;
	/** @var int */
	public static $cacheExpire=NULL;


	/**
	 * Invoke filter
	 * @param string $code
	 * @param WebLoader $loader
	 * @param string $file filename
	 * @return string|array
	 */
	static public function __invoke($code, WebLoader $loader, $file=NULL)
	{
		throw new \RuntimeException("Can't be called directly");
	}

	/**
	 * Get cache
	 * @return Cache
	 */
	protected static function getCache()
	{
		if (self::$cache===NULL) {
			self::$cache=Environment::getCache('WebLoader');
			}
		return self::$cache;
	}

	/**
	 * save preprocessed content to cache
	 * @param string $key
	 * @param string $file filename
	 * @param mixed $content
	 */
	protected static function save($key, $file, $content)
	{
		self::getCache()->save(
			$key,
			$content,
			array(
				Cache::FILES => array($file),
				Cache::EXPIRE => self::$cacheExpire,
				Cache::CONSTS => array(
					'Nette\Framework::REVISION',
					'Lohini\Core::REVISION',
					),
				)
			);
	}

	/**
	 * Get cached value
	 * @param string $key cache key
	 */
	public static function getItem($key)
	{
		$cache=self::getCache();
		if (($cached=$cache[$key])!==NULL) {
			return $cached;
			}
		return '';
	}
}
