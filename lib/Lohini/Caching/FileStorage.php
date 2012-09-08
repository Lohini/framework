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
class FileStorage
extends \Nette\Caching\Storages\FileStorage
{
	/**
	 * @param string $key
	 * @return \Nette\DateTime|NULL
	 */
    public function getCreateTime($key)
    {
		return \Nette\DateTime::createFromFormat('U', @filemtime($this->getCacheFile($key))) ?: NULL;
    }
}
