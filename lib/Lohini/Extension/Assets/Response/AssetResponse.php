<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Response;
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

/**
 * @property-read string $file
 * @property-read string $contentType
 */
class AssetResponse
extends \Nette\Object
implements \Nette\Application\IResponse
{
	/** @var \Lohini\Extension\Assets\Storage\CacheStorage */
	private $storage;
	/** @var string */
	private $assetOutput;


	/**
	 * @param \Lohini\Extension\Assets\Storage\CacheStorage $storage
	 * @param string $assetOutput
	 */
	public function __construct(\Lohini\Extension\Assets\Storage\CacheStorage $storage, $assetOutput)
	{
		$this->storage=$storage;
		$this->assetOutput=$assetOutput;
	}

	/**
	 * Sends response to output.
	 *
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Http\IResponse $httpResponse
	 */
	public function send(\Nette\Http\IRequest $httpRequest, \Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->storage->getContentType($this->assetOutput));
		$httpResponse->setHeader('Content-Disposition', 'inline');

		echo $this->storage->readAsset($this->assetOutput);
	}
}
