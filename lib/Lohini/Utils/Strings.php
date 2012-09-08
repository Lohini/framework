<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
final class Strings
extends \Nette\Object
{
	/**
	 * Static class - cannot be instantiated.
	 *
	 * @throws \Nette\StaticClassException
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException("Can't instantiate static class ".get_class($this));
	}

	/**
	 * @param string $a
	 * @param string $b
	 * @return string
	 */
	public static function blend($a, $b)
	{
		$pos=strrpos($a, $b);
		if ($pos!==FALSE) { // is croping
			return substr($a, 0, $pos+strlen($b));
			}
		// is merging
		$fromRight=0;
		do {
			$fromRight--;
			$pos=strrpos($a, $match=substr($b, 0, $fromRight));
			} while ($pos===FALSE && $match);

		return substr($a, 0, $pos+strlen($match)).substr($b, $fromRight);
	}

	/**
	 * Mirror of Nette\Utils\Strings
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		return callback('Nette\Utils\Strings', $name)->invokeArgs($args);
	}
}
