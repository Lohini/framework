<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff;

/**
 * BailIff system
 * 
 * @author Lopo <lopo@losys.eu>
 */
final class Core
{
	/**#@+ BailIff version ID's */
	const NAME='BailIff';
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
