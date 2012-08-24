<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Assets\Latte;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class AsseticMacroSetTest
extends \Lohini\Testing\LatteTestCase
{
	/** @var \Lohini\Extension\Assets\AssetFactory|\PHPUnit_Framework_MockObject_MockObject */
	private $factory;


	public function setUp()
	{
		$this->factory=$this->getMockBuilder('Lohini\Extension\Assets\AssetFactory')
			->disableOriginalConstructor()
			->getMock();

		/** @var \Lohini\Extension\Assets\Latte\AssetMacros $ms */
		$ms=$this->installMacro('Lohini\Extension\Assets\Latte\AssetMacros::install');
		$ms->setFactory($this->factory);
	}

	public function testMacroStylesheet()
	{
		// prepare asset
		$assetColl=new \Assetic\Asset\AssetCollection(array(
			$asset=new \Assetic\Asset\FileAsset(realpath(__DIR__.'/../Fixtures/lipsum.less'))
			));
		$assetColl->setTargetPath('static/main.css');
		foreach ($assetColl as $asset) {
			} // this affects all assets
		$serialized=\Nette\Utils\PhpGenerator\Helpers::formatArgs('?', array(serialize($asset)));

		$this->factory->expects($this->once())
			->method('createAsset')
			->with(
				$this->equalTo(array($input='@Bar/public/css/*.less')),
				$this->equalTo(array('less', 'yui')),
				$this->equalTo(array('root' => 'root', 'fileext' => FALSE))
				)
			->will($this->returnValue($assetColl));

		// parse
		$this->parse('{stylesheet \''.$input.'\', \'filters\' => \'less,yui\', \'root\' => \'root\'}');

		// verify
		$this->assertLatteMacroEquals('', 'Macro has no output');

		$prolog = <<<php
if (!isset(\$template->_fm)) \$template->_fm = Lohini\Extension\Assets\Latte\AssetMacros::findFormulaeManager(\$control);
\$template->_fm->register(unserialize($serialized), 'css', array(
	'less',
	'yui',
), array(
	'root' => 'root',
	'fileext' => FALSE,
	'output' => 'static/main.css',
), \$control);

php;
		$this->assertLattePrologEquals($prolog);
	}

	public function testMacroJavascript()
	{
		// prepare asset
		$assetColl=new \Assetic\Asset\AssetCollection(array(
			$asset=new \Assetic\Asset\FileAsset(realpath(__DIR__.'/../Fixtures/jQuery.js'))
			));
		foreach ($assetColl as $asset) {
			} // this affects all assets
		$serialized=\Nette\Utils\PhpGenerator\Helpers::formatArgs('?', array(serialize($asset)));

		$this->factory->expects($this->once())
			->method('createAsset')
			->with(
				$this->equalTo(array($input='@Bar/public/js/jQuery.js')),
				$this->equalTo(array('closure')),
				$this->equalTo(array('root' => 'root', 'output' => 'static/main.js', 'fileext' => FALSE))
				)
			->will($this->returnValue($assetColl));

		// parse
		$this->parse('{javascript \''.$input.'\', \'filters\' => \'closure\', \'root\' => \'root\', \'output\' => \'static/main.js\'}');

		// verify
		$this->assertLatteMacroEquals('', 'Macro has no output');

		$prolog = <<<php
if (!isset(\$template->_fm)) \$template->_fm = Lohini\Extension\Assets\Latte\AssetMacros::findFormulaeManager(\$control);
\$template->_fm->register(unserialize($serialized), 'js', array(
	'closure',
), array(
	'root' => 'root',
	'output' => 'static/main.js',
	'fileext' => FALSE,
), \$control);

php;
		$this->assertLattePrologEquals($prolog);
	}
}
