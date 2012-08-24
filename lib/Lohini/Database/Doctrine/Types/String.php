<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Types;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Normalizes given text. Always trims whitespace and when empty, converts to NULL.
 */
class String
extends \Doctrine\DBAL\Types\StringType
{
	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return string|NULL
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		return ($value=trim((string)$value))==''? NULL : $value;
	}

	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 *
	 * @return string|null
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return ($value=trim((string)$value))==''? NULL : $value;
	}
}
