<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Utils\Arrays;

/**
 */
class FormulaeManager
extends \Nette\Object
{
	const TYPE_STYLESHEET='css';
	const TYPE_JAVASCRIPT='js';

	/** @var IStorage */
	private $storage;
	/** @var AssetManager */
	private $assetManager;
	/** @var FilterManager */
	private $filterManager;
	/** @var IAssetRepository */
	private $repository;
	/** @var bool */
	private $debug=FALSE;
	/** @var array */
	private $presenterTypes=array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
		);
	/** @var array */
	private $componentTypes=array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
		);
	/** @var array */
	private $packagesRequiredBy=array();
	/** @var array */
	private $resolved=array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
		);


	/**
	 * @param IStorage $storage
	 * @param AssetManager $assetManager
	 * @param FilterManager $filterManager
	 */
	public function __construct(IStorage $storage, AssetManager $assetManager, FilterManager $filterManager)
	{
		$this->storage=$storage;
		$this->assetManager=$assetManager;
		$this->filterManager=$filterManager;
	}

	/**
	 * @param IAssetRepository $provider
	 */
	public function setJavascriptProvider(IAssetRepository $provider)
	{
		$this->repository=$provider;
	}

	/**
	 * @param bool $debug
	 */
	public function setDebug($debug=TRUE)
	{
		$this->debug=(bool)$debug;
	}

	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 * @param string $type
	 * @param array $filters
	 * @param array $options
	 * @param \Nette\ComponentModel\IComponent|NULL $presenterComponent
	 * @return string
	 */
	public function register(\Assetic\Asset\AssetInterface $asset, $type, $filters=array(), $options=array(), \Nette\ComponentModel\IComponent $presenterComponent=NULL)
	{
		if (isset($options['output'])) {
			$asset->setTargetPath($options['output']);
			}

		$options['type']=$type;
//		$options['type']=strtolower(pathinfo($asset->getTargetPath(), PATHINFO_EXTENSION));
		$name=$this->assetManager->add($asset, $filters, $options);
		if (!empty($options['requiredBy'])) {
			foreach ($options['requiredBy'] as $requiredBy) {
				$this->packagesRequiredBy[$requiredBy][]=$name;
				}
			}
		else {
			if ($presenterComponent instanceof \Nette\Application\IPresenter) {
				$this->presenterTypes[$type][]=$name;
				}
			else {
				$this->componentTypes[$type][]=$name;
				}
			}

		return $name;
	}

	/**
	 * @param string $name
	 * @param string $version
	 * @throws \Nette\DI\MissingServiceException
	 */
	public function requireAsset($name, $version=NULL)
	{
		if (!$this->repository) {
			throw new \Nette\DI\MissingServiceException('No implementation of IJavascriptProvider was given.');
			}
		$this->componentTypes[$name]=$version;
	}

	/**
	 * @param string $type
	 * @return array[]
	 */
	public function getAssets($type)
	{
		return $this->resolved[$type];
	}

	/**
	 * @param string $name
	 * @return array
	 */
	public function getAssetInfo($name)
	{
		$asset=$this->assetManager->get($name);
		if ($asset instanceof \Assetic\Asset\AssetCollection) {
			/** @var \Assetic\Asset\AssetCollection $asset */
			foreach ($asset as $one) {
				$asset=$one;
				break;
				}
			}
		return array(
			'source' => $asset->getSourcePath(),
			'src' => $this->storage->getAssetUrl($this->assetManager->get($name))
			)+$this->assetManager->getOptions($name);
	}

	/**
	 * When registered asset is not fresh, writes it to storage
	 */
	public function publish()
	{
		$types=array_unique(array_merge(
			Arrays::flatten($this->presenterTypes),
			Arrays::flatten($this->componentTypes)
			));

		foreach ($this->mergeIntoPackages($types) as $name) {
			$this->publishAndResolve($name);
			}
	}

	/**
	 * @param array $types
	 * @return array
	 */
	private function mergeIntoPackages($types)
	{
		$merged=array();
		do {
			$name=array_shift($types);
			if (($options=$this->assetManager->getOptions($name)) && !empty($options['name'])) {
				$assets=array($first=$this->assetManager->get($name));

				foreach ($this->findPackageParts($options['name'], $options['type'], $types) as $name) {
					$assets[]=$this->assetManager->get($name);
					$options=array_merge($options, $this->assetManager->getOptions($name));
					}

				$coll=new \Assetic\Asset\AssetCollection($assets);
				$coll->setTargetPath($first->getTargetPath());

				$merged[]=$this->assetManager->add($coll, array(), $options);
				}
			else {
				$merged[]=$name;
				}
			} while ($types);

		return $merged;
	}

	/**
	 * @param string $packageName
	 * @param string $type
	 * @param array $types
	 * @return array
	 */
	private function findPackageParts($packageName, $type, array &$types)
	{
		$parts=array();
		foreach ($types as $i => $name) {
			if (($options=$this->assetManager->getOptions($name)) && !empty($options['name'])) {
				if ($options['name']===$packageName && $options['type']===$type) {
					$parts[]=$name;
					unset($types[$i]);
					}
				}
			}

		return $parts;
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	private function publishAndResolve($name)
	{
		$asset=$this->assetManager->get($name);
		$info=$this->getAssetInfo($name);

		// resolve deps
		if (isset($info['name']) && isset($this->packagesRequiredBy[$info['name']])) {
			foreach ($this->packagesRequiredBy[$info['name']] as $requiredName) {
				$this->publishAndResolve($requiredName);
				}
			}

		// register resolved
		if (isset($info['type'])) {
			$type=$info['type'];
			}
		elseif (!in_array($type=pathinfo($info['src'], PATHINFO_EXTENSION), array_keys($this->resolved))) {
			$type=pathinfo($info['source'], PATHINFO_EXTENSION);
			}
		if (isset($info['name'])) {
			$this->resolved[$type][$info['name']]=$info;
			}
		else {
			$this->resolved[$type][$info['source']]=$info;
			}

		// if files is not fresh or published, make it right
		if ($this->storage->isFresh($asset)) {
			return;
			}

		// ensure filters before write
		foreach ($this->assetManager->getFilters($name) as $filter) {
			if ('?'!=$filter[0]) {
				$asset->ensureFilter($this->filterManager->get($filter));
				}
			elseif ($this->debug) {
				$asset->ensureFilter($this->filterManager->get(substr($filter, 1)));
				}
			}
		$this->storage->writeAsset($asset, \Lohini\Utils\MimeTypeDetector::extensionToMime($type, FALSE));
	}
}
