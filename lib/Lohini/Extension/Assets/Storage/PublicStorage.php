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
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Assetic\Asset\AssetInterface;

/**
 */
class PublicStorage
extends \Nette\Object
implements \Lohini\Extension\Assets\IStorage
{
	/** @var string */
	private $dir;
	/** @var string */
	private $baseUrl;


	/**
	 * @param string $dir
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct($dir, \Nette\Http\Request $httpRequest)
	{
		$this->dir=$dir;
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
	 * @param \Assetic\Asset\AssetInterface $asset
	 */
	public function writeAsset(AssetInterface $asset)
	{
		\Lohini\Utils\Filesystem::write($this->dir.'/'.$asset->getTargetPath(), $asset->dump());
	}

	/**
	 * @param string $assetOutput
	 * @return \Assetic\Asset\AssetInterface
	 */
	public function readAsset($assetOutput)
	{
		throw new \Nette\NotSupportedException('Not supported');
	}

	/**
	 * @param string|\Assetic\Asset\AssetInterface $asset
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
	 * @param \Assetic\Asset\AssetInterface $asset
	 * @return bool
	 */
	public function isFresh(AssetInterface $asset)
	{
		$file=$this->dir.'/'.$asset->getTargetPath();
		if (!file_exists($file)) {
			return FALSE;
			}

		return filemtime($file)>$asset->getLastModified();
	}
}
