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

use Lohini\Extension\Assets,
	Nette\Utils\Validators;

/**
 */
class PackagesRepository
extends \Nette\Object
implements Assets\IAssetRepository
{
	/** @var array */
	public static $extensionsMap=array(
		'js' => Assets\FormulaeManager::TYPE_JAVASCRIPT,
		'css' => Assets\FormulaeManager::TYPE_STYLESHEET,
		);

	/** @var array|AssetPackage[] */
	protected $assets=array();


	/**
	 * Resolvers, whether or not, the repository provides the script.
	 *
	 * @param string $name
	 * @param null $version
	 * @return bool
	 */
	public function hasAsset($name, $version = NULL)
	{
		if (!isset($this->assets[$name=strtolower($name)])) {
			return FALSE;
			}

		if ($version==='*' || $version==='latest') {
			return TRUE;
			}

		if ($version!==NULL && !isset($this->assets[$name][$version])) {
			return FALSE;
			}

		return TRUE;
	}

	/**
	 * @param string $name
	 * @param string $version
	 * @return mixed
	 * @throws Assets\AssetNotFoundException
	 */
	public function getAsset($name, $version = NULL)
	{
		if (!$this->hasAsset($name=strtolower($name), $version)) {
			throw new Assets\AssetNotFoundException("Assets $name are not registered.");
			}

		$versions=$this->assets[$name];
		if ($version!==NULL && $version!=='latest' && $version!=='*') {
			return $this->assets[$name][$version];
			}
		ksort($versions);
		return end($versions);
	}

	/**
	 * @return Assets\Repository\AssetPackage[]
	 */
	public function getAll()
	{
		return \Nette\Utils\Arrays::flatten($this->assets);
	}

	/**
	 * @param string $definitionFile
	 * @throws \Lohini\FileNotFoundException
	 * @throws Assets\InvalidDefinitionFileException
	 */
	public function registerAssetsFile($definitionFile)
	{
		if (!file_exists($definitionFile)) {
			throw new \Lohini\FileNotFoundException("Definition file $definitionFile is missing.");
			}

		foreach (\Nette\Utils\Json::decode(file_get_contents($definitionFile)) as $definition) {
			try {
				$this->registerAsset($this->createAsset($definition, $definitionFile));
				}
			catch (\Exception $e) {
				throw new Assets\InvalidDefinitionFileException($e->getMessage(), 0, $e);
				}
			}
	}

	/**
	 * @param Assets\Repository\AssetPackage $asset
	 */
	public function registerAsset(AssetPackage $asset)
	{
		$this->assets[strtolower($asset->name)][strtolower($asset->version)]=$asset;
	}

	/**
	 * @internal
	 *
	 * @param array $definition
	 * @param string $definitionFile
	 * @return Assets\Repository\AssetPackage
	 * @throws \Nette\NotSupportedException
	 * @throws \Lohini\UnexpectedValueException
	 * @throws \Lohini\FileNotFoundException
	 * @throws \Nette\Utils\AssertionException
	 */
	public static function createAsset($definition, $definitionFile=NULL)
	{
		$asset=new AssetPackage;
		$definition=(array)$definition;
		/** @var Assets\Repository\AssetFile[] $files */
		$files=array();

		// name of asset
		Validators::assertField($definition, 'name',
			'string|pattern:[-a-z0-9]+/[-a-z0-9]+',
			"item '%' of asset definition, in $definitionFile"
			);
		$asset->name=$definition['name'];
		unset($definition['name']);

		// dependencies
		if (isset($definition['require'])) {
			$asset->require=(array)$definition['require'];
			unset($definition['require']);
			}

		// paths to include in page
		$baseDir=dirname($definitionFile);
		foreach ($definition['paths'] as $path) {
			if (!file_exists($assetPath=$baseDir . '/' . $path)) {
				throw new \Lohini\FileNotFoundException("Path '{$path}' of asset '{$asset->name}', in $definitionFile is not valid.");
				}
			$extension=pathinfo($assetPath, PATHINFO_EXTENSION);
			if (!isset(static::$extensionsMap[$extension]) && !isset($definition['filter'])) {
				throw new \Nette\NotSupportedException("Cannot handle extension $extension of asset '{$asset->name}'.");
				$extension=static::$extensionsMap[$extension];
				}
			$files[$path]=$asset->addPath($assetPath, $extension);
			}
		unset($definition['paths']);

		// filters
		if (isset($definition['filter'])) {
			foreach ($definition['filter'] as $path => $filters) {
				$files[$path]->filters=(array)$filters;
				}
			unset($definition['filter']);
			}
		elseif (isset($definition['filters'])) {
			throw new \Lohini\UnexpectedValueException("Key 'filters' of asset '{$asset->name}' should be named 'filter'.");
			}

		// options
		if (isset($definition['options'])) {
			foreach ($definition['options'] as $path => $options) {
				$files[$path]->options=(array)$options;
				}
			unset($definition['options']);
			}
		elseif (isset($definition['option'])) {
			throw new \Lohini\UnexpectedValueException("Key 'option' of asset '{$asset->name}' should be named 'options'.");
			}

		// version
		if (isset($definition['version'])) {
			Validators::assertField($definition, 'version',
				'string|pattern:[0-9]+(\\.[0-9]+)+(-?[-a-z]+[0-9]*)?',
				"item '%' of asset '{$asset->name}', in $definitionFile"
				);
			$asset->version=$definition['version'];
			unset($definition['version']);
			}

		$definition=(array)$definition;
		if (!empty($definition)) {
			$keys=implode(', ', array_keys($definition));
			throw new \Lohini\UnexpectedValueException("Keys $keys in definition of asset '{$asset->name}', in $definitionFile are ambiguous.");
			}

		return $asset;
	}
}
