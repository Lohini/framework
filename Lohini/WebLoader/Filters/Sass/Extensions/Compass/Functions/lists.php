<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass\Functions;
/**
 * Compass extension SassScript lists functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */
 
use Lohini\WebLoader\Filters\Sass\Script\Literals\String;
 
/**
 * Compass extension SassScript lists functions class.
 * A collection of functions for use in SassSCript.
 */
class Lists
{
	const SPACE_SEPARATOR='/\s+/';

	/**
	 * Return the first value from a space separated list.
	 * @param String $list
	 * @return String
	 */
	public static function first_value_of($list)
	{
		if ($list instanceof String) {
			$items=preg_split(self::SPACE_SEPARATOR, $list->value);
			return new String($items[0]);
			}
		return $list;
	}

	/**
	 * Return the nth value from a space separated list.
	 * @param String $list
	 * @param type $n
	 * @return String
	 */
	public static function nth_value_of($list, $n)
	{
		if ($list instanceof String) {
			$items=preg_split(self::SPACE_SEPARATOR, $list->value);
			return new String($items[$n->toInt()-1]);
			}
		return $list;
	}

	/**
	 * Return the last value from a space separated list.
	 * @param String $list
	 * @return String
	 */
	public static function last_value_of($list)
	{
		if ($list instanceof String) {
			$items=array_reverse(preg_split(self::SPACE_SEPARATOR, $list->value));
			return new String($items[0]);
			}
		return $list;
	}
}
