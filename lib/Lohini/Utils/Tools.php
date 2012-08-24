<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;

/**
 * @author Lopo <lopo@lohini.net>
 */
class Tools
{
	/**
	 * @author Filip ProchĂˇzka
	 * @param mixed $object
	 */
	public static function getType($value)
	{
		return is_object($value)? get_class($value) : gettype($value);
	}

	/**
	 * @param mixed $value
	 * @return bool 
	 */
	public static function isSerializable($value)
	{
		return is_scalar($value);
	}
}
