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

use Nette\Diagnostics\Debugger;

/**
 * Redis session handler allows to store session in redis using Nette\Http\Session.
 *
 * <code>
 * $session->setStorage(new \Lohini\Extension\Redis\RedisSessionHandler($redisClient));
 * </code>
 */
class RedisSessionHandler
extends \Nette\Object
implements \Nette\Http\ISessionStorage
{
	/** @internal cache structure */
	const NS_NETTE='Nette.Session';
	/** @var string */
	private $savePath;
	/** @var RedisClient */
	private $client;


	/**
	 * @param RedisClient $redisClient
	 */
	public function __construct(RedisClient $redisClient)
	{
		$this->client=$redisClient;
	}

	/**
	 * @param $savePath
	 * @param $sessionName
	 * @return bool
	 */
	public function open($savePath, $sessionName)
	{
		$this->savePath=$savePath;
		return true;
	}

	/**
	 * @param string $id
	 * @return string
	 */
	public function read($id)
	{
		try {
			return (string)$this->client->get($this->getKeyId($id));
			}
		catch (\Nette\InvalidStateException $e) {
			Debugger::log($e);
			return FALSE;
			}
	}

	/**
	 * @param string $id
	 * @param string $data
	 * @return bool
	 */
	public function write($id, $data)
	{
		try {
			$this->client->setex($this->getKeyId($id), ini_get('session.gc_maxlifetime'), $data);
			return TRUE;
			}
		catch (\Nette\InvalidStateException $e) {
			Debugger::log($e);
			return FALSE;
			}
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function remove($id)
	{
		try {
			$this->client->del($this->getKeyId($id));
			return TRUE;
			}
		catch (\Nette\InvalidStateException $e) {
			Debugger::log($e);
			return FALSE;
			}
	}

	/**
	 * @param string $id
	 * @return string
	 */
	private function getKeyId($id)
	{
		return self::NS_NETTE.':'.substr(md5($this->savePath), 0, 10).':'.$id;
	}

	/**
	 * @return bool
	 */
	public function close()
	{
		return TRUE;
	}

	/**
	 * @param int $maxLifeTime
	 * @return bool
	 */
	public function clean($maxLifeTime)
	{
		return TRUE;
	}
}
