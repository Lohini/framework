<?php // vim: ts=4 sw=4 ai:
namespace BailIff\WebLoader;

use Nette\Application\Control,
	Nette\Caching\Cache,
	Nette\Caching\ICacheStorage,
	Nette\Caching\FileStorage,
	Nette\Environment as NEnvironment,
	Nette\String,
	BailIff\WebLoader\Filters\PreFileFilter,
	Nette\Debug;

/**
 * WebLoader
 *
 * @author Jan Marek
 * @license MIT
 * @author Lopo <lopo@losys.eu>
 */
abstract class WebLoader
extends Control
{
	/**#@+ cache content */
	const CONTENT_TYPE='contentType';
	const CONTENT='content';
	const ETAG='Etag';
	/**#@-*/
	/** @var string */
	protected $sourcePath;
	/** @var string */
	protected $sourceUri;
	/** @var bool */
	protected $joinFiles=TRUE;
	/** @var string */
	private $generatedFileNamePrefix='wldr-';
	/** @var string */
	private $generatedFileNameSuffix='';
	/** @var bool */
	public $throwExceptions=FALSE;
	/** @var array */
	public $filters=array();
	/** @var array */
	public $preFileFilters=array();
	/** @var array */
	public $fileFilters=array();
	/** @var array */
	protected $files=array();
	/** @var Cache */
	private static $cache=NULL;
	/** @var int */
	public static $cacheExpire=NULL;
	/** @var ICacheStorage */
	private static $cacheStorage;
	/** @var string */
	protected $contentType;

	/**
	 * Get html element including generated content
	 * @param string $source
	 * @return Html
	 */
	abstract public function getElement($source);
	/**
	 * Process files and render elements including generated content
	 * @return Html
	 */
	abstract public function renderFiles();
	/**
	 * Add file
	 * @param string $file filename
	 * @param mixed $mixed
	 */
	abstract public function addFile($file, $mixed);

	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render()
	{
		$hasArgs=func_num_args()>0;
		if ($hasArgs) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		$this->renderFiles();
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Set source path
	 * @param string $sourcePath
	 * @return WebLoader (fluent)
	 */
	public function setSourcePath($sourcePath)
	{
		$sourcePath=realpath($sourcePath);
		if ($sourcePath===FALSE) {
			throw new \FileNotFoundException("Source path '$sourcePath' doesn't exist.");
			}
		$this->sourcePath=$sourcePath;
		return $this;
	}

	/**
	 * Get sourcePath
	 * @return string
	 */
	public function getSourcePath()
	{
		return $this->sourcePath;
	}

	/**
	 * Set source Uri
	 * @param string $sourceUri
	 * @return WebLoader (fluent)
	 */
	public function setSourceUri($sourceUri)
	{
		$this->sourceUri=(string)$sourceUri;
		return $this;
	}

	/**
	 * Set joining of files
	 * @param bool $join
	 * @return WebLoader (fluent)
	 */
	public function setJoinFiles($join)
	{
		$this->joinFiles=(bool)$join;
		return $this;
	}

	/**
	 * Set generated file name prefix
	 * @param string $prefix generated file name prefix
	 * @return WebLoader (fluent)
	 */
	public function setGeneratedFileNamePrefix($prefix)
	{
		$this->generatedFileNamePrefix=(string)$prefix;
		return $this;
	}

	/**
	 * Set generated file name suffix
	 * @param string $suffix generated file name suffix
	 * @return WebLoader (fluent)
	 */
	public function setGeneratedFileNameSuffix($suffix)
	{
		$this->generatedFileNameSuffix=(string)$suffix;
		return $this;
	}

	/**
	 * Remove all files
	 */
	public function clear()
	{
		$this->files=array();
	}

	/**
	 * Add files
	 *
	 * Three ways how to set css files.
	 *
	 * 1. Media is not set, this type of files will be packed/minimized to file with media = screen,
	 *
	 *		{assign css=>array(
	 *						'web/screen.css',
	 *						'web/menu.css',
	 *						)
	 *			}
	 *
	 * 2. Media is set, files will be separated by media, there will be to much packs as much is types of media (every pack will be minimized),
	 *
	 *		{assign css=>array(
	 *						'web/screen.css'=>'screen,projection,tv',
	 *						'web/print.css'=>'print',
	 *						)
	 *			}
	 *
	 * 3. You can combine ways.
	 *		{assign css=>array(
	 *						'web/screen.css',
	 *						'web/print.css'=>'print',
	 *						)
	 *			}
	 *
	 * {$control['css']->addFiles($css)}
	 *
	 * At the end you can render all saved files with widget
	 *
	 *		{control css}
	 *
	 * Alternatively you can render files directly, the same result like the lines above is:
	 *
	 *		{control css 'web/screen.css'}
	 *		{control css 'web/screen.css', 'web/menu.css'}
	 *		{control css 'web/screen.css', 'web/print.css'=>'print'}
	 *		{control css 'web/screen.css'=>'screen,projection,tv', 'web/print.css'=>'print'}
	 *
	 * But in this case you render files set only in render, not the before saved files from presenter etc.
	 *
	 * Adding of javascript files is similar, but if there is not set type of processing, there is automaticaly set default type COMPACT, actually it means compact without minimizing.
	 *
	 *		{assign js=>array(
	 *						'datagrid.js',
	 *						'mootools.nette.js',
	 *						)
	 *			}
	 *
	 *		{assign js=>array(
	 *						'datagrid.js',
	 *						'mootools.nette.js'=>JSLoader::MINIFY,
	 *						)
	 *			}
	 *
	 *		{$control['js']->addFiles($js)}
	 *
	 *		{control js}
	 *
	 *		{control js 'datagrid.js'}
	 *		{control js 'datagrid.js', 'jquery.nette.js'}
	 *		{control js 'datagrid.js', 'mootools.nette.js'=>JSLoader::MINIFY}
	 *
	 * @param array $files list of files
	 */
	public function addFiles(array $files)
	{
		foreach ($files as $k => $v) {
			if (is_int($k)) {
				if (is_string($v)) {
					$this->addFile($v);
					}
				elseif (is_array($v)) {
					foreach ($v as $k1 => $v1) {
						if (is_int($k1)) {
							$this->addFile($v1);
							}
						elseif (is_string($k1)) {
							$this->addFile($k1, $v1);
							}
						}
					}
				}
			elseif (is_string($k)) {
				$this->addFile ($k, $v);
				}
			}
	}

	/**
	 * Get last modified timestamp of newest file
	 * @param array $files
	 * @return int
	 */
	public function getLastModified(array $files=NULL)
	{
		if ($files===NULL) {
			$files=$this->files;
			}
		$modified=0;
		foreach ($files as $file) {
			$modified=max($modified, filemtime("$this->sourcePath/$file"));
			}
		return $modified;
	}

	/**
	 * Filename of generated file
	 * @param array $files
	 * @return string
	 */
	public function getGeneratedFilename(array $files=NULL)
	{
		if ($files===NULL) {
			$files=$this->files;
			}
		$name=substr(md5(implode('|', $files)), 0, 12);
		if (count($files)===1) {
			$name.='-'.pathinfo($files[0], PATHINFO_FILENAME);
			}
		return $this->generatedFileNamePrefix.$name.$this->generatedFileNameSuffix;
	}

	/**
	 * Get joined content of all files
	 * @param array $files
	 * @return string
	 */
	public function getContent(array $files=NULL)
	{
		if ($files===NULL) {
			$files=$this->files;
			}
		// load content
		$content='';
		foreach ($files as $file) {
			$content.=$this->loadFile($file);
			}
		// apply filters
		foreach ($this->filters as $filter) {
			$content=call_user_func($filter, $content, $this);
			}
		return $content;
	}

	/**
	 * Load content and save file
	 * @param array $files
	 * @param mixed $content
	 * @return string filename of generated file
	 */
	protected function generate($files, $content=NULL)
	{
		$key=String::webalize($this->getGeneratedFilename($files));
		$cache=self::getCache();

		if ($cache[$key]===NULL) {
			if (is_null($content)) {
				$content=$this->getContent($files);
				}
			foreach ($files as $file) {
				$cache->save(
					$key,
					array(
						self::CONTENT_TYPE => $this->contentType,
						self::ETAG => md5($content),//.'-'.dechex(time()),
						self::CONTENT => $content
						),
					array(
						Cache::FILES => array_map(create_function("\$args,\$path='$this->sourcePath'", 'return "$path/$args";'), $files),
						Cache::EXPIRE => self::$cacheExpire,
						Cache::CONSTS => array(
							'Nette\Framework::REVISION',
							'BailIff\Core::REVISION',
							),
						)
					);
				}
			$cache->release();
			}
		return $key;
	}

	/**
	 * Load file
	 * @param string $file filepath
	 * @return string
	 */
	protected function loadFile($file)
	{
		if (($content=file_get_contents("$this->sourcePath/$file"))===FALSE) {
			if ($this->throwExceptions) {
				if (NEnvironment::isProduction())
					throw new \FileNotFoundException("File '$this->sourcePath/$file' doesn't exist.");
				else {
					Debug::processException(new \FileNotFoundException("File '$this->sourcePath/$file' doesn't exist."));
					return '';
					}
				}
			return '';
			}
		foreach ($this->preFileFilters as $filter) {
			$fcontent=call_user_func($filter, $content, $this, "$this->sourcePath/$file");
			$content= is_array($fcontent)? $fcontent[PreFileFilter::CONTENT] : $fcontent;
			foreach ($this->fileFilters as $filter) {
				$content=\call_user_func($filter, $content, $this, "$this->sourcePath/$file");
				}
			}
		return $content;
	}

	/**
	 * Get cache
	 * @return Cache
	 */
	protected static function getCache()
	{
		if (self::$cache===NULL) {
			self::$cache=NEnvironment::getCache('BailIff.WebLoader');
//			self::$cache=new Cache(self::getCacheStorage(), 'BailIff.WebLoader');
			}
		return self::$cache;
	}

	/**
	 * Set cache storage
	 * @param  Cache
	 */
	protected static function setCacheStorage(ICacheStorage $storage)
	{
		self::$cacheStorage=$storage;
	}

	/**
	 * Get cache storage
	 * @return ICacheStorage
	 */
	protected static function getCacheStorage()
	{
		if (self::$cacheStorage===NULL) {
			$dir=NEnvironment::getVariable('tempDir').'/cache';
			umask(0000);
			@mkdir($dir, 0755); // @ - directory may exists
			self::$cacheStorage=new WebLoaderCacheStorage($dir);
			}
		return self::$cacheStorage;
	}

	/**
	 * Retrieves the specified item from the cache or NULL if the key is not found (\ArrayAccess implementation).
	 * @param  string key
	 * @return mixed|NULL
	 * @throws \InvalidArgumentException
	 */
	public static function getItem($key)
	{
		$cache=self::getCache();
		$item=$cache->offsetGet($key);
		$content=$item[self::CONTENT];
		if (preg_match_all('/{\[of#(?P<filter>.*?)#(?P<key>.*?)#cf\]}/m', $content, $matches)) {
			for ($i=0; $i<count($matches[0]); $i++) {
				$content=str_replace(
						$matches[0][$i],
						call_user_func(__NAMESPACE__.'\Filters\\'.$matches['filter'][$i].'Filter::getItem', $matches['key'][$i]),
						$content
						);
				}
			}
		return array(
			self::CONTENT_TYPE => $item[self::CONTENT_TYPE],
			self::ETAG => md5($content),
			self::CONTENT => $content
			);
	}
}
