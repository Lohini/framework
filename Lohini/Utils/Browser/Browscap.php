<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils\Browser;
/**
 * Browscap.ini parsing class with caching and update capabilities
 *
 * PHP version 5
 *
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package	   Browscap
 * @author	   Jonathan Stoppani <st.jonathan@gmail.com>
 * @copyright  Copyright (c) 2006-2008 Jonathan Stoppani
 * @version	   0.7
 * @license	   http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link	   http://garetjax.info/projects/browscap/
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 * @copyright  2010 Lopo <lopo@lohini.net> (Lohini port)
 */

use Nette\Caching\Cache,
	Nette\Environment as NEnvironment;

class Browscap
{
	/** Current version of the class */
	const VERSION='0.7';
	/**
	 * Different ways to access remote and local files
	 *
	 * UPDATE_FOPEN:	 Uses the fopen url wrapper (use file_get_contents).
	 * UPDATE_FSOCKOPEN: Uses the socket functions (fsockopen).
	 * UPDATE_CURL:		 Uses the cURL extension.
	 * UPDATE_LOCAL:	 Updates from a local file (file_get_contents).
	 */
	const UPDATE_FOPEN='URL-wrapper';
	const UPDATE_FSOCKOPEN='socket';
	const UPDATE_CURL='cURL';
	const UPDATE_LOCAL='local';
	/**
	 * Options for regex patterns
	 *
	 * REGEX_DELIMITER: Delimiter of all the regex patterns in the whole class
	 * REGEX_MODIFIERS: Regex modifiers
	 */
	const REGEX_DELIMITER='@';
	const REGEX_MODIFIERS='i';
	/** The values to quote in the ini file */
	const VALUES_TO_QUOTE='Browser|Parent';
	/**
	 * Definitions of the function used by the uasort() function to order the
	 * userAgents array.
	 *
	 * ORDER_FUNC_ARGS:	Arguments that the function will take
	 * ORDER_FUNC_LOGIC: Internal logic of the function
	 */
	const ORDER_FUNC_ARGS='$a, $b';
	const ORDER_FUNC_LOGIC='$a=strlen($a);$b=strlen($b);return$a==$b?0:($a<$b?1:-1);';
	/** The headers to be sent for checking the version and requesting the file */
	const REQUEST_HEADERS="GET %s HTTP/1.0\r\nHost: %s\r\nUser-Agent: %s\r\nConnection: Close\r\n\r\n";

	/* Options for auto update capabilities */
	/**
	 * The location from which download the ini file.
	 * The placeholder for the file should be represented by a %s.
	 * @var string
	 */
	public $remoteIniUrl='http://browsers.garykeith.com/stream.asp?BrowsCapINI';
	/** @var string The location to use to check out if a new version of the browscap.ini file is available */
	public $remoteVerUrl='http://updates.browserproject.com/version-date.asp';
	/** @var string */
	public $remoteVerNumUrl='https://browsers.garykeith.com/versions/version-number.asp';
	/** @var int The timeout for the requests */
	public $timeout=5;
	/** @var int The update interval in seconds */
	public $updateInterval=432000; // 5 days (5*24*60*60)
	/** @var int The next update interval in seconds in case of an error */
	public $errorInterval=7200; // 2 hours (2*60*60)
	/** @var bool Flag to disable the automatic interval based update */
	public $doAutoUpdate=TRUE;
	/** @var string|NULL|FALSE The method to use to update the file, has to be a value of an UPDATE_* constant, NULL or false */
	public $updateMethod=NULL;
	/**
	 * The path of the local version of the browscap.ini file from which to
	 * update (to be set only if used).
	 *
	 * @var string
	 */
	public $localFile=NULL;
	/** @var string The useragent to include in the requests made by the class during the update process */
	public $userAgent='Lohini Browscap/%v %m';
	/**
	 * Flag to enable only lowercase indexes in the result.
	 * The cache has to be rebuilt in order to apply this option.
	 *
	 * @var bool
	 */
	public $lowercase=FALSE;
	/**
	 * Flag to enable/disable silent error management.
	 * In case of an error during the update process the class returns an empty
	 * array/object if the update process can't take place and the browscap.ini
	 * file does not exist.
	 *
	 * @var bool
	 */
	public $silent=FALSE;
	/** @var string Where to store the downloaded ini file */
	public $iniFilename=NULL;
	/** @var bool Flag to be set to TRUE after loading the cache */
	private $_cacheLoaded=FALSE;
	/** @var array Where to store the value of the included PHP cache file */
	private $_userAgents=array();
	private $_browsers=array();
	private $_patterns=array();
	private $_properties=array();
	/** @var int */
	public static $cacheExpire=NULL;
	/** @var Nette\Caching\IStorage */
	private static $cacheStorage;
	/** @var array */
	private static $result=NULL;
	/** @var string */
	private static $input;
	/** @var Browscap */
	private static $instance=NULL;
	/** @var int */
	private $_version=0;


