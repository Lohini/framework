<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Storage;
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

use Assetic\Asset\AssetInterface,
	Lohini\Utils\Filesystem,
	Nette\Caching\Cache,
	Lohini\Extension\Curl;

/**
 */
class CacheStorage
extends \Nette\Object
implements \Lohini\Extension\Assets\IStorage
{
	/** @var bool */
	private static $rewriteIsWorking=FALSE;
	/** @var string */
	private $cache;
	/** @var string */
	private $tempDir;
	/** @var string */
	private $baseUrl;


	/**
	 * @param \Nette\Caching\IStorage $storage
	 * @param string $tempDir
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct(\Nette\Caching\IStorage $storage, $tempDir, \Nette\Http\Request $httpRequest)
	{
		$this->cache=new Cache($storage, 'Assetic');
		$this->tempDir=$tempDir;
		$this->baseUrl=rtrim($httpRequest->getUrl()->getBaseUrl(), '/');
	}

	/**
	 * @param \Assetic\AssetManager $am
	 */
	public function writeManagerAssets(\Assetic\AssetManager $am)
	{
		foreach ($am->getNames() as $name) {
			$this->writeAsset($am->get($name));
			}
	}

	/**
	 * @param AssetInterface $asset
	 * @param string $mime
	 * @throws \Nette\NotSupportedException
	 */
	public function writeAsset(AssetInterface $asset, $contentType=NULL)
	{
		// prepare
		$tempFile=$this->tempDir.'/'.basename($contentKey=$asset->getTargetPath());
		Filesystem::write($tempFile, $assetDump=$asset->dump());
		if ($contentType===NULL) {
			$contentType=\Lohini\Utils\MimeTypeDetector::fromFile($tempFile);
			}

		// store
		$this->cache->save(
				$contentKey,
				$assetDump,
				$dp=array(
					Cache::FILES => (array)$this->getAssetDeps($asset)
					)
				);
		$this->cache->save($metaKey='Content-Type:'.$asset->getTargetPath(), $contentType, $dp);

		// cleanup
		Filesystem::rm($tempFile);

		if (static::$rewriteIsWorking===TRUE) {
			return;
			}

		try {
			$test=new Curl\Request($this->getAssetUrl($asset));
			$test->method='HEAD';
			$test->setSender($tester=new Curl\CurlSender);
			$tester->timeout=5;

			static::$rewriteIsWorking=(bool)$test->send();
			}
		catch (Curl\CurlException $e) {
			$this->cache->remove($contentKey);
			$this->cache->remove($metaKey);

			$class=get_called_class();
			throw new \Nette\NotSupportedException(
				"Your current server settings doesn't allow to properly link assets. "
					."$class requires working rewrite technology, "
					.'e.g. mod_rewrite on Apache or properly configured nginx.',
				0,
				$e
			);
		}
	}

	/**
	 * @param string|AssetInterface $asset
	 * @return string
	 */
	public function getAssetUrl($asset)
	{
		if ($asset instanceof AssetInterface) {
			$asset=$asset->getTargetPath();
			}

		return $this->baseUrl.'/'.$asset;
	}

	/**
	 * @param AssetInterface $asset
	 * @return bool
	 */
	public function isFresh(AssetInterface $asset)
	{
		return $this->cache->load($asset->getTargetPath())!==NULL;
	}

	/**
	 * @param string $assetOutput
	 * @return string
	 */
	public function readAsset($assetOutput)
	{
		return $this->cache->load($assetOutput);
	}

	/**
	 * @param string $assetOutput
	 * @return string
	 */
	public function getContentType($assetOutput)
	{
		return $this->cache->load('Content-Type:'.$assetOutput);
	}

	/**
	 * @param AssetInterface $asset
	 * @return array|string
	 */
	private function getAssetDeps(AssetInterface $asset)
	{
		if (!$asset instanceof Assetic\Asset\AssetCollectionInterface) {
			return $asset->getSourceRoot().'/'.$asset->getSourcePath();
			}

		$deps=array();
		foreach ($asset as $leaf) {
			$deps[]=$this->getAssetDeps($leaf);
			}

		return $deps;
	}
}
