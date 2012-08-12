<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Loaders;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class ExceptionsLoader
extends \Nette\Loaders\AutoLoader
{
	/**
	 * @param string $type
	 */
	public function tryLoad($type)
	{
		if (substr($type, -9)!=='Exception') {
			return;
			}
		foreach (\Lohini\Core::findExceptionClasses() as $file) {
			require_once $file;
			}
	}
}
