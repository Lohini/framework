<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Types;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Normalizes given text. When empty, always converts to NULL.
 */
class Text
extends \Doctrine\DBAL\Types\TextType
{
	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return string|NULL
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		return ($value=(string)$value)=='' ? NULL : $value;
	}

	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return string|NULL
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		$value=(string)parent::convertToPHPValue($value, $platform);
		return $value=='' ? NULL : $value;
	}
}
