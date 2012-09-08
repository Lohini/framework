<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Redis;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Caching\Cache,
	Nette\Utils\Json;

/**
 * Redis Storage.
 */
class RedisStorage
extends \Nette\Object
implements \Nette\Caching\IStorage
{
	/** @internal cache structure */
	const NS_META='meta',
		NS_NETTE='Nette.Storage';
	/** @internal cache meta structure: array of */
	const META_TIME='time', // timestamp
		META_SERIALIZED='serialized', // is content serialized?
		META_EXPIRE='expire', // expiration timestamp
		META_DELTA='delta', // relative (sliding) expiration
		META_ITEMS='di', // array of dependent items (file => timestamp)
		META_CALLBACKS='callbacks'; // array of callbacks (function, args)
	/** additional cache structure */
	const KEY='key';

	/** @var RedisClient */
	private $client;
	/** @var \Nette\Caching\Storages\IJournal */
	private $journal;


	/**
	 * @param RedisClient $client
	 * @param \Nette\Caching\Storages\IJournal $journal
	 */
	public function __construct(RedisClient $client, \Nette\Caching\Storages\IJournal $journal=NULL)
	{
		$this->client=$client;
		$this->journal=$journal;
	}

	/**
	 * Read from cache.
	 *
	 * @param string $key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		if (($meta=$this->readMeta($key)) && $this->verify($meta)) {
			$data=$this->client->get($this->getEntryKey($key));
			}
		else {
			return NULL;
			}

		if (empty($meta[self::META_SERIALIZED])) {
			return $data;
			}
		return @unserialize($data); // intentionally @
	}

	/**
	 * Verifies dependencies.
	 *
	 * @param  array
	 * @return bool
	 */
	protected function verify($meta)
	{
		do {
			if (!empty($meta[self::META_DELTA])) {
				$this->client->expire($this->getEntryKey($meta[self::KEY]), $meta[self::META_DELTA]);
				$this->client->expire($this->getMetaKey($meta[self::KEY]), $meta[self::META_DELTA]);
				}
			elseif (!empty($meta[self::META_EXPIRE]) && $meta[self::META_EXPIRE]<time()) {
				break;
				}

			if (!empty($meta[self::META_CALLBACKS]) && !Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
				break;
				}

			if (!empty($meta[self::META_ITEMS])) {
				foreach ($meta[self::META_ITEMS] as $itemKey => $time) {
					$m=$this->readMeta($itemKey);
					if ($m[self::META_TIME]!==$time || ($m && !$this->verify($m))) {
						break 2;
						}
					}
				}
			return TRUE;
			} while (FALSE);

		$this->remove($meta[self::KEY]); // meta[handle] & meta[file] was added by readMetaAndLock()
		return FALSE;
	}

	/**
	 * @param string $key
	 */
	public function lock($key)
	{
		// TODO: http://redis.io/topics/transactions ?
	}

	/**
	 * Writes item into the cache.
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param array $dp
	 * @throws \Nette\InvalidStateException
	 */
	public function write($key, $data, array $dp)
	{
		$meta=array(
			self::META_TIME => microtime(),
			);

		if (isset($dp[Cache::EXPIRATION])) {
			if (empty($dp[Cache::SLIDING])) {
				$meta[self::META_EXPIRE]=$dp[Cache::EXPIRATION]+time(); // absolute time
				}
			else {
				$meta[self::META_DELTA]=(int)$dp[Cache::EXPIRATION]; // sliding time
				}
			}

		if (isset($dp[Cache::ITEMS])) {
			foreach ((array)$dp[Cache::ITEMS] as $itemName) {
				$m=$this->readMeta($itemName);
				$meta[self::META_ITEMS][$itemName]=$m[self::META_TIME]; // may be NULL
				unset($m);
				}
			}

		if (isset($dp[Cache::CALLBACKS])) {
			$meta[self::META_CALLBACKS]=$dp[Cache::CALLBACKS];
			}

		if (isset($dp[Cache::TAGS]) || isset($dp[Cache::PRIORITY])) {
			if (!$this->journal) {
				throw new \Nette\InvalidStateException('CacheJournal has not been provided.');
				}
			$this->journal->write($key, $dp);
			}

		if (!is_string($data) || $data===NULL) {
			$data=serialize($data);
			$meta[self::META_SERIALIZED]=TRUE;
			}

		$meta=Json::encode($meta);

		try {
			if (isset($dp[Cache::EXPIRATION])) {
				$this->client->setEX($this->getMetaKey($key), $dp[Cache::EXPIRATION], $meta);
				$this->client->setEX($this->getEntryKey($key), $dp[Cache::EXPIRATION], $data);
				}
			else {
				$this->client->mSet(
					$this->getMetaKey($key), $meta,
					$this->getEntryKey($key), $data
					);
				}
			}
		catch (RedisClientException $e) {
			$this->remove($key);
			throw new \Nette\InvalidStateException($e->getMessage(), $e->getCode(), $e);
			}
	}

	/**
	 * Removes item from the cache.
	 *
	 * @param string $key
	 */
	public function remove($key)
	{
		$this->client->del(
			$this->getEntryKey($key),
			$this->getMetaKey($key)
			);
	}

	/**
	 * Removes items from the cache by conditions & garbage collector.
	 *
	 * @param array $conds
	 */
	public function clean(array $conds)
	{
		// cleaning using file iterator
		if (!empty($conds[Cache::ALL])) {
			foreach ($this->client->keys(self::NS_NETTE.':*') as $entry) {
				$this->client->del($entry);
				}

			if ($this->journal) {
				$this->journal->clean($conds);
				}
			return;
			}

		// cleaning using journal
		if ($this->journal) {
			foreach ($this->journal->clean($conds) as $key) {
				$this->remove($key);
				}
			}
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function getMetaKey($key)
	{
		return self::NS_NETTE.':'.str_replace(Cache::NAMESPACE_SEPARATOR, ':', $key).':'.self::NS_META;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function getEntryKey($key)
	{
		return self::NS_NETTE.':'.str_replace(Cache::NAMESPACE_SEPARATOR, ':', $key);
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	protected function readMeta($key)
	{
		if ($meta=$this->client->get($this->getMetaKey($key))) {
			try {
				$meta=Json::decode($meta, Json::FORCE_ARRAY);
				}
			catch (\Nette\Utils\JsonException $e) {
				$meta=array();
				\Nette\Diagnostics\Debugger::log($e, 'redis-error');
				}
			$meta[self::KEY]=$key;
			return $meta;
			}

		return NULL;
		}
}
