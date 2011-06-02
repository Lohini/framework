<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassFile class file.
 * File handling utilites.
 * @author                      Chris Yates <chris.l.yates@gmail.com>
 * @copyright   Copyright (c) 2010 PBM Web Development
 * @license                     http://phamlp.googlecode.com/files/license.txt
 * @package                     PHamlP
 * @subpackage  Sass
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use BailIff\WebLoader\Filters\Sass,
	Nette\Caching\Cache,
	Nette\Caching\ICacheStorage,
	Nette\Caching\FileStorage,
	Nette\Utils\Strings;

/**
 * File class
 */
class File
{
	const SASS='sass';
	const SCSS='scss';
	const SASSC='sassc';

	/** @var array */
	private static $extensions=array(self::SASS, self::SCSS);
	/** @var \Nette\Caching\Cache */
	private static $cache;


	/**
	 * Returns the parse tree for a file.
	 * If caching is enabled a cached version will be used if possible; if not the
	 * parsed file will be cached.
	 * @param string $filename filename to parse
	 * @param Sass\Parser $parser Sass parser
	 * @return Sass\RootNode
	 */
	public static function getTree($filename, $parser)
	{
		if (($cached=self::getCachedFile(Strings::webalize(md5($filename))))!==NULL) {
			return $cached;
			}
		$sassParser=new Sass\Parser(array_merge($parser->options, array('line' => 1)));
		$tree=$sassParser->parse($filename);
		return $tree;
	 }

	/**
	 * Returns the full path to a file to parse.
	 * The file is looked for recursively under the load_paths directories and
	 * the template_location directory.
	 * If the filename does not end in .sass or .scss try the current syntax first
	 * then, if a file is not found, try the other syntax.
	 * @param string $filename filename to find
	 * @param Sass\Parser $parser Sass parser
	 * @return string path to file
	 * @throws Sass\Exception if file not found
	 */
	public static function getFile($filename, $parser)
	{
		$ext=Strings::lower(pathinfo($filename, PATHINFO_EXTENSION));
		foreach (self::$extensions as $i => $extension) {
			if ($ext!==self::SASS && $ext!==self::SCSS) {
				if ($i===0) {
					$_filename="$filename.$parser->syntax";
					}
				else {
					$_filename="$filename.".($parser->syntax===self::SASS? self::SCSS : self::SASS);
					}
				}
			else {
				$_filename=$filename;
				}

			if (file_exists($_filename)) {
				return $_filename;
				}

			foreach (array_merge(array(dirname($parser->filename)), $parser->load_paths) as $loadPath) {
				$path=self::findFile($_filename, realpath($loadPath));
				if ($path!==FALSE) {
					return $path;
					}
				} // foreach

			if (!empty($parser->template_location)) {
				$path=self::findFile($_filename, realpath($parser->template_location));
				if ($path!==FALSE) {
					return $path;
					}
				}		
			}
		throw new Sass\Exception("Unable to find import file: $filename");
	}

	/**
	 * Looks for the file recursively in the specified directory.
	 * This will also look for _filename to handle Sass partials.
	 * @param string $filename filename to look for
	 * @param string $dir path to directory to look in and under
	 * @return mixed string: full path to file if found, false if not
	 */
	public static function findFile($filename, $dir)
	{
		$partialname=dirname($filename).DIRECTORY_SEPARATOR.'_'.basename($filename);
		
		foreach (array($filename, $partialname) as $file) {		
			if (file_exists($dir.DIRECTORY_SEPARATOR.$file)) {
				return realpath($dir.DIRECTORY_SEPARATOR.$file);
				}
			}

		$files=array_slice(scandir($dir), 2);
		foreach ($files as $file) {
			if (is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
				$path=self::findFile($filename, $dir.DIRECTORY_SEPARATOR.$file);
				if ($path!==FALSE) {
					return $path;
					}
				}
			} // foreach
		return FALSE;
	}
	/**
	 * Retrieves the specified item from the cache or NULL if the key is not found (\ArrayAccess implementation).
	 * @param  string key
	 * @return mixed|NULL
	 * @throws \InvalidArgumentException
	 */
	public static function getCachedFile($filename)
	{
		return self::getCache()->offsetGet(/*'sass-'.Strings::webalize(md5(*/$filename/*))*/);
	}

	/**
	 * Saves a cached version of the file.
	 * @param Sass\RootNode $sassc Sass tree to save
	 * @param string $filename filename to save
	 * @return string key of created record
	 */
	public static function setCachedFile($sassc, $filename)
	{
		$cache=self::getCache();
//		$key='sass-'.Strings::webalize(md5($filename));
		$cache->save(
			$filename, //$key,
			$sassc,
			array(
				Cache::FILES => $filename, // XXX: "$this->sourcePath/$filename",
				Cache::CONSTS => array(
					'Nette\Framework::REVISION',
					'BailIff\Core::REVISION',
					),
				)
			);
		$cache->release();
//		return $key;
	}

	/**
	 * Get cache
	 * @return Nette\Caching\Cache
	 */
	protected static function getCache()
	{
		if (self::$cache===NULL) {
			self::$cache=\Nette\Environment::getCache('BailIff.WebLoader.Sass');
//			self::$cache=new Cache(self::getCacheStorage(), 'BailIff.WebLoader.Sass');
			}
		return self::$cache;
	}

	/**
	 * Set cache storage
	 * @param  Nette\Caching\Cache
	 */
	protected static function setCacheStorage(ICacheStorage $storage)
	{
		self::$cacheStorage=$storage;
	}

	/**
	 * Get cache storage
	 * @return Nette\Caching\ICacheStorage
	 */
	protected static function getCacheStorage()
	{
		if (self::$cacheStorage===NULL) {
			$dir=NEnvironment::getVariable('tempDir').'/cache';
			umask(0000);
			@mkdir($dir, 0755); // @ - directory may exists
			self::$cacheStorage=new FileStorage($dir);
			}
		return self::$cacheStorage;
	}
}
