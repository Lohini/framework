<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;

/**
 * Lohini template run-time helpers
 *
 * @author Lopo <lopo@lohini.net>
 */
final class Helpers
extends \Nette\Object
{
	/** @var string */
	public static $datetimeFormat='j.n.Y H:i:s';
	/** @var array */
	private static $helpers=[];


	public function __construct()
	{
		throw new \Nette\StaticClassException;
	}

	/**
	 * Try to load the requested helper.
	 *
	 * @param string $helper name
	 * @return callable
	 */
	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return [__CLASS__, $helper];
			}
		if (isset(self::$helpers[$helper])) {
			return self::$helpers[$helper];
			}
		// fallback
		return \Nette\Templating\Helpers::loader($helper);
	}

	/**
	 * Office XML Date formatting
	 *
	 * @param string|int|\DateTime $date
	 * @return string|NULL
	 */
	public static function oxmlDate($date)
	{
		if ($date==NULL) {
			return NULL;
			}
		return \Nette\DateTime::from($date)->format('Y-m-d').'T00:00:00.000';
	}

	/**
	 * Office XML DateTime formatting
	 *
	 * @param string|int|\DateTime $date
	 * @return string|NULL
	 */
	public static function oxmlDateTime($date)
	{
		if ($date==NULL) {
			return NULL;
			}
		return \Nette\DateTime::from($date)->format('Y-m-d\TH:i:s').'.000';
	}

	/**
	 * Converts to human readable file size, extends/overrides Nette version
	 *
	 * @param int $bytes value
	 * @param int $precision number of decimal digits
	 * @param int $k value of 1k
	 * @param bool $iec use ISO/IEC unit names
	 * @see http://www.iso.org/iso/iso_catalogue/catalogue_tc/catalogue_detail.htm?csnumber=31898
	 * @return string formated value
	 */
	public static function bytes($bytes, $precision=2, $kilo=1024, $iec=FALSE)
	{
		$bytes=round($bytes);
		if ($iec && $kilo==1024) {// only if ISO/IEC forced
			$units=['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
			}
		elseif ($kilo==1000 || $kilo==1024) {// 1024 breaks ISO/IEC
			$units=['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
			}
		else {// invalid $kilo value
			return $bytes;
			}
		foreach ($units as $unit) {
			if (abs($bytes)<$kilo || $unit===end($units)) {
				break;
				}
			$bytes/=$kilo;
			}
		return round($bytes, $precision).' '.$unit;
	}

	/**
	 * @param string|int|\DateTime $time
	 * @param string $format
	 * @return string
	 */
	public static function datetime($time, $format=NULL)
	{
		if ($time==NULL) { // intentionally ==
			return NULL;
			}
		return date($format ?: self::$datetimeFormat, \Nette\DateTime::from($time)->format('U'));
	}
}
