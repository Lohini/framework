<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Responder;
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

use Lohini\Extension\Assets;

/**
 */
class AssetResponder
extends \Nette\Object
{
	/** @var Assets\Storage\CacheStorage */
	private $storage;


	/**
	 * @param Assets\IStorage $storage
	 */
	public function __construct(Assets\IStorage $storage)
	{
		$this->storage=$storage;
	}

	/**
	 * @param string $prefix
	 * @param string $name
	 * @return Assets\Response\AssetResponse
	 */
	public function __invoke($prefix, $name)
	{
		return new Assets\Response\AssetResponse($this->storage, trim($prefix, '/').'/'.trim($name, '/'));
	}
}
