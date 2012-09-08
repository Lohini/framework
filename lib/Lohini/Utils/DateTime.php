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
 * @param string $defaultFormat
 */
class DateTime
extends \Nette\DateTime
{
	/** @var string */
	protected static $defaultFormat='j.n.Y G:i';


	/**
	 * DateTime object factory.
	 * @param string|int|\DateTime
	 * @return DateTime
	 */
	public static function from($time)
	{
		if ($time===NULL) {
			return NULL;
			}
		if ($time instanceof \DateTime) {
			return new static(
				$time->format('Y-m-d H:i:s'),
				$time->getTimezone()
				);
			}
		if ($time===0) {
			return static::tryFormats('U', 0);
			}
		if ($date=static::tryFormats(array(static::$defaultFormat), $time)) {
			return $date;
			}
		return parent::from($time);
	}

	/**
	 * @param array|string $formats
	 * @param $date
	 * @return bool|DateTime
	 */
	public static function tryFormats($formats, $date)
	{
		foreach ((array)$formats as $format) {
			if ($valid=static::createFromFormat('!'.$format, $date)) {
				return static::from($valid);
				}
			}

		return FALSE;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->format(static::$defaultFormat);
	}
}
