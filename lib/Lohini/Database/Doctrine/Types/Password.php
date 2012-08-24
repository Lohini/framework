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
 */
class Password
extends \Doctrine\DBAL\Types\StringType
{
    /**
     * Converts a value from its PHP representation to its database representation of this type.
     *
     * @param \Lohini\Types\Password $value The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return mixed The database representation of the value.
	 * @throws \Nette\InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
		if (!$value instanceof \Lohini\Types\Password) {
			throw new \Nette\InvalidArgumentException('Expected instanceof Lohini\Types\Password, '.\Lohini\Utils\Tools::getType($value).' given');
			}

        return $value->getHash();
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return \Lohini\Types\Password The PHP representation of the value.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
		return new \Lohini\Types\Password($value);
    }

    /**
     * Gets the default length of this type.
	 *
	 * @return int
	 */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return 50;
    }

    /**
	 * @return string
	 */
    public function getName()
    {
        return 'password';
    }
}
