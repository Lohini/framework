<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Utils;

/**
 * @author Lopo <lopo@losys.eu>
 */
class Tools
{
	/**
	 * @author Filip Procházka
	 * @param mixed $object
	 */
	public static function getType($value)
	{
		return is_object($value)? get_class($value) : gettype($value);
	}
}
