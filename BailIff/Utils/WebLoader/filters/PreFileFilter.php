<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters;

use Nette\Caching\Cache,
	BailIff\WebLoader\WebLoader,
	BailIff\Environment;
/**
 * Base class for PreFile filters
 * @author Lopo <lopo@losys.eu>
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
					'BailIff\Core::REVISION',
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
