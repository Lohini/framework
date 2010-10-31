<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Templates;

use Nette\Tools;

/**
 * BailIff TemplateHelpers
 *
 * @author Lopo <lopo@losys.eu>
 */
final class TemplateHelpers
{
	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException('Cannot instantiate static class '.get_class($this));
	}

	/**
	 * Try to load the requested helper.
	 * @param string $helper name
	 * @return callback
	 */
	public static function loader($helper)
	{
		$callback=callback('BailIff\Templates\TemplateHelpers', $helper);
		if ($callback->isCallable()) {
			return $callback;
			}
		// fallback
		$callback=callback('Nette\Templates\TemplateHelpers', $helper);
		if ($callback->isCallable()) {
			return $callback;
			}
		$callback=callback('Nette\String', $helper);
		if ($callback->isCallable()) {
			return $callback;
			}
	}

	/**
	 * Office XML Date formatting
	 * @param string|int|DateTime $date
	 */
	public static function oxmlDate($date)
	{
		return $date==NULL? NULL : Tools::createDateTime($date)->format('Y-m-d')."T00:00:00.000";
	}

	/**
	 * Office XML DateTime formatting
	 * @param string|int|DateTime $date
	 */
	public static function oxmlDateTime($date)
	{
		return $date==NULL? NULL : Tools::createDateTime($date)->format('Y-m-d\TH:i:s').".000";
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
}
