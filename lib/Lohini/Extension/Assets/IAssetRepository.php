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
 */
interface IAssetRepository
{
	/**
	 * @param string $name
	 * @param string $version
	 * @return bool
	 */
	function hasAsset($name, $version=NULL);

	/**
	 * @param string $name
	 * @param string $version
	 * @return Repository\AssetPackage
	 */
	function getAsset($name, $version=NULL);

	/**
	 * @param Repository\AssetPackage $asset
	 */
	function registerAsset(Repository\AssetPackage $asset);
}