	public function __construct()
	{
		$this->iniFilename=realpath(NEnvironment::getVariable('tempDir')).'/browscap.ini';
	}

	/**
	 * Gets the information about the browser by User Agent
	 *
	 * @param string $user_agent the user agent string
	 * @param bool $return_array whether return an array or an object
	 * @throws BrowscapException
	 * @return Object|array containing the browsers details. Array if $return_array is set to TRUE.
	 */
	public function getBrowser($user_agent=NULL, $return_array=FALSE)
	{
		// Load the cache at the first request
		if (!$this->_cacheLoaded) {
			// Set the interval only if needed
			if ($this->doAutoUpdate && file_exists($this->iniFilename)) {
				$interval=time()-filemtime($this->iniFilename);
				}
			else {
				$interval=0;
				}

			$cache_file=$this->getCache()->offsetGet('parsed');
			// Find out if the cache needs to be updated
			if (is_null($cache_file) || !file_exists($this->iniFilename) || ($interval>$this->updateInterval)) {
				try {
					$this->updateCache();
					}
				catch (BrowscapException $e) {
					if (file_exists($this->iniFilename)) {
						// Adjust the filemtime to the $errorInterval
						touch($this->iniFilename, time()-$this->updateInterval+$this->errorInterval);
						}
					else if ($this->silent) {
						// Return an array if silent mode is active and the ini db doesn't exsist
						return array();
						}
					if (!$this->silent) {
						throw $e;
						}
					}
				}
			$this->_loadCache();
			}

		// Automatically detect the useragent
		$user_agent= isset($user_agent) ?: NEnvironment::getHttpRequest()->getHeader('user-agent', '');

		$browser=array();
		foreach ($this->_patterns as $key => $pattern) {
			if (preg_match($pattern.'i', $user_agent)) {
				$browser=array(
					$user_agent, // Original useragent
					trim(strtolower($pattern), self::REGEX_DELIMITER),
					$this->_userAgents[$key]
					);
				$browser=$value=$browser+$this->_browsers[$key];
				while (array_key_exists(3, $value) && $value[3]) {
					$value=$this->_browsers[(int)$value[3]];
					$browser+=$value;
					}
				$browser[3]= empty($browser[3]) ?: $this->_userAgents[$browser[3]];
				break;
				}
			}

		// Add the keys for each property
		$array=array();
		foreach ($browser as $key => $value) {
			if ($value==='true') {
				$value=TRUE;
				}
			else if ($value==='false') {
				$value=FALSE;
				}
			$array[$this->_properties[$key]]=$value;
			}
		/**
		 * Engine
		 * @link http://en.wikipedia.org/wiki/Web_browser_engine
		 */
		$engines=array(
			'Amaya'=>'Amaya', 'Gecko'=>'Gecko', 'KHTML'=>'KHTML', 'Presto'=>'Presto', 'Prince'=>'Prince', 'Trident'=>'Trident', 'WebKit'=>'WebKit', 'AppleWebKit' => 'WebKit',
//			'Boxely', 'Gazelle', 'GtkHTML', 'HTMLayout', 'iCab', 'Mariner', 'Tasman', 'Tkhtml' //inactive engines
			);
		$array['Engine']='unknown';
		foreach ($engines as $key => $engine) {
			if (preg_match("* $key/*", $user_agent)) {
				$array['Engine']= isset($engine)? $engine : $key;
				break;
				}
			}

		return $return_array? $array : (object)$array;
	}

