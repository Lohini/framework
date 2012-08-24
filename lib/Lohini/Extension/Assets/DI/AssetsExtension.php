<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\DI;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class AssetsExtension
extends \Nette\Config\CompilerExtension
{
	/** @var array */
	public $asseticDefaults=array(
		'publicDir' => '%wwwDir%',
		'prefix' => 'static', // XXX: enable change
		'debug' => '%lohini.debug%'
		);


	public function loadConfiguration()
	{
		$builder=$this->getContainerBuilder();

		$options=$this->getConfig($this->asseticDefaults);
		$builder->parameters+=array(
			'assets' => array(
				'debug' => $debug=(bool)$builder->expand($options['debug']),
				'prefix' => $options['prefix'],
				'outputMask' => $options['prefix'].'/*',
				'publicDir' => $options['publicDir']
				)
			);

		if ($debug) {
			$builder->addDefinition($this->prefix('assetStorage'))
				->setClass(
					'Lohini\Extension\Assets\Storage\CacheStorage',
					array('@lohini.cacheStorage', '%tempDir%/cache')
					);

			$builder->addDefinition($this->prefix('route.asset'))
				->setClass('Lohini\Extension\Assets\Router\AssetRoute', array('%assets.prefix%'))
				->setAutowired(FALSE);

			$builder->getDefinition('router')
				->addSetup('offsetSet', array(NULL, $this->prefix('@route.asset')));
			}
		else {
			$builder->addDefinition($this->prefix('assetStorage'))
				->setClass(
					'Lohini\Extension\Assets\Storage\PublicStorage',
					array('%assets.publicDir%')
					);
			}

		$builder->addDefinition($this->prefix('filterManager'))
			->setClass('Lohini\Extension\Assets\FilterManager');

		$builder->addDefinition($this->prefix('assetManager'))
			->setClass('Lohini\Extension\Assets\AssetManager');

		$factory=$builder->addDefinition($this->prefix('assetFactory'))
			->setClass('Lohini\Extension\Assets\AssetFactory', array(1 => '%assets.publicDir%'))
			->addSetup('setAssetManager')
			->addSetup('setFilterManager')
			->addSetup('setDefaultOutput', array('%assets.outputMask%'))
			->addSetup('setDebug', array('%assets.debug%'));

		if (class_exists('Lohini\Packages\PackageManager')) {
			$factory->addSetup('addResolver', array(new \Nette\DI\Statement('Lohini\Extension\Assets\Resolver\PackagePathResolver')));
			if (class_exists('Lohini\Package\Plugins\Manager')) {
				$factory->addSetup('addResolver', array(new \Nette\DI\Statement('Lohini\Extension\Assets\Resolver\PluginPathResolver')));
				$builder->addDefinition($this->prefix('repository'))
					->setClass('Lohini\Extension\Assets\Repository\LohiniPluginsRepository');
				}
			else {
				$builder->addDefinition($this->prefix('repository'))
					->setClass('Lohini\Extension\Assets\Repository\LohiniPackagesRepository');
				}
			}
		else {
			$builder->addDefinition($this->prefix('repository'))
				->setClass('Lohini\Extension\Assets\Repository\PackagesRepository');
			}

		$builder->addDefinition($this->prefix('formulaeManager'))
			->setClass('Lohini\Extension\Assets\FormulaeManager')
			->addSetup('setDebug', array('%assets.debug%'));

		// macros
		$macroFactory='Lohini\Extension\Assets\Latte\AssetMacros::install($service->compiler)';
		$builder->getDefinition('nette.latte')
			->addSetup($macroFactory.'->setFactory(?)->setRepository(?)',
				array(
					$this->prefix('@assetFactory'),
					$this->prefix('@repository')
					)
				);
	}

	// todo: register filters by tags

}
