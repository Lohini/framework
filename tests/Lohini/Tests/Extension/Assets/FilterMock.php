<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Assets;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class FilterMock
extends \Nette\Object
implements \Assetic\Filter\FilterInterface
{
	/**
	 * Filters an asset after it has been loaded.
	 *
	 * @param \Assetic\Asset\AssetInterface $asset An asset
	 */
	public function filterLoad(\Assetic\Asset\AssetInterface $asset)
	{
	}

	/**
	 * Filters an asset just before it's dumped.
	 *
	 * @param \Assetic\Asset\AssetInterface $asset An asset
	 */
	public function filterDump(\Assetic\Asset\AssetInterface $asset)
	{
	}
}
