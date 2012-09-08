<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Router;
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
class AssetRoute
extends \Nette\Application\Routers\Route
{
	/**
	 * @param string $prefix
	 * @param Assets\IStorage $storage
	 */
	public function __construct($prefix, Assets\IStorage $storage)
	{
		parent::__construct(
			'<prefix '.$prefix.'>/<name .*>',
			array(
				static::PRESENTER_KEY => 'Nette:Micro',
				'callback' => callback(new Assets\Responder\AssetResponder($storage), '__invoke'),
				)
			);
	}
}