	/**
	 * Parses the ini file and updates the cache files
	 * @return bool whether the file was correctly written to the disk
	 */
	public function updateCache()
	{
		$url= $this->_getUpdateMethod()==self::UPDATE_LOCAL? $this->localFile : $this->remoteIniUrl;
		$rver= $this->_getRemoteVersionNumber();
		$cache=$this->getCache();
		if ($cache->offsetExists('parsed')) {
			$c=$cache->offsetGet('parsed');
			$cver=$c['version'];
			if ($cver>=$rver) {
				return TRUE;
				}
			}
		$this->_getRemoteIniFile($url);

		$browsers=parse_ini_file($this->iniFilename, TRUE, INI_SCANNER_RAW);
		$this->_version=(int)$browsers['GJK_Browscap_Version']['Version'];
		array_shift($browsers);

		$this->_properties=array_keys($browsers['DefaultProperties']);
		array_unshift(
			$this->_properties,
			'browser_name',
			'browser_name_regex',
			'browser_name_pattern',
			'Parent',
			'Engine'
			);

		$this->_userAgents=array_keys($browsers);
		usort(
			$this->_userAgents,
			create_function(self::ORDER_FUNC_ARGS, self::ORDER_FUNC_LOGIC)
			);

		$user_agents_keys=array_flip($this->_userAgents);
		$properties_keys=array_flip($this->_properties);

		$search=array('\*', '\?');
		$replace=array('.*', '.');

		foreach ($this->_userAgents as $user_agent) {
			$pattern=preg_quote($user_agent, self::REGEX_DELIMITER);
			$this->_patterns[]=self::REGEX_DELIMITER
								.'^'
								.str_replace($search, $replace, $pattern)
								.'$'
								.self::REGEX_DELIMITER;

			if (!empty($browsers[$user_agent]['Parent'])) {
				$browsers[$user_agent]['Parent']=$user_agents_keys[$browsers[$user_agent]['Parent']];
				}

			foreach ($browsers[$user_agent] as $key => $value) {
				$browser[$properties_keys[$key]]=$value;
				}

			$this->_browsers[]=$browser;
			unset($browser);
			}
		unset($user_agents_keys, $properties_keys, $browsers);

		// Save the keys lowercased if needed
		if ($this->lowercase) {
			$this->_properties=array_map('strtolower', $this->_properties);
			}
		// Save and return
		$this->getCache()->save(
			'parsed',
			array(
				'version' => $this->_version,
				'properties' => $this->_properties,
				'browsers' => $this->_browsers,
				'userAgents' => $this->_userAgents,
				'patterns' => $this->_patterns,
				),
			array(
				Cache::FILES => $this->iniFilename,
				Cache::EXPIRE => $this->updateInterval+time(),
				Cache::CONSTS => array(
					'Nette\Framework::REVISION',
					'Lohini\Core::REVISION',
					),
				)
			);
		$this->getCache()->release();
		$this->_cacheLoaded=TRUE;
		return TRUE;
	}

	/**
	 * Loads the cache into object's properties
	 */
	private function _loadCache()
	{
		if ($this->_cacheLoaded) {
			return;
			}
		$cache=$this->getCache();
		$this->_browsers=$cache['parsed']['browsers'];
		$this->_userAgents=$cache['parsed']['userAgents'];
		$this->_patterns=$cache['parsed']['patterns'];
		$this->_properties=$cache['parsed']['properties'];
		$this->_version=$cache['parsed']['version'];

		$this->_cacheLoaded=TRUE;
	}

