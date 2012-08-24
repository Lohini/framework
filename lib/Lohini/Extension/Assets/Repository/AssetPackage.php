<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Repository;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\Assets;

/**
 */
class AssetPackage
extends \Nette\Object
{
	/** @var string */
	public $name;
	/** @var string */
	public $version='latest';
	/** @var array */
	private $paths=array(
		Assets\FormulaeManager::TYPE_JAVASCRIPT => array(),
		Assets\FormulaeManager::TYPE_STYLESHEET => array(),
		);
	/** @var array */
	public $require=array();


	/**
	 * @param string $path
	 * @param string $type
	 * @return Assets\Repository\AssetFile
	 */
	public function addPath($path, $type)
	{
		$this->addFile($asset=new AssetFile($path, $type));
		return $asset;
	}

	/**
	 * @param Assets\Repository\AssetFile $file
	 */
	public function addFile(AssetFile $file)
	{
		$this->paths[$file->type][]=$file;
		$file->options['name']=$this->name;
		$file->options['require']=array_keys($this->require);
	}

	/**
	 * @return array|Assets\Repository\AssetFile[]
	 */
	public function getFiles()
	{
		return array_reverse(Arrays::flatten($this->paths));
	}

	/**
	 * @param Assets\IAssetRepository $repository
	 * @return array|Assets\Repository\AssetPackage[]
	 */
	public function getDependencies(Assets\IAssetRepository $repository)
	{
		$dependencies=array();
		foreach ($this->require as $dependency => $version) {
			$dependencies[]=$repository->getAsset($dependency, $version);
			}

		return $dependencies;
	}

	/**
	 * @param Assets\IAssetRepository $repository
	 * @param array $resolved
	 * @return array|Assets\Repository\AssetFile[]
	 */
	public function resolveFiles(Assets\IAssetRepository $repository, $resolved=array())
	{
		$files=array();

		$resolve=array($this);
		do {
			/** @var Assets\Repository\AssetPackage $resolving */
			$resolved[]= $resolving= array_shift($resolve);

			foreach ($resolving->getFiles() as $file) {
				$files[]= $file= clone $file;

				if ($resolving!==$this) {
					$file->options['requiredBy'][]=$this->name;
					}
				}

			foreach ($resolving->getDependencies($repository) as $dependency) {
				if (in_array($dependency, $resolved, TRUE)) {
					continue;
					}
				$resolve[]=$dependency;
				}
			} while($resolve);
		return $files;
	}
}
