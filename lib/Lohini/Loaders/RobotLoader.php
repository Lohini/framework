<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Loaders;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Caching;

/**
 */
class RobotLoader
extends \Nette\Loaders\RobotLoader
{
	const CACHE_NAMESPACE='Nette.RobotLoader';

	/** @var bool */
	public $autoRebuild=FALSE;


	public function __construct()
	{
		parent::__construct();
		$this->setCacheStorage(new Caching\Storages\MemoryStorage);
	}

	/**
	 * @return \DateTime
	 */
	public function getIndexCreateTime()
	{
		$key= is_scalar($key=$this->getKey())? $key : serialize($key);
		$key=self::CACHE_NAMESPACE.Caching\Cache::NAMESPACE_SEPARATOR.md5($key);
		return $this->getCacheStorage()->getCreateTime($key);
	}

	/**
	 * @return \Lohini\Iterators\TypeIterator
	 */
	public function createIndexFilter()
	{
		return new \Lohini\Iterators\TypeIterator(new \ArrayIterator(array_keys($this->getIndexedClasses())));
	}
}
