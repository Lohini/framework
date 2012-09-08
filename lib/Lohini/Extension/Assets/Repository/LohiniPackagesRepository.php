<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Repository;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
final class LohiniPackagesRepository
extends PackagesRepository
{
	/** @var \Lohini\Packages\PackageManager */
	private $packageManager;


	/**
	 * @param \Lohini\Packages\PackageManager $packageManager
	 */
	public function __construct(\Lohini\Packages\PackageManager $packageManager)
	{
		$this->packageManager=$packageManager;
	}

	/**
	 * @param string $name
	 * @param string $version
	 * @return bool
	 */
	public function hasAsset($name, $version=NULL)
	{
		$this->loadAssets();
		return parent::hasAsset($name, $version);
	}

	/**
	 * @param string $name
	 * @param string $version
	 * @return AssetPackage
	 */
	public function getAsset($name, $version=NULL)
	{
		$this->loadAssets();
		return parent::getAsset($name, $version);
	}

	/**
	 * Crawls registered packages and loads javascript resources information.
	 */
	protected function loadAssets()
	{
		if ($this->assets) {
			return;
			}

		foreach ($this->packageManager->getPackages() as $package) {
			if (!file_exists($definitionFile=$package->getPath().'/Resources/assets.json')) {
				continue;
				}

			$this->registerAssetsFile($definitionFile);
			}
	}
}
