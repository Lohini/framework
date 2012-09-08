<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Assets;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class AssetManagerTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Extension\Assets\AssetManager */
	private $manager;


	public function setUp()
	{
		$this->manager=new \Lohini\Extension\Assets\AssetManager;
	}

	/**
	 * @return array
	 */
	public function dataAssets()
	{
		$filters=array('less', 'yui');
		$options=array('name' => 'foobar.css');

		$asset=new \Assetic\Asset\AssetCollection(array(
			new \Assetic\Asset\FileAsset(__DIR__.'/Fixtures/lipsum.less')
			));
		$asset->setTargetPath($options['name']);

		return array(
			array($asset, $filters, $options)
			);
	}

	/**
	 * @dataProvider dataAssets
	 *
	 * @param $asset
	 * @param $filters
	 * @param $options
	 */
	public function testAdd($asset, $filters, $options)
	{
		$name=$this->manager->add($asset, $filters, $options);

		// name
		$this->assertEquals(1, $name);
		$this->assertEquals(1, $this->manager->getAssetName($asset));

		// meta
		$this->assertEquals($filters, $this->manager->getFilters($name));
		$this->assertEquals($options, $this->manager->getOptions($name));

		// other way
		$this->assertSame($asset, $this->manager->getOutputAsset($options['name']));
	}
}
