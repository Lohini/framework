<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Assets\Storage;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class PublicStorageTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Extension\Assets\Storage\PublicStorage */
	private $storage;
	/** @var string */
	private $dir;
	/** @var \Nette\Http\UrlScript */
	private $url;


	public function setUp()
	{
		$this->dir=$this->getContext()->expand('%tempDir%/www');
		$this->url=new \Nette\Http\UrlScript('http://www.kdyby.org/static/');

		$this->storage=new \Lohini\Extension\Assets\Storage\PublicStorage($this->dir, new \Nette\Http\Request($this->url));
	}

	public function tearDown()
	{
		\Lohini\Utils\Filesystem::rmDir($this->dir);
	}

	/**
	 * @return array
	 */
	public function dataAssets()
	{
		$asset=$this->getMockBuilder('Assetic\Asset\FileAsset')
			->disableOriginalConstructor()
			->getMock();

		$asset->expects($this->once())
			->method('getTargetPath')
			->will($this->returnValue('public/css/lipsum.css'));

		return array(
			array($asset)
			);
	}

	/**
	 * @dataProvider dataAssets
	 *
	 * @param $asset
	 */
	public function testWriteAsset($asset)
	{
		$contents=file_get_contents(__DIR__.'/../Fixtures/lipsum.css');
		$asset->expects($this->once())
			->method('dump')
			->will($this->returnValue($contents));

		$this->storage->writeAsset($asset);

		$this->assertFileExists($file=$this->dir.'/public/css/lipsum.css');
		$this->assertEquals($contents, file_get_contents($file));
	}

	/**
	 * @dataProvider dataAssets
	 *
	 * @param $asset
	 */
	public function testWriteAssetManager($asset)
	{
		$contents=file_get_contents(__DIR__.'/../Fixtures/lipsum.css');
		$asset->expects($this->once())
			->method('dump')
			->will($this->returnValue($contents));

		$am=new \Assetic\AssetManager;
		$am->set('name', $asset);

		$this->storage->writeManagerAssets($am);

		$this->assertFileExists($file=$this->dir.'/public/css/lipsum.css');
		$this->assertEquals($contents, file_get_contents($file));
	}

	/**
	 * @dataProvider dataAssets
	 *
	 * @param $asset
	 */
	public function testGetAssetUrl($asset)
	{
		$baseUrl=rtrim($this->url->getBaseUrl(), '/');
		$expected=$baseUrl.'/public/css/lipsum.css';

		$this->assertEquals($expected, $this->storage->getAssetUrl($asset));
	}

	public function testIsFresh()
	{
		$asset=new \Assetic\Asset\AssetCollection(array(
			new \Assetic\Asset\FileAsset($source=__DIR__.'/../Fixtures/lipsum.css')
			));
		$asset->setTargetPath('public/css/lipsum.css');

		// make sure, there will be no anarchy
		touch($source, time()-10);
		$this->storage->writeAsset($asset);

		// everything should be fresh
		clearstatcache();
		$this->assertTrue($this->storage->isFresh($asset));

		// create anarchy
		touch($source, time()+10);
		clearstatcache();

		// should not be fresh
		$this->assertFalse($this->storage->isFresh($asset));
	}
}
