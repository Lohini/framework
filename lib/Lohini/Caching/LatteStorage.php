<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Caching;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class LatteStorage
extends FileStorage
{
	/**
	 * Reads cache data from disk.
	 *
	 * @param array $meta
	 * @return mixed
	 */
	protected function readData($meta)
	{
		return array(
			'file' => $meta[self::FILE],
			'handle' => $meta[self::HANDLE],
			);
	}

	/**
	 * Returns file name.
	 *
	 * @param string $key
	 * @return string
	 */
	protected function getCacheFile($key)
	{
		return parent::getCacheFile($key).'.latte';
	}
}
