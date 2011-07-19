<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Templating;

/**
 * BailIff template run-time telpers
 *
 * @author Lopo <lopo@losys.eu>
 */
final class Helpers
{
	public static $datetimeFormat='j.n.Y H:i:s';


	/** @var array */
	private static $helpers=array(
		'gravatar' => 'BailIff\Components\Gravatar::helper'
		);


	/**
	 * Try to load the requested helper.
	 * @param string $helper name
	 * @return callback
	 */
	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return callback(__CLASS__, $helper);
			}
		if (isset(self::$helpers[$helper])) {
			return self::$helpers[$helper];
			}
		// fallback
		return \Nette\Templating\DefaultHelpers::loader($helper);
	}

	/**
	 * Office XML Date formatting
	 * @param string|int|DateTime $date
	 * @return DateTime|NULL
	 */
	public static function oxmlDate($date)
	{
		if ($date==NULL) {
			return NULL;
			}
		return \Nette\DateTime::from($date)->format('Y-m-d')."T00:00:00.000";
	}

	/**
	 * Office XML DateTime formatting
	 * @param string|int|DateTime $date
	 * @return DateTime|NULL
	 */
	public static function oxmlDateTime($date)
	{
		if ($date==NULL) {
			return NULL;
			}
		return \Nette\DateTime::from($date)->format('Y-m-d\TH:i:s').".000";
	}

	/**
	 * Converts to human readable file size, extends/overrides Nette version
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
			$units=array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
			}
		elseif ($kilo==1000 || $kilo==1024) {// 1024 breaks ISO/IEC
			$units=array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
			}
		else {// invalid $k value
			return $bytes;
			}
		foreach ($units as $unit) {
			if (abs($bytes)<$kilo || $unit===end($units)) {
				break;
				}
			$bytes=$bytes/$kilo;
			}
		return round($bytes, $precision)." $unit";
	}

	/**
	 * @param string|int|DateTime $time
	 * @param string $format
	 * @return string
	 */
	public static function datetime($time, $format=NULL)
	{
		if ($time==NULL) { // intentionally ==
			return NULL;
			}
		if (!isset($format)) {
			$format=self::$datetimeFormat;
			}
		return date($format, \Nette\DateTime::from($time)->format('U'));
	}
}
