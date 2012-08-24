<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class CacheTest
extends \Lohini\Testing\TestCase
{
	/** @var \Nette\Caching\Storages\FileStorage */
	private $storage;
	/** @var \Lohini\Database\Doctrine\Cache */
	private $cache;


	public function setUp()
	{
		$tempDir=$this->getContext()->expand('%tempDir%/cache');
		\Lohini\Utils\Filesystem::cleanDir($tempDir);

		$this->storage=new \Nette\Caching\Storages\FileStorage($tempDir, $this->getContext()->nette->cacheJournal);
		$this->cache=new \Lohini\Database\Doctrine\Cache($this->storage);
	}

	public function testSaving()
	{
		$id='10#20#30';
		$data="před pikolou, za pikolou!";
		$this->cache->save($id, $data);

		$this->assertTrue($this->cache->contains($id));
		$this->assertSame($data, $this->cache->fetch($id));
	}

	public function testSavingOfEntityThatChanges()
	{
		$className=$this->touchTempClass();
		$metadata=new \Doctrine\ORM\Mapping\ClassMetadata($className);
		$metadata->name=$className;

		// save
		$this->cache->save('meta', $metadata);

		// contains
		$this->assertTrue($this->cache->contains('meta'));
		$this->assertEquals($className, $this->cache->fetch('meta')->name);

		// update file
		sleep(1);
		$this->touchTempClass($className);

		// contains no more
		$this->assertFalse($this->cache->contains('meta'));
	}
}
