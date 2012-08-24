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

use Assetic\Asset\AssetInterface;

/**
 */
interface IStorage
{
	/**
	 * @param \Assetic\AssetManager $am
	 */
	function writeManagerAssets(\Assetic\AssetManager $am);

	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 */
    function writeAsset(AssetInterface $asset);

	/**
	 * @param string|\Assetic\Asset\AssetInterface $asset
	 * @return string
	 */
	function getAssetUrl($asset);

	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 * @return bool
	 */
	function isFresh(AssetInterface $asset);
}