	/**
	 * Updates the local copy of the ini file (by version checking) and adapts his syntax to the PHP ini parser
	 * @param string $url  the url of the remote server
	 * @throws BrowscapException
	 * @return bool if the ini file was updated
	 */
	private function _getRemoteIniFile($url)
	{
		// Check version
		if (file_exists($this->iniFilename)
			&& filesize($this->iniFilename)
			&& ($this->_getUpdateMethod()==self::UPDATE_LOCAL? $this->_getLocalMTime() : $this->_getRemoteMTime())<filemtime($this->iniFilename)) {
				// No update needed, return
				touch($this->iniFilename, time());
				return FALSE;
			}

		// Get updated .ini file
		$browscap=$this->_getRemoteData($url);

		$browscap=explode("\n", $browscap);
		$pattern=self::REGEX_DELIMITER
				 .'('
				 .self::VALUES_TO_QUOTE
				 .')="?([^"]*)"?$'
				 .self::REGEX_DELIMITER;

		// Ok, lets read the file
		$content='';
		foreach ($browscap as $subject) {
			$content.=preg_replace($pattern, '$1="$2"', trim($subject))."\n";
			}

		if (!@file_put_contents($this->iniFilename, $content)) {
			throw new BrowscapException("Could not write .ini content to '$this->iniFilename'");
			}
		return TRUE;
	}

	/**
	 * Gets the remote ini file update timestamp
	 *
	 * @return int the remote modification timestamp
	 * @throws BrowscapException
	 */
	private function _getRemoteMTime()
	{
		$remote_tmstp=strtotime($this->_getRemoteData($this->remoteVerUrl));

		if (!$remote_tmstp) {
			throw new BrowscapException("Bad datetime format from {$this->remoteVerUrl}");
			}
		return $remote_tmstp;
	}

	/**
	 * Gets the remote ini file update version
	 *
	 * @return int the remote version
	 * @throws BrowscapException
	 */
	private function _getRemoteVersionNumber()
	{
		if ($this->updateMethod==self::UPDATE_LOCAL) {
			$ini=parse_ini_file($this->localFile, TRUE, INI_SCANNER_RAW);
			return (int)$ini['GJK_Browscap_Version']['Version'];
			}
		return (int)$this->_getRemoteData($this->remoteVerNumUrl);
	}

	/**
	 * Gets the local ini file update timestamp
	 * @throws BrowscapException
	 * @return int the local modification timestamp
	 */
	private function _getLocalMTime()
	{
		if (is_readable($this->localFile) && is_file($this->localFile)) {
			return filemtime($this->localFile);
			}
		throw new BrowscapException('Local file is not readable');
	}

	/**
	 * Checks for the various possibilities offered by the current configuration
	 * of PHP to retrieve external HTTP data
	 *
	 * @return string the name of function to use to retrieve the file
	 */
	private function _getUpdateMethod()
	{
		// Caches the result
		if ($this->updateMethod===NULL) {
			if ($this->localFile!==NULL) {
				$this->updateMethod=self::UPDATE_LOCAL;
				}
			else if (ini_get('allow_url_fopen') && function_exists('file_get_contents')) {
				$this->updateMethod=self::UPDATE_FOPEN;
				}
			else if (function_exists('fsockopen')) {
				$this->updateMethod=self::UPDATE_FSOCKOPEN;
				}
			else if (extension_loaded('curl')) {
				$this->updateMethod=self::UPDATE_CURL;
				}
			else {
				$this->updateMethod=FALSE;
				}
			}
		return $this->updateMethod;
	}

