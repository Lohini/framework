<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini;

/**
 * Lohini system
 * 
 * @author Lopo <lopo@lohini.net>
 */
final class Core
{
	/**#@+ Lohini version ID's */
	const NAME='Lohini';
	const VERSION='0.1.0-dev';
	const REVISION='$WCREV$ released on $WCDATE$';
	const DEVELOPMENT=TRUE;
	/**#@-*/

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException("Can't instantiate static class ".get_class($this));
	}
}
