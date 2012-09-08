<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * @author Patrik Votoček
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Reflection\ClassType,
	Nette\Caching\Cache AS NCache;

/**
 * Nette cache driver for doctrine
 */
class Cache
extends \Doctrine\Common\Cache\CacheProvider
{
	/** @var string */
	const CACHE_NS='Doctrine';
	/** @var NCache */
	private $cache;
	/** @var string The namespace to prefix all cache ids with */
	private $namespace;


	/**
	 * @param \Nette\Caching\IStorage $storage
	 * @param string $namespace
	 */
	public function __construct(\Nette\Caching\IStorage $storage, $namespace=self::CACHE_NS)
	{
		$this->cache=new NCache($storage, $namespace);
	}

	/**
	 * Set the namespace to prefix all cache ids with.
	 *
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace=(string)$namespace;
		parent::setNamespace($namespace);
	}

	/**
	 * Prefix the passed id with the configured namespace value
	 *
	 * @param string $id The id to namespace
	 * @return string $id The namespaced id
	 */
	private function getNamespacedId($id)
	{
		if (!$this->namespace || strpos($id, $this->namespace)===0) {
			return $id;
			}

		return $this->namespace.$id;
	}

	/**
	 * @return \Nette\Caching\Cache
	 */
	private function getCache()
	{
		$this->cache->release();
		return $this->cache;
	}

	/**
	 * @param $id
	 * @param $data
	 * @param array $files
	 * @param int $lifeTime
	 * @return bool
	 */
	public function saveDependingOnFiles($id, $data, array $files, $lifeTime=0)
	{
		return $this->doSaveDependingOnFiles($this->getNamespacedId($id), $data, $files, $lifeTime);
	}

	/**
	 * @return array
	 */
	public function getIds()
	{
		return array();
	}

	/**
     * Delete all cache entries.
     *
     * @return array $deleted Array of the deleted cache ids
     */
	public function deleteAll()
	{
		$this->getCache()->clean(array(NCache::TAGS => array('doctrine')));
	}

	/**
	 * @param $id
	 * @return bool
	 */
	protected function doFetch($id)
	{
		return $this->getCache()->load($id) ?: FALSE;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	protected function doContains($id)
	{
		return $this->getCache()->load($id)!==NULL;
	}

	/**
	 * @param $id
	 * @param $data
	 * @param int $lifeTime
	 * @return bool
	 */
	protected function doSave($id, $data, $lifeTime=0)
	{
		$files=array();
		if ($data instanceof \Doctrine\ORM\Mapping\ClassMetadata) {
			$files[]=ClassType::from($data->name)->getFileName();
			foreach ($data->parentClasses as $class) {
				$files[]=ClassType::from($class)->getFileName();
				}
			}

		return $this->doSaveDependingOnFiles($id, $data, $files, $lifeTime);
	}

	/**
	 * @param string $id
	 * @param mixed $data
	 * @param array $files
	 * @param int $lifeTime
	 * @return bool
	 */
	protected function doSaveDependingOnFiles($id, $data, array $files, $lifeTime=0)
	{
		$dp=array(NCache::TAGS => array('doctrine'), NCache::FILES => $files);
		if ($lifeTime!=0) {
			$dp[NCache::EXPIRE]=time()+$lifeTime;
			}

		$this->getCache()->save($id, $data, $dp);
		return TRUE;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	protected function doDelete($id)
	{
		$this->getCache()->save($id, NULL);
		return TRUE;
	}

	/**
	 */
	protected function doFlush()
	{
		$this->getCache()->clean(array(
			NCache::TAGS => array('doctrine')
			));
	}

	/**
	 * @return NULL
	 */
	protected function doGetStats()
	{
		return NULL;
	}
}