	/**
	 * Retrieve the data identified by the URL
	 *
	 * @param string $url the url of the data
	 * @throws BrowscapException
	 * @return string the retrieved data
	 */
	private function _getRemoteData($url)
	{
		ini_set('user_agent', $this->_getUserAgent());
		switch ($this->_getUpdateMethod()) {
			case self::UPDATE_LOCAL:
				$file=file_get_contents($url);
				if ($file!==FALSE) {
					return $file;
					}
				throw new BrowscapException('Cannot open the local file');
			case self::UPDATE_FOPEN:
				$file=file_get_contents($url);
				if ($file!==FALSE) {
					return $file;
					}
				// else try with the next possibility (break omitted)
			case self::UPDATE_FSOCKOPEN:
				$remote_url=parse_url($url);
				$remote_handler=fsockopen($remote_url['host'], 80, $c, $e, $this->timeout);

				if ($remote_handler) {
					stream_set_timeout($remote_handler, $this->timeout);
					if (isset($remote_url['query'])) {
						$remote_url['path'].='?'.$remote_url['query'];
						}

					fwrite(
						$remote_handler,
						sprintf(
							self::REQUEST_HEADERS,
							$remote_url['path'],
							$remote_url['host'],
							$this->_getUserAgent()
							)
						);

					if (strpos(fgets($remote_handler), '200 OK')!==FALSE) {
						$file='';
						while (!feof($remote_handler)) {
							$file.=fgets($remote_handler);
							}

						$file=explode("\n\n", str_replace("\r\n", "\n", $file));
						array_shift($file);
						$file=implode("\n\n", $file);

						fclose($remote_handler);
						return $file;
						}
					} // else try with the next possibility
			case self::UPDATE_CURL:
				if (is_callable('curl_init')) {
					$ch=curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
					curl_setopt($ch, CURLOPT_USERAGENT, $this->_getUserAgent());
					$file=curl_exec($ch);
					curl_close($ch);
					}

				if ($file!==FALSE) {
					return $file;
					}
				// else try with the next possibility
			case FALSE:
				throw new BrowscapException("Your server can't connect to external resources. Please update the file manually.");
			}
	}

	/**
	 * Format the useragent string to be used in the remote requests made by the
	 * class during the update process.
	 *
	 * @return string the formatted user agent
	 */
	private function _getUserAgent()
	{
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			return $_SERVER['HTTP_USER_AGENT'];
			}
		return \Nette\String::replace(
				$this->userAgent,
				array(
					'%v' => self::VERSION,
					'%m' => $this->_getUpdateMethod()
					)
				);
	}

	/**
	 * @return Nette\Caching\Cache
	 */
	protected function getCache()
	{
		return NEnvironment::getCache('Lohini.Browscap');
		if (!self::$cacheStorage) {
//			trigger_error('Missing cache storage.', E_USER_WARNING);
			self::$cacheStorage=new \Nette\Caching\Storages\PhpFileStorage;// DevNullStorage;
			}
		return new Cache(self::$cacheStorage, 'Lohini.Browscap');
	}

	/**
	 * Set cache storage
	 * @param  Nette\Caching\Cache
	 * @return void
	 */
	protected static function setCacheStorage(\Nette\Caching\IStorage $storage)
	{
		self::$cacheStorage=$storage;
	}

	/**
	 * @return Nette\Caching\IStorage
	 */
	protected static function getCacheStorage()
	{
		if (self::$cacheStorage===NULL) {
			return new \Nette\Caching\Storages\FileStorage;// DevNullStorage;
			}
		return self::$cacheStorage;
	}

	public static function get_browser($user_agent=NULL, $return_array=FALSE)
	{
		$inst=self::getInstance();
		if (self::$result==NULL || self::$input!=$user_agent) {
//			$inst->updateMethod=self::UPDATE_LOCAL;
//			$inst->localFile=realpath(NEnvironment::getVariable('tempDir').'/bcap.ini');
			self::$result=$inst->getBrowser($user_agent, TRUE);
			self::$input=$user_agent;
			}
		return $return_array? self::$result : (object)self::$result;
	}

	public static function getInstance()
	{
		if (self::$instance===NULL) {
			self::$instance=new static;
			}
		return self::$instance;
	}
}


/**
 * Browscap.ini parsing class exception
 *
 * @package	   Browscap
 * @author	   Jonathan Stoppani <st.jonathan@gmail.com>
 * @copyright  Copyright (c) 2006-2008 Jonathan Stoppani
 * @license	   http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link	   http://garetjax.info/projects/browscap/
 */
class BrowscapException
extends \Exception
{}
