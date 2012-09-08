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
 */
class Callback
extends \Doctrine\DBAL\Types\Type
{
	/**
	 * @param \Nette\Callback $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string)$value;
    }

	/**
	 * @param string $value
	 * @param AbstractPlatform $platform
	 * @return \Nette\Callback
	 */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value? new \Nette\Callback($value) : NULL;
    }

	/**
	 * @param array $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return mixed
	 */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

	/**
	 * @param AbstractPlatform $platform
	 * @return int
	 */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return $platform->getVarcharDefaultLength();
    }

	/**
	 * @return string
	 */
    public function getName()
    {
        return 'callback';
    }
}